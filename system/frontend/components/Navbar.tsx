
import React from 'react';

const Navbar: React.FC = () => {
  return (
    <nav className="bg-white border-b border-gray-200 sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-20 items-center">
          <div className="flex items-center gap-3">
            {/* Logo Section Replicating the Image Style */}
            <div className="flex items-baseline gap-4">
              <span className="text-4xl font-extrabold tracking-tighter" style={{ color: '#8dc63f', fontFamily: 'system-ui, sans-serif' }}>
                ZAU
              </span>
              <span className="text-2xl md:text-3xl font-black text-black tracking-wide" style={{ fontFamily: '"Microsoft JhengHei", "Heiti TC", sans-serif' }}>
                蘭光電動機車出租
              </span>
            </div>
          </div>
          
          <div className="hidden md:flex space-x-8 items-center">
            <a href="#" className="text-gray-600 hover:text-indigo-600 font-medium transition-colors">機車列表</a>
            <a href="#" className="text-gray-600 hover:text-indigo-600 font-medium transition-colors">租賃須知</a>
            <a href="#" className="text-gray-600 hover:text-indigo-600 font-medium transition-colors">服務據點</a>
            <button className="bg-indigo-600 text-white px-6 py-2 rounded-full font-bold hover:bg-indigo-700 transition-all shadow-md">
              立即預約
            </button>
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
