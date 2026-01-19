import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { Mail, Lock, Loader2, AlertCircle, RefreshCw, Store, ChevronDown } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useStore } from '../contexts/StoreContext';
import { captchaApi, storesApi } from '../lib/api';

interface Captcha {
  captcha_id: string;
  image: string; // Base64 encoded image
}

const LoginPage: React.FC = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const { currentStore, stores, loading: storesLoading, setCurrentStore, createStore, deleteStore } = useStore();
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    captchaAnswer: '',
  });
  const [captcha, setCaptcha] = useState<Captcha | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isLoadingCaptcha, setIsLoadingCaptcha] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isStoreDropdownOpen, setIsStoreDropdownOpen] = useState(false);
  const [isStoreModalOpen, setIsStoreModalOpen] = useState(false);
  const [isEditingStore, setIsEditingStore] = useState(false);
  const [editingStore, setEditingStore] = useState<{ id: number; name: string; address: string; phone: string; manager: string } | null>(null);
  const [storeFormData, setStoreFormData] = useState({
    name: '',
    address: '',
    phone: '',
    manager: '',
  });
  const [isSubmittingStore, setIsSubmittingStore] = useState(false);
  const hasFetchedRef = useRef(false);
  const storeDropdownRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!hasFetchedRef.current) {
      hasFetchedRef.current = true;
      fetchCaptcha();
    }
  }, []);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (storeDropdownRef.current && !storeDropdownRef.current.contains(event.target as Node)) {
        setIsStoreDropdownOpen(false);
      }
    };

    if (isStoreDropdownOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isStoreDropdownOpen]);

  const handleOpenStoreModal = (store?: { id: number; name: string; address: string | null; phone: string | null; manager: string }) => {
    if (store) {
      setIsEditingStore(true);
      setEditingStore({ id: store.id, name: store.name, address: store.address || '', phone: store.phone || '', manager: store.manager });
      setStoreFormData({
        name: store.name,
        address: store.address || '',
        phone: store.phone || '',
        manager: store.manager,
      });
    } else {
      setIsEditingStore(false);
      setEditingStore(null);
      setStoreFormData({ name: '', address: '', phone: '', manager: '' });
    }
    setIsStoreModalOpen(true);
    setIsStoreDropdownOpen(false);
  };

  const handleCloseStoreModal = () => {
    setIsStoreModalOpen(false);
    setIsEditingStore(false);
    setEditingStore(null);
    setStoreFormData({ name: '', address: '', phone: '', manager: '' });
  };

  const handleStoreSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmittingStore(true);

    try {
      if (isEditingStore && editingStore) {
        await storesApi.update(editingStore.id, storeFormData);
      } else {
        await createStore(storeFormData);
      }
      handleCloseStoreModal();
    } catch (error: any) {
      console.error('Failed to save store:', error);
      alert(error?.response?.data?.message || '儲存失敗，請稍後再試');
    } finally {
      setIsSubmittingStore(false);
    }
  };

  const handleDeleteStore = async (id: number) => {
    if (!confirm('確定要刪除這個商店嗎？')) {
      return;
    }

    try {
      await deleteStore(id);
      setIsStoreDropdownOpen(false);
    } catch (error: any) {
      console.error('Failed to delete store:', error);
      alert(error?.response?.data?.message || '刪除失敗，請稍後再試');
    }
  };

  const fetchCaptcha = async () => {
    setIsLoadingCaptcha(true);
    setError(null);
    try {
      const response = await captchaApi.generate();
      if (response && response.data) {
        setCaptcha(response.data);
        setFormData(prev => ({ ...prev, captchaAnswer: '' }));
      } else {
        console.error('Invalid captcha response:', response);
        setError('無法獲取驗證碼，請重新整理頁面');
      }
    } catch (error: any) {
      console.error('Failed to fetch captcha:', error);
      setError(error?.response?.data?.message || '無法獲取驗證碼，請稍後再試');
    } finally {
      setIsLoadingCaptcha(false);
    }
  };

  const inputClasses = "w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500 dark:text-gray-200";

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    if (!captcha) {
      setError('請先獲取驗證碼');
      return;
    }

    if (!formData.captchaAnswer || formData.captchaAnswer.length !== 6) {
      setError('請輸入完整的 6 位驗證碼');
      return;
    }

    setIsSubmitting(true);

    try {
      await login(
        formData.email, 
        formData.password, 
        captcha.captcha_id, 
        formData.captchaAnswer.toUpperCase().trim()
      );
      navigate('/orders');
    } catch (err: any) {
      setError(err.message || '登入失敗，請檢查 Email 和密碼');
      // 登入失敗後重新獲取驗證碼
      fetchCaptcha();
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-50 via-white to-orange-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 p-4">
      <div className="w-full max-w-md">
        <div className="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 space-y-6">
          {/* Logo/Title */}
          <div className="text-center space-y-2">
            <h1 className="text-3xl font-black text-gray-800 dark:text-gray-100">蘭光電動機車管理系統</h1>
            <p className="text-sm text-gray-500 dark:text-gray-400">請登入您的帳號</p>
          </div>

          {/* Error Message */}
          {error && (
            <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center space-x-3">
              <AlertCircle size={20} className="text-red-600 dark:text-red-400 flex-shrink-0" />
              <p className="text-sm text-red-600 dark:text-red-400">{error}</p>
            </div>
          )}

          {/* Store Selector */}
          <div className="relative" ref={storeDropdownRef}>
            <label className="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">
              選擇商店
            </label>
            <button
              type="button"
              onClick={() => setIsStoreDropdownOpen(!isStoreDropdownOpen)}
              disabled={storesLoading}
              className={`w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all flex items-center justify-between ${storesLoading ? 'opacity-50 cursor-not-allowed' : ''}`}
            >
              <div className="flex items-center space-x-2 flex-1 min-w-0">
                {storesLoading ? (
                  <Loader2 size={18} className="animate-spin text-gray-400" />
                ) : (
                  <Store size={18} className="text-gray-400 flex-shrink-0" />
                )}
                <span className="text-left truncate dark:text-gray-200">
                  {storesLoading ? '載入中...' : (currentStore ? currentStore.name : stores.length === 0 ? '無商店，點擊新增' : '選擇商店')}
                </span>
              </div>
              {!storesLoading && <ChevronDown size={16} className={`text-gray-400 flex-shrink-0 transition-transform ${isStoreDropdownOpen ? 'rotate-180' : ''}`} />}
            </button>

            {isStoreDropdownOpen && !storesLoading && (
              <div className="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg z-50 max-h-64 overflow-y-auto">
                {stores.length === 0 ? (
                  <div className="px-4 py-3 text-center text-gray-500 dark:text-gray-400 text-sm">
                    <p className="mb-2">目前沒有商店</p>
                    <p className="text-xs">請點擊下方按鈕新增</p>
                  </div>
                ) : (
                  stores.map((store) => (
                    <div key={store.id} className="group">
                      <div
                        className={`px-4 py-2.5 flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 ${
                          currentStore?.id === store.id ? 'bg-orange-50 dark:bg-orange-900/20' : ''
                        }`}
                        onClick={() => {
                          setCurrentStore(store);
                          setIsStoreDropdownOpen(false);
                        }}
                      >
                        <div className="flex items-center space-x-2 flex-1 min-w-0">
                          <Store size={14} className="flex-shrink-0 text-gray-400" />
                          <span className={`text-sm truncate ${currentStore?.id === store.id ? 'text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-700 dark:text-gray-200'}`}>
                            {store.name}
                          </span>
                        </div>
                        <div className="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                          <button
                            type="button"
                            onClick={(e) => {
                              e.stopPropagation();
                              handleOpenStoreModal(store);
                            }}
                            className="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600 rounded text-gray-600 dark:text-gray-300"
                            title="編輯"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                          </button>
                          <button
                            type="button"
                            onClick={(e) => {
                              e.stopPropagation();
                              handleDeleteStore(store.id);
                            }}
                            className="p-1.5 hover:bg-red-100 dark:hover:bg-red-900/30 rounded text-red-600 dark:text-red-400"
                            title="刪除"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  ))
                )}
                <div className="border-t border-gray-200 dark:border-gray-600">
                  <button
                    type="button"
                    onClick={() => handleOpenStoreModal()}
                    className="w-full px-4 py-2.5 flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                      <line x1="12" y1="5" x2="12" y2="19"></line>
                      <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>新增商店</span>
                  </button>
                </div>
              </div>
            )}
          </div>

          {/* Login Form */}
          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label className="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">
                Email
              </label>
              <div className="relative">
                <Mail className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
                <input
                  type="email"
                  className={`${inputClasses} pl-11`}
                  placeholder="輸入您的 Email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  required
                  disabled={isSubmitting}
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">
                密碼
              </label>
              <div className="relative">
                <Lock className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
                <input
                  type="password"
                  className={`${inputClasses} pl-11`}
                  placeholder="輸入您的密碼"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  required
                  disabled={isSubmitting}
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">
                驗證碼 <span className="text-red-500">*</span>
              </label>
              <div className="flex items-center space-x-3 mb-2">
                <div className="flex-1 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 flex items-center justify-center min-h-[60px]">
                  {isLoadingCaptcha ? (
                    <Loader2 size={20} className="animate-spin text-gray-400" />
                  ) : captcha ? (
                    <img 
                      src={captcha.image} 
                      alt="驗證碼" 
                      className="h-12 w-auto select-none cursor-pointer"
                      style={{ imageRendering: 'auto' }}
                      onClick={fetchCaptcha}
                      title="點擊刷新驗證碼"
                    />
                  ) : (
                    <span className="text-sm text-gray-400">載入驗證碼中...</span>
                  )}
                </div>
                <button
                  type="button"
                  onClick={fetchCaptcha}
                  disabled={isLoadingCaptcha || isSubmitting}
                  className="p-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-all disabled:opacity-50"
                  title="重新獲取驗證碼"
                >
                  <RefreshCw size={18} className={`text-gray-600 dark:text-gray-300 ${isLoadingCaptcha ? 'animate-spin' : ''}`} />
                </button>
              </div>
              <input
                type="text"
                className={`${inputClasses} uppercase font-mono tracking-widest text-center text-lg`}
                placeholder="輸入 6 位驗證碼"
                value={formData.captchaAnswer}
                onChange={(e) => {
                  // 只允許字母和數字，排除 O 和 0，最多 6 位，強制大寫
                  const value = e.target.value.toUpperCase().replace(/[O0]/g, '').slice(0, 6);
                  setFormData({ ...formData, captchaAnswer: value });
                }}
                required
                disabled={isSubmitting || !captcha}
                maxLength={6}
                pattern="[A-NP-Z1-9]{6}"
              />
            </div>

            <button
              type="submit"
              disabled={isSubmitting || !captcha}
              className="w-full bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-xl font-black text-sm shadow-lg transition-all disabled:opacity-50 flex items-center justify-center space-x-2"
            >
              {isSubmitting ? (
                <>
                  <Loader2 size={18} className="animate-spin" />
                  <span>登入中...</span>
                </>
              ) : (
                <span>登入</span>
              )}
            </button>
          </form>

          {/* Store Modal */}
          {isStoreModalOpen && (
            <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
              <div className="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-lg font-bold text-gray-800 dark:text-gray-100">
                    {isEditingStore ? '編輯商店' : '新增商店'}
                  </h2>
                  <button
                    type="button"
                    onClick={handleCloseStoreModal}
                    className="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                      <line x1="18" y1="6" x2="6" y2="18"></line>
                      <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                  </button>
                </div>

                <form onSubmit={handleStoreSubmit} className="space-y-4">
                  <div>
                    <label className="block text-xs font-bold mb-2 text-gray-600 dark:text-gray-300">
                      商店名稱 *
                    </label>
                    <input
                      type="text"
                      required
                      value={storeFormData.name}
                      onChange={(e) => setStoreFormData({ ...storeFormData, name: e.target.value })}
                      className="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-bold mb-2 text-gray-600 dark:text-gray-300">
                      地址
                    </label>
                    <input
                      type="text"
                      value={storeFormData.address}
                      onChange={(e) => setStoreFormData({ ...storeFormData, address: e.target.value })}
                      className="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-bold mb-2 text-gray-600 dark:text-gray-300">
                      電話
                    </label>
                    <input
                      type="text"
                      value={storeFormData.phone}
                      onChange={(e) => setStoreFormData({ ...storeFormData, phone: e.target.value })}
                      className="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-bold mb-2 text-gray-600 dark:text-gray-300">
                      負責人 *
                    </label>
                    <input
                      type="text"
                      required
                      value={storeFormData.manager}
                      onChange={(e) => setStoreFormData({ ...storeFormData, manager: e.target.value })}
                      className="w-full px-3 py-2 rounded-lg border bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    />
                  </div>

                  <div className="flex space-x-3 pt-4">
                    <button
                      type="button"
                      onClick={handleCloseStoreModal}
                      className="flex-1 px-4 py-2 rounded-lg font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600"
                    >
                      取消
                    </button>
                    <button
                      type="submit"
                      disabled={isSubmittingStore}
                      className="flex-1 px-4 py-2 rounded-lg font-medium bg-orange-600 text-white hover:bg-orange-700 disabled:opacity-50"
                    >
                      {isSubmittingStore ? '儲存中...' : '儲存'}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default LoginPage;

