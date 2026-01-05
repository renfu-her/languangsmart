import React, { useState, useEffect, useRef } from 'react';
import { Search, Edit2, Trash2, X, Loader2, ChevronDown, Check } from 'lucide-react';
import { bookingsApi } from '../lib/api';
import { inputClasses, labelClasses, modalCancelButtonClasses, modalSubmitButtonClasses } from '../styles';

interface Booking {
  id: number;
  name: string;
  line_id: string;
  phone: string | null;
  scooter_type: string;
  booking_date: string;
  rental_days: string;
  note: string | null;
  status: '執行中' | '已經回覆' | '取消';
  created_at: string;
  updated_at: string;
}

const BookingsPage: React.FC = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingBooking, setEditingBooking] = useState<Booking | null>(null);
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [loading, setLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('');
  const [formData, setFormData] = useState({
    name: '',
    line_id: '',
    phone: '',
    scooter_type: '',
    booking_date: '',
    rental_days: '',
    note: '',
    status: '執行中' as '執行中' | '已經回覆' | '取消',
  });
  const [openStatusDropdownId, setOpenStatusDropdownId] = useState<number | null>(null);
  const [statusDropdownPosition, setStatusDropdownPosition] = useState<{ top: number; right: number } | null>(null);
  const statusButtonRefs = useRef<Record<number, HTMLButtonElement | null>>({});
  const statusDropdownRefs = useRef<Record<number, HTMLDivElement | null>>({});

  useEffect(() => {
    fetchBookings();
  }, [searchTerm, statusFilter]);

  const fetchBookings = async () => {
    setLoading(true);
    try {
      const params: { search?: string; status?: string } = {};
      if (searchTerm) params.search = searchTerm;
      if (statusFilter) params.status = statusFilter;
      
      const response = await bookingsApi.list(params);
      setBookings(response.data || []);
    } catch (error) {
      console.error('Failed to fetch bookings:', error);
      setBookings([]);
    } finally {
      setLoading(false);
    }
  };

  const handleOpenModal = (booking?: Booking) => {
    if (booking) {
      setEditingBooking(booking);
      setFormData({
        name: booking.name,
        line_id: booking.line_id,
        phone: booking.phone || '',
        scooter_type: booking.scooter_type,
        booking_date: booking.booking_date,
        rental_days: booking.rental_days,
        note: booking.note || '',
        status: booking.status,
      });
    } else {
      setEditingBooking(null);
      setFormData({
        name: '',
        line_id: '',
        phone: '',
        scooter_type: '',
        booking_date: '',
        rental_days: '',
        note: '',
        status: '執行中',
      });
    }
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingBooking(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      if (editingBooking) {
        await bookingsApi.update(editingBooking.id, formData);
      }
      await fetchBookings();
      handleCloseModal();
    } catch (error: any) {
      console.error('Failed to save booking:', error);
      alert(error.message || '儲存失敗');
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('確定要刪除此預約嗎？')) return;

    try {
      await bookingsApi.delete(id);
      await fetchBookings();
    } catch (error: any) {
      console.error('Failed to delete booking:', error);
      alert(error.message || '刪除失敗');
    }
  };

  const handleStatusChange = async (id: number, newStatus: '執行中' | '已經回覆' | '取消') => {
    try {
      await bookingsApi.updateStatus(id, newStatus);
      await fetchBookings();
      setOpenStatusDropdownId(null);
    } catch (error: any) {
      console.error('Failed to update status:', error);
      alert(error.message || '更新狀態失敗');
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case '執行中':
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
      case '已經回覆':
        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
      case '取消':
        return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
      default:
        return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
    }
  };

  // 處理狀態下拉選單位置
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (openStatusDropdownId !== null) {
        const buttonRef = statusButtonRefs.current[openStatusDropdownId];
        const dropdownRef = statusDropdownRefs.current[openStatusDropdownId];
        
        if (buttonRef && dropdownRef && 
            !buttonRef.contains(event.target as Node) && 
            !dropdownRef.contains(event.target as Node)) {
          setOpenStatusDropdownId(null);
        }
      }
    };

    if (openStatusDropdownId !== null) {
      document.addEventListener('mousedown', handleClickOutside);
      const buttonRef = statusButtonRefs.current[openStatusDropdownId];
      if (buttonRef) {
        const rect = buttonRef.getBoundingClientRect();
        setStatusDropdownPosition({
          top: rect.bottom + 4,
          right: window.innerWidth - rect.right,
        });
      }
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [openStatusDropdownId]);

  if (loading && bookings.length === 0) {
    return (
      <div className="px-6 pb-6 pt-0 dark:text-gray-100">
        <div className="flex justify-center items-center py-12">
          <Loader2 className="animate-spin text-orange-600" size={32} />
        </div>
      </div>
    );
  }

  return (
    <div className="px-6 pb-6 pt-0 dark:text-gray-100">
      <div className="mb-8 flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-800 dark:text-gray-100">預約管理</h1>
          <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">管理線上預約資料</p>
        </div>
      </div>

      {/* 搜尋和篩選 */}
      <div className="mb-6 flex gap-4">
        <div className="flex-1 relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
          <input
            type="text"
            placeholder="搜尋姓名、LINE ID、電話..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500 dark:text-gray-200"
          />
        </div>
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          className="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all dark:text-gray-200"
        >
          <option value="">全部狀態</option>
          <option value="執行中">執行中</option>
          <option value="已經回覆">已經回覆</option>
          <option value="取消">取消</option>
        </select>
      </div>

      {bookings.length === 0 ? (
        <div className="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
          <p className="text-gray-500 dark:text-gray-400">目前沒有預約資料</p>
        </div>
      ) : (
        <div className="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead className="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                  <th className="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">姓名</th>
                  <th className="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">LINE ID</th>
                  <th className="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">電話</th>
                  <th className="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">車款</th>
                  <th className="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">預約日期</th>
                  <th className="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">租借天數</th>
                  <th className="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">狀態</th>
                  <th className="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300">建立時間</th>
                  <th className="px-6 py-4 text-sm font-bold text-gray-700 dark:text-gray-300 text-center">操作</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                {bookings.map((booking) => (
                  <tr key={booking.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td className="px-6 py-4">
                      <span className="text-sm font-medium text-gray-800 dark:text-gray-100">{booking.name}</span>
                    </td>
                    <td className="px-6 py-4">
                      <span className="text-sm text-gray-600 dark:text-gray-400">{booking.line_id}</span>
                    </td>
                    <td className="px-6 py-4">
                      <span className="text-sm text-gray-600 dark:text-gray-400">{booking.phone || '-'}</span>
                    </td>
                    <td className="px-6 py-4">
                      <span className="text-sm text-gray-600 dark:text-gray-400">{booking.scooter_type}</span>
                    </td>
                    <td className="px-6 py-4">
                      <span className="text-sm text-gray-600 dark:text-gray-400">{booking.booking_date}</span>
                    </td>
                    <td className="px-6 py-4">
                      <span className="text-sm text-gray-600 dark:text-gray-400">{booking.rental_days}</span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="relative">
                        <button
                          ref={(el) => (statusButtonRefs.current[booking.id] = el)}
                          onClick={() => setOpenStatusDropdownId(openStatusDropdownId === booking.id ? null : booking.id)}
                          className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(booking.status)} cursor-pointer hover:opacity-80 transition-opacity`}
                        >
                          {booking.status}
                          <ChevronDown size={12} className="ml-1" />
                        </button>
                        {openStatusDropdownId === booking.id && (
                          <div
                            ref={(el) => (statusDropdownRefs.current[booking.id] = el)}
                            className="absolute z-50 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg min-w-[120px]"
                            style={{
                              top: statusDropdownPosition?.top || 0,
                              right: statusDropdownPosition?.right || 0,
                            }}
                          >
                            {(['執行中', '已經回覆', '取消'] as const).map((status) => (
                              <button
                                key={status}
                                onClick={() => handleStatusChange(booking.id, status)}
                                className={`w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-between ${
                                  booking.status === status ? 'text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300'
                                }`}
                              >
                                {status}
                                {booking.status === status && <Check size={14} />}
                              </button>
                            ))}
                          </div>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <span className="text-sm text-gray-600 dark:text-gray-400">
                        {new Date(booking.created_at).toLocaleString('zh-TW')}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex items-center justify-center space-x-2">
                        <button
                          onClick={() => handleOpenModal(booking)}
                          className="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                          title="編輯"
                        >
                          <Edit2 size={18} />
                        </button>
                        <button
                          onClick={() => handleDelete(booking.id)}
                          className="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                          title="刪除"
                        >
                          <Trash2 size={18} />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* 編輯 Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-[60] flex items-center justify-center p-4">
          <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={handleCloseModal} />
          <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl relative animate-in fade-in zoom-in duration-200 overflow-hidden flex flex-col max-h-[90vh]">
            <div className="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
              <h2 className="text-xl font-bold text-gray-800 dark:text-gray-100">
                {editingBooking ? '編輯預約' : '新增預約'}
              </h2>
              <button
                onClick={handleCloseModal}
                className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
              >
                <X size={20} />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-6 overflow-y-auto flex-1">
              <div className="space-y-4">
                <div>
                  <label className={labelClasses}>姓名 *</label>
                  <input
                    type="text"
                    required
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className={inputClasses}
                  />
                </div>

                <div>
                  <label className={labelClasses}>LINE ID *</label>
                  <input
                    type="text"
                    required
                    value={formData.line_id}
                    onChange={(e) => setFormData({ ...formData, line_id: e.target.value })}
                    className={inputClasses}
                  />
                </div>

                <div>
                  <label className={labelClasses}>電話</label>
                  <input
                    type="tel"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    className={inputClasses}
                  />
                </div>

                <div>
                  <label className={labelClasses}>車款 *</label>
                  <select
                    required
                    value={formData.scooter_type}
                    onChange={(e) => setFormData({ ...formData, scooter_type: e.target.value })}
                    className={inputClasses}
                  >
                    <option value="">請選擇</option>
                    <option value="白牌">白牌 (Heavy)</option>
                    <option value="綠牌">綠牌 (Light)</option>
                    <option value="電輔車">電輔車 (E-Bike)</option>
                    <option value="三輪車">三輪車 (Tricycle)</option>
                  </select>
                </div>

                <div>
                  <label className={labelClasses}>預約日期 *</label>
                  <input
                    type="date"
                    required
                    value={formData.booking_date}
                    onChange={(e) => setFormData({ ...formData, booking_date: e.target.value })}
                    className={inputClasses}
                  />
                </div>

                <div>
                  <label className={labelClasses}>租借天數 *</label>
                  <select
                    required
                    value={formData.rental_days}
                    onChange={(e) => setFormData({ ...formData, rental_days: e.target.value })}
                    className={inputClasses}
                  >
                    <option value="">請選擇</option>
                    <option value="1">1 天 (24小時)</option>
                    <option value="2">2 天 1 夜</option>
                    <option value="3">3 天 2 夜</option>
                    <option value="4">4 天以上</option>
                  </select>
                </div>

                <div>
                  <label className={labelClasses}>備註</label>
                  <textarea
                    value={formData.note}
                    onChange={(e) => setFormData({ ...formData, note: e.target.value })}
                    className={inputClasses}
                    rows={4}
                  />
                </div>

                <div>
                  <label className={labelClasses}>狀態 *</label>
                  <select
                    required
                    value={formData.status}
                    onChange={(e) => setFormData({ ...formData, status: e.target.value as '執行中' | '已經回覆' | '取消' })}
                    className={inputClasses}
                  >
                    <option value="執行中">執行中</option>
                    <option value="已經回覆">已經回覆</option>
                    <option value="取消">取消</option>
                  </select>
                </div>
              </div>

              <div className="mt-6 flex justify-end space-x-3">
                <button
                  type="button"
                  onClick={handleCloseModal}
                  className={modalCancelButtonClasses}
                >
                  取消
                </button>
                <button
                  type="submit"
                  className={modalSubmitButtonClasses}
                >
                  儲存
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default BookingsPage;
