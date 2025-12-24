import React, { useState, useEffect } from 'react';
import { Plus, Search, Bike, Edit, Trash2, X, Loader2 } from 'lucide-react';
import { scootersApi, partnersApi } from '../lib/api';

interface Scooter {
  id: number;
  partner_id: number;
  partner?: { id: number; name: string };
  plate_number: string;
  model: string;
  type: string;
  color: string | null;
  status: string;
  photo_path: string | null;
}

interface Partner {
  id: number;
  name: string;
}

const ScootersPage: React.FC = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingScooter, setEditingScooter] = useState<Scooter | null>(null);
  const [scooters, setScooters] = useState<Scooter[]>([]);
  const [partners, setPartners] = useState<Partner[]>([]);
  const [loading, setLoading] = useState(false);
  const [statusFilter, setStatusFilter] = useState<string>('');
  const [searchTerm, setSearchTerm] = useState('');
  const [formData, setFormData] = useState({
    partner_id: '',
    plate_number: '',
    model: '',
    type: '白牌',
    color: '',
    status: '待出租',
  });
  const [photoFile, setPhotoFile] = useState<File | null>(null);
  const [photoPreview, setPhotoPreview] = useState<string | null>(null);

  const inputClasses = "w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all placeholder:text-gray-400";

  useEffect(() => {
    fetchScooters();
    fetchPartners();
  }, [statusFilter, searchTerm]);

  const fetchScooters = async () => {
    setLoading(true);
    try {
      const params: any = {};
      if (statusFilter) params.status = statusFilter;
      if (searchTerm) params.search = searchTerm;
      const response = await scootersApi.list(Object.keys(params).length > 0 ? params : undefined);
      setScooters(response.data.data || []);
    } catch (error) {
      console.error('Failed to fetch scooters:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchPartners = async () => {
    try {
      const response = await partnersApi.list();
      setPartners(response.data.data || []);
    } catch (error) {
      console.error('Failed to fetch partners:', error);
    }
  };

  const handleOpenModal = (scooter?: Scooter) => {
    if (scooter) {
      setEditingScooter(scooter);
      setFormData({
        partner_id: String(scooter.partner_id),
        plate_number: scooter.plate_number,
        model: scooter.model,
        type: scooter.type,
        color: scooter.color || '',
        status: scooter.status,
      });
      setPhotoPreview(scooter.photo_path || null);
    } else {
      setEditingScooter(null);
      setFormData({
        partner_id: '',
        plate_number: '',
        model: '',
        type: '白牌',
        color: '',
        status: '待出租',
      });
      setPhotoPreview(null);
    }
    setPhotoFile(null);
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingScooter(null);
    setFormData({
      partner_id: '',
      plate_number: '',
      model: '',
      type: '白牌',
      color: '',
      status: '待出租',
    });
    setPhotoFile(null);
    setPhotoPreview(null);
  };

  const handleSubmit = async () => {
    if (!formData.partner_id || !formData.plate_number || !formData.model) {
      alert('請填寫必填欄位');
      return;
    }

    try {
      const data = {
        ...formData,
        partner_id: parseInt(formData.partner_id),
        color: formData.color || null,
      };

      if (editingScooter) {
        await scootersApi.update(editingScooter.id, data);
        if (photoFile) {
          await scootersApi.uploadPhoto(editingScooter.id, photoFile);
        }
      } else {
        const response = await scootersApi.create(data);
        if (photoFile && response.data.data) {
          await scootersApi.uploadPhoto(response.data.data.id, photoFile);
        }
      }
      handleCloseModal();
      fetchScooters();
    } catch (error) {
      console.error('Failed to save scooter:', error);
      alert('儲存失敗，請檢查輸入資料');
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('確定要刪除此機車嗎？')) return;
    try {
      await scootersApi.delete(id);
      fetchScooters();
    } catch (error) {
      console.error('Failed to delete scooter:', error);
      alert('刪除失敗');
    }
  };

  const handlePhotoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setPhotoFile(file);
      const reader = new FileReader();
      reader.onloadend = () => {
        setPhotoPreview(reader.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const statusCounts = {
    all: scooters.length,
    '待出租': scooters.filter(s => s.status === '待出租').length,
    '出租中': scooters.filter(s => s.status === '出租中').length,
    '保養中': scooters.filter(s => s.status === '保養中').length,
  };

  return (
    <div className="p-6">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">機車管理</h1>
          <p className="text-sm text-gray-500 mt-1">管理車隊清單、保養狀態與車型分類</p>
        </div>
        <button 
          onClick={() => handleOpenModal()}
          className="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2.5 rounded-xl flex items-center space-x-2 transition-all shadow-sm active:scale-95 font-bold"
        >
          <Plus size={18} />
          <span>新增機車</span>
        </button>
      </div>

      <div className="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div className="p-5 bg-gray-50/30 flex flex-col md:flex-row justify-between items-center gap-4 border-b border-gray-100">
           <div className="flex items-center space-x-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0 scrollbar-hide">
             <button 
               onClick={() => setStatusFilter('')}
               className={`px-5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors ${
                 !statusFilter ? 'bg-orange-600 text-white shadow-sm shadow-orange-100' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
               }`}
             >
               全部 {statusCounts.all}
             </button>
             <button 
               onClick={() => setStatusFilter('待出租')}
               className={`px-5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors ${
                 statusFilter === '待出租' ? 'bg-orange-600 text-white shadow-sm shadow-orange-100' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
               }`}
             >
               待出租 {statusCounts['待出租']}
             </button>
             <button 
               onClick={() => setStatusFilter('出租中')}
               className={`px-5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors ${
                 statusFilter === '出租中' ? 'bg-orange-600 text-white shadow-sm shadow-orange-100' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
               }`}
             >
               出租中 {statusCounts['出租中']}
             </button>
             <button 
               onClick={() => setStatusFilter('保養中')}
               className={`px-5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors ${
                 statusFilter === '保養中' ? 'bg-orange-600 text-white shadow-sm shadow-orange-100' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
               }`}
             >
               保養中 {statusCounts['保養中']}
             </button>
           </div>
           <div className="relative w-full max-w-xs">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
            <input 
              type="text" 
              placeholder="搜尋車牌、型號..." 
              className="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all placeholder:text-gray-400 shadow-sm"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>

        {loading ? (
          <div className="p-12 text-center">
            <Loader2 size={32} className="animate-spin mx-auto text-orange-600" />
            <p className="mt-4 text-gray-500">載入中...</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm whitespace-nowrap">
              <thead className="bg-gray-50/50 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                <tr>
                  <th className="px-6 py-5">車牌號碼</th>
                  <th className="px-6 py-5">機車型號</th>
                  <th className="px-6 py-5">車款類型</th>
                  <th className="px-6 py-5">顏色</th>
                  <th className="px-6 py-5">所屬商店</th>
                  <th className="px-6 py-5">狀態</th>
                  <th className="px-6 py-5 text-center">操作</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {scooters.map((scooter) => (
                  <tr key={scooter.id} className="hover:bg-gray-50/50 group transition-colors">
                    <td className="px-6 py-5 font-black text-gray-900">{scooter.plate_number}</td>
                    <td className="px-6 py-5 text-gray-700 font-bold">{scooter.model}</td>
                    <td className="px-6 py-5">
                      <span className={`px-2 py-1 rounded-lg text-[10px] font-black border ${
                        scooter.type === '白牌' ? 'bg-blue-50 text-blue-600 border-blue-100' : 
                        scooter.type === '電輔車' ? 'bg-orange-50 text-orange-600 border-orange-100' :
                        'bg-green-50 text-green-600 border-green-100'
                      }`}>
                        {scooter.type}
                      </span>
                    </td>
                    <td className="px-6 py-5 text-gray-500 font-medium">{scooter.color || '-'}</td>
                    <td className="px-6 py-5 text-gray-500 font-medium">{scooter.partner?.name || '-'}</td>
                    <td className="px-6 py-5">
                       <span className={`inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black shadow-sm ${
                         scooter.status === '待出租' ? 'bg-green-100 text-green-700' :
                         scooter.status === '出租中' ? 'bg-blue-100 text-blue-700' :
                         'bg-orange-100 text-orange-700'
                       }`}>
                         {scooter.status}
                       </span>
                    </td>
                    <td className="px-6 py-5 text-center">
                      <div className="flex items-center justify-center space-x-2">
                        <button 
                          onClick={() => handleOpenModal(scooter)}
                          className="p-2 hover:bg-orange-50 rounded-xl text-gray-400 hover:text-orange-600 transition-all"
                        >
                          <Edit size={16} />
                        </button>
                        <button 
                          onClick={() => handleDelete(scooter.id)}
                          className="p-2 hover:bg-red-50 rounded-xl text-gray-400 hover:text-red-600 transition-all"
                        >
                          <Trash2 size={16} />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={handleCloseModal} />
          <div className="bg-white rounded-3xl shadow-2xl w-full max-w-3xl relative animate-in fade-in zoom-in duration-200 overflow-hidden flex flex-col">
            <div className="p-6 border-b border-gray-100 flex items-center justify-between">
              <h2 className="text-xl font-bold text-gray-800">新增機車設備</h2>
              <button onClick={handleCloseModal} className="p-2 hover:bg-gray-100 rounded-full text-gray-400">
                 <X size={20} />
              </button>
            </div>
            <div className="p-8 space-y-6 overflow-y-auto max-h-[70vh]">
              <div className="grid grid-cols-2 gap-6">
                <div>
                  <label className="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">
                    所屬分店 / 商店 <span className="text-red-500">*</span>
                  </label>
                  <select 
                    className={inputClasses}
                    value={formData.partner_id}
                    onChange={(e) => setFormData({ ...formData, partner_id: e.target.value })}
                  >
                    <option value="">請選擇</option>
                    {partners.map(partner => (
                      <option key={partner.id} value={partner.id}>{partner.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">
                    車牌號碼 <span className="text-red-500">*</span>
                  </label>
                  <input 
                    type="text" 
                    className={inputClasses} 
                    placeholder="例如: ABC-1234"
                    value={formData.plate_number}
                    onChange={(e) => setFormData({ ...formData, plate_number: e.target.value })}
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">
                    機車型號 <span className="text-red-500">*</span>
                  </label>
                  <input 
                    type="text" 
                    className={inputClasses} 
                    placeholder="例如: ES-2000"
                    value={formData.model}
                    onChange={(e) => setFormData({ ...formData, model: e.target.value })}
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">
                    車款顏色 <span className="text-gray-400 font-normal">(非必填)</span>
                  </label>
                  <input 
                    type="text" 
                    className={inputClasses} 
                    placeholder="例如: 消光黑"
                    value={formData.color}
                    onChange={(e) => setFormData({ ...formData, color: e.target.value })}
                  />
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">
                    車款類型 <span className="text-red-500">*</span>
                  </label>
                  <select 
                    className={inputClasses}
                    value={formData.type}
                    onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                  >
                    <option value="白牌">白牌 (Heavy)</option>
                    <option value="綠牌">綠牌 (Light)</option>
                    <option value="電輔車">電輔車 (E-Bike)</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">
                    初始狀態 <span className="text-red-500">*</span>
                  </label>
                  <select 
                    className={inputClasses}
                    value={formData.status}
                    onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                  >
                    <option value="待出租">待出租</option>
                    <option value="出租中">出租中</option>
                    <option value="保養中">保養中</option>
                  </select>
                </div>
              </div>
              <div>
                <label className="block text-xs font-bold text-gray-500 mb-3 uppercase tracking-wider">機車外觀照片</label>
                <div className="border-2 border-dashed border-gray-200 rounded-2xl p-10 bg-gray-50/50 flex flex-col items-center justify-center hover:border-orange-400 hover:bg-orange-50/10 cursor-pointer transition-all group relative">
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handlePhotoChange}
                    className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                  />
                   <div className="p-4 bg-white rounded-2xl shadow-sm mb-3 group-hover:scale-110 transition-transform">
                     <Bike size={32} className="text-gray-400 group-hover:text-orange-500 transition-colors" />
                   </div>
                   <p className="text-sm font-bold text-gray-700">點擊或拖放照片至此</p>
                   <p className="text-xs text-gray-400 mt-1">建議解析度 1280x720 以上的清晰照片</p>
                   {photoPreview && (
                     <img src={photoPreview} alt="Preview" className="mt-4 max-w-full max-h-48 rounded-lg" />
                   )}
                </div>
              </div>
            </div>
            <div className="p-6 border-t border-gray-100 bg-gray-50 flex justify-end space-x-3 rounded-b-2xl">
              <button onClick={handleCloseModal} className="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-white hover:text-gray-700 transition-all">取消</button>
              <button onClick={handleSubmit} className="px-10 py-2.5 bg-gray-900 rounded-xl text-sm font-black text-white hover:bg-black shadow-lg active:scale-95 transition-all">
                {editingScooter ? '確認更新' : '完成建立'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ScootersPage;
