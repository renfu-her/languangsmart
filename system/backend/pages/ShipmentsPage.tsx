import React, { useState, useEffect, useRef } from 'react';
import { Plus, Search, Edit3, Trash2, X, Loader2, MoreHorizontal } from 'lucide-react';
import { shippingCompaniesApi, storesApi } from '../lib/api';
import { inputClasses, selectClasses, labelClasses, searchInputClasses, modalCancelButtonClasses, modalSubmitButtonClasses } from '../styles';

interface Store {
  id: number;
  name: string;
}

interface ShippingCompany {
  id: number;
  name: string;
  store_id: number;
  store?: { id: number; name: string };
}

const ShipmentsPage: React.FC = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<ShippingCompany | null>(null);
  const [items, setItems] = useState<ShippingCompany[]>([]);
  const [stores, setStores] = useState<Store[]>([]);
  const [loading, setLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [storeFilterId, setStoreFilterId] = useState<string>('');
  const [formData, setFormData] = useState({
    name: '',
    store_id: '',
  });
  const [openDropdownId, setOpenDropdownId] = useState<number | null>(null);
  const [dropdownPosition, setDropdownPosition] = useState<{ top: number; right: number } | null>(null);
  const buttonRefs = useRef<Record<number, HTMLButtonElement | null>>({});

  useEffect(() => {
    fetchStores();
  }, []);

  useEffect(() => {
    fetchItems();
  }, [searchTerm, storeFilterId]);

  const fetchStores = async () => {
    try {
      const response = await storesApi.list();
      setStores((response.data || []).sort((a: Store, b: Store) => a.id - b.id));
    } catch (error) {
      console.error('Failed to fetch stores:', error);
    }
  };

  const fetchItems = async () => {
    setLoading(true);
    try {
      const params: Record<string, string> = {};
      if (searchTerm) params.search = searchTerm;
      if (storeFilterId) params.store_id = storeFilterId;
      const response = await shippingCompaniesApi.list(params);
      setItems(response.data || []);
    } catch (error) {
      console.error('Failed to fetch shipping companies:', error);
      setItems([]);
    } finally {
      setLoading(false);
    }
  };

  const handleOpenModal = (item?: ShippingCompany) => {
    if (item) {
      setEditingItem(item);
      setFormData({
        name: item.name,
        store_id: String(item.store_id),
      });
    } else {
      setEditingItem(null);
      setFormData({
        name: '',
        store_id: storeFilterId || '',
      });
    }
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingItem(null);
    setFormData({ name: '', store_id: '' });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const storeId = formData.store_id ? parseInt(formData.store_id, 10) : undefined;
    if (!storeId) {
      alert('請選擇所屬商店');
      return;
    }
    try {
      if (editingItem) {
        await shippingCompaniesApi.update(editingItem.id, {
          name: formData.name,
          store_id: storeId,
        });
      } else {
        await shippingCompaniesApi.create({
          name: formData.name,
          store_id: storeId,
        });
      }
      handleCloseModal();
      fetchItems();
    } catch (error: any) {
      console.error('Failed to save shipping company:', error);
      if (error?.response?.data?.errors) {
        const errors = error.response.data.errors;
        const errorMessages = Object.entries(errors).map(([field, messages]: [string, any]) => {
          return messages.join(', ');
        }).join('\n');
        alert(`儲存失敗：\n${errorMessages}`);
      } else if (error?.response?.data?.message) {
        alert(`儲存失敗：${error.response.data.message}`);
      } else {
        alert('儲存失敗，請檢查輸入資料');
      }
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('確定要刪除此船班嗎？此操作無法復原。')) return;
    try {
      await shippingCompaniesApi.delete(id);
      fetchItems();
    } catch (error: any) {
      console.error('Failed to delete shipping company:', error);
      alert(error?.response?.data?.message || '刪除失敗，請稍後再試。');
    }
    setOpenDropdownId(null);
    setDropdownPosition(null);
  };

  const toggleDropdown = (id: number) => {
    if (openDropdownId === id) {
      setOpenDropdownId(null);
      setDropdownPosition(null);
    } else {
      const button = buttonRefs.current[id];
      if (button) {
        const rect = button.getBoundingClientRect();
        setDropdownPosition({
          top: rect.bottom + window.scrollY + 8,
          right: window.innerWidth - rect.right,
        });
      }
      setOpenDropdownId(id);
    }
  };

  useEffect(() => {
    const handleScroll = () => {
      if (openDropdownId !== null) {
        setOpenDropdownId(null);
        setDropdownPosition(null);
      }
    };
    window.addEventListener('scroll', handleScroll, true);
    return () => window.removeEventListener('scroll', handleScroll, true);
  }, [openDropdownId]);

  const getStoreName = (item: ShippingCompany) => item.store?.name ?? stores.find(s => s.id === item.store_id)?.name ?? `商店 #${item.store_id}`;

  return (
    <div className="px-6 pb-6 pt-0 dark:text-gray-100">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h1 className="text-2xl font-bold text-gray-800 dark:text-gray-100">船運管理</h1>
          <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">管理各商店的船班名稱，新增訂單與線上預約時依所選商店顯示對應船班</p>
        </div>
        <button
          onClick={() => handleOpenModal()}
          className="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2.5 rounded-xl flex items-center space-x-2 transition-all shadow-sm active:scale-95 font-bold"
        >
          <Plus size={18} />
          <span>新增船班</span>
        </button>
      </div>

      <div className="bg-white dark:bg-gray-800 rounded-2xl p-6 mb-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div className="flex flex-col md:flex-row md:items-center gap-4">
          <div className="relative w-full max-w-xs">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
            <input
              type="text"
              placeholder="搜尋船班名稱..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className={searchInputClasses}
            />
          </div>
          <div className="flex items-center gap-2">
            <label className="text-sm font-medium text-gray-600 dark:text-gray-400">所屬商店</label>
            <select
              className={selectClasses}
              value={storeFilterId}
              onChange={(e) => setStoreFilterId(e.target.value)}
            >
              <option value="">全部</option>
              {stores.map((s) => (
                <option key={s.id} value={String(s.id)}>{s.name}</option>
              ))}
            </select>
          </div>
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
            <thead className="bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[11px]">
              <tr>
                <th className="px-6 py-5">船班名稱</th>
                <th className="px-6 py-5">所屬商店</th>
                <th className="px-6 py-5 text-center">操作</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {items.length === 0 ? (
                <tr>
                  <td colSpan={3} className="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    目前沒有船班資料，請先選擇所屬商店後新增船班
                  </td>
                </tr>
              ) : (
                items.map((item) => (
                  <tr key={item.id} className="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 group transition-colors">
                    <td className="px-6 py-5 font-medium text-gray-800 dark:text-gray-100">{item.name}</td>
                    <td className="px-6 py-5 text-gray-600 dark:text-gray-400">{getStoreName(item)}</td>
                    <td className="px-6 py-5 text-center">
                      <div className="relative">
                        <button
                          ref={(el) => { if (el) buttonRefs.current[item.id] = el; }}
                          onClick={() => toggleDropdown(item.id)}
                          className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-gray-400 dark:text-gray-500 transition-colors"
                        >
                          <MoreHorizontal size={18} />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      )}

      {openDropdownId !== null && dropdownPosition && (
        <>
          <div className="fixed inset-0 z-40" onClick={() => { setOpenDropdownId(null); setDropdownPosition(null); }} />
          <div
            className="fixed z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-1 min-w-[120px]"
            style={{ top: `${dropdownPosition.top}px`, right: `${dropdownPosition.right}px` }}
            onClick={(e) => e.stopPropagation()}
          >
            {(() => {
              const item = items.find((t) => t.id === openDropdownId);
              if (!item) return null;
              return (
                <>
                  <button
                    onClick={() => { handleOpenModal(item); setOpenDropdownId(null); setDropdownPosition(null); }}
                    className="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                  >
                    <Edit3 size={16} /> 編輯
                  </button>
                  <button
                    onClick={() => handleDelete(item.id)}
                    className="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2"
                  >
                    <Trash2 size={16} /> 刪除
                  </button>
                </>
              );
            })()}
          </div>
        </>
      )}

      {isModalOpen && (
        <div className="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4">
          <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div className="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
              <h2 className="text-xl font-bold text-gray-900 dark:text-gray-100">
                {editingItem ? '編輯船班' : '新增船班'}
              </h2>
              <button onClick={handleCloseModal} className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <X size={20} />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-6">
              <div>
                <label className={labelClasses}>所屬商店 <span className="text-red-500">*</span></label>
                <div className="relative">
                  <select
                    className={selectClasses}
                    value={formData.store_id}
                    onChange={(e) => setFormData({ ...formData, store_id: e.target.value })}
                    required
                    disabled={!!editingItem}
                  >
                    <option value="">請選擇所屬商店</option>
                    {stores.map((s) => (
                      <option key={s.id} value={String(s.id)}>{s.name}</option>
                    ))}
                  </select>
                  {editingItem && (
                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">編輯時無法變更所屬商店</p>
                  )}
                </div>
              </div>
              <div>
                <label className={labelClasses}>船班名稱 <span className="text-red-500">*</span></label>
                <input
                  type="text"
                  className={inputClasses}
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  placeholder="例如：泰富、藍白、聯營"
                  required
                />
              </div>
              <div className="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onClick={handleCloseModal} className={modalCancelButtonClasses}>取消</button>
                <button type="submit" className={modalSubmitButtonClasses}>{editingItem ? '更新' : '新增'}</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default ShipmentsPage;
