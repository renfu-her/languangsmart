
import React, { useState, useMemo } from 'react';
import Navbar from './components/Navbar';
import Banner from './components/Banner';
import MotorbikeCard from './components/MotorbikeCard';
import { MOCK_BIKES } from './constants';
import { SearchType } from './types';

const App: React.FC = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [searchType, setSearchType] = useState<SearchType>(SearchType.MODEL);

  const filteredBikes = useMemo(() => {
    if (!searchTerm) return MOCK_BIKES;
    
    const term = searchTerm.toLowerCase();
    return MOCK_BIKES.filter(bike => {
      if (searchType === SearchType.MODEL) {
        return bike.model.toLowerCase().includes(term);
      } else {
        return bike.plateNumber.toLowerCase().includes(term);
      }
    });
  }, [searchTerm, searchType]);

  return (
    <div className="min-h-screen bg-[#fafafa] flex flex-col">
      <Navbar />
      
      <main className="flex-grow">
        <Banner />

        {/* Search Section */}
        <section className="max-w-7xl mx-auto px-4 -mt-10 relative z-10 sm:px-6 lg:px-8 mb-12">
          <div className="bg-white p-4 rounded-2xl shadow-xl border border-gray-100">
            <div className="flex flex-col md:flex-row gap-3">
              <div className="flex-grow relative">
                <input
                  type="text"
                  placeholder={searchType === SearchType.MODEL ? "輸入機車型號 (例如: ES-2000)" : "輸入車牌號碼 (例如: ABC-1234)"}
                  className="w-full pl-12 pr-4 py-4 bg-gray-50 border border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent outline-none transition-all text-lg font-bold"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
                <div className="absolute left-4 top-1/2 -translate-y-1/2 text-black opacity-20">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>

              <div className="flex bg-gray-100 p-1 rounded-xl">
                <button
                  onClick={() => setSearchType(SearchType.MODEL)}
                  className={`px-6 py-3 rounded-lg font-black text-sm transition-all ${
                    searchType === SearchType.MODEL ? 'bg-black text-white shadow-md' : 'text-gray-400 hover:text-gray-600'
                  }`}
                >
                  搜尋型號
                </button>
                <button
                  onClick={() => setSearchType(SearchType.PLATE)}
                  className={`px-6 py-3 rounded-lg font-black text-sm transition-all ${
                    searchType === SearchType.PLATE ? 'bg-black text-white shadow-md' : 'text-gray-400 hover:text-gray-600'
                  }`}
                >
                  搜尋車牌
                </button>
              </div>
            </div>
          </div>
        </section>

        {/* Results */}
        <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
          <div className="mb-10 flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 className="text-3xl font-black text-black uppercase italic tracking-tighter">
              {searchTerm ? '查詢結果' : '現有設備列表'}
            </h2>
            
            <div className="flex gap-2">
              <span className="px-3 py-1 bg-white border border-gray-200 rounded-full text-[10px] font-black text-gray-400">250cc 以下速克達</span>
            </div>
          </div>

          {filteredBikes.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {filteredBikes.map(bike => (
                <MotorbikeCard key={bike.id} bike={bike} />
              ))}
            </div>
          ) : (
            <div className="text-center py-20 bg-white rounded-3xl border border-gray-100">
              <p className="text-gray-300 font-black text-2xl tracking-tighter italic">NO SCOOTERS FOUND</p>
              <button 
                onClick={() => setSearchTerm('')}
                className="mt-4 text-black font-black border-b-2 border-black hover:opacity-70"
              >
                CLEAR SEARCH
              </button>
            </div>
          )}
        </section>
      </main>

      <footer className="bg-white border-t border-gray-100 py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <div className="flex items-center justify-center gap-2 mb-4">
            <span className="text-3xl font-black" style={{ color: '#8dc63f' }}>ZAU</span>
            <span className="text-xl font-black text-gray-900">蘭光電動機車出租</span>
          </div>
          <p className="text-gray-400 text-xs font-bold tracking-widest uppercase">專業電動機車租賃查詢系統</p>
        </div>
      </footer>
    </div>
  );
};

export default App;
