
import React from 'react';

const Banner: React.FC = () => {
  return (
    <div className="relative w-full h-[300px] md:h-[350px] overflow-hidden bg-[#FFEBEE]">
      {/* Pop Art Style Background Overlay */}
      <div className="absolute inset-0 flex">
        <div className="w-1/2 h-full bg-[#FF7070] relative overflow-hidden">
          {/* Decorative dots like the red pattern in Image 1 */}
          <div className="absolute inset-0 opacity-20 pointer-events-none" style={{ backgroundImage: 'radial-gradient(circle, black 2px, transparent 0)', backgroundSize: '20px 20px' }}></div>
        </div>
        <div className="w-1/2 h-full bg-[#E0F7FA]"></div>
      </div>
      
      {/* Content Area */}
      <div className="absolute inset-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div className="z-10 bg-white border-[6px] border-black p-6 md:p-10 transform -rotate-2 shadow-[12px_12px_0px_0px_rgba(0,0,0,1)]">
          <h1 className="text-5xl md:text-8xl font-black text-black tracking-tighter italic">
            機車出租推薦
          </h1>
        </div>
      </div>
    </div>
  );
};

export default Banner;
