
import React from 'react';
import { Motorbike } from '../types';

interface Props {
  bike: Motorbike;
}

const MotorbikeCard: React.FC<Props> = ({ bike }) => {
  const typeBadgeColors = {
    '白牌': 'bg-[#1a1a1a] text-white',
    '綠牌': 'bg-green-600 text-white',
    '電輔車': 'bg-blue-500 text-white'
  };

  const statusColors = {
    '待出租': 'text-green-600',
    '已出租': 'text-gray-400',
    '保養中': 'text-red-500',
  };

  return (
    <div className="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md border border-gray-100 transition-all flex flex-col group h-full">
      {/* No Image Placeholder */}
      <div className="aspect-[4/3] w-full bg-gray-50 flex flex-col items-center justify-center p-4 border-b border-gray-100">
        <div className="w-12 h-12 mb-2 text-gray-200">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <span className="text-gray-300 font-black text-sm tracking-tighter italic">No Image</span>
      </div>
      
      {/* Simplified Info Section */}
      <div className="p-5 flex-grow bg-white">
        <div className="flex justify-between items-start mb-4">
          <div>
            <span className={`text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded mb-1 inline-block ${typeBadgeColors[bike.scooterType]}`}>
              {bike.scooterType}
            </span>
            <h3 className="text-xl font-black text-gray-900 leading-tight">
              {bike.model}
            </h3>
          </div>
          <div className="text-right">
            <p className={`text-[10px] font-bold ${statusColors[bike.status]}`}>
              ● {bike.status}
            </p>
          </div>
        </div>

        <div className="space-y-3">
          <div className="flex justify-between items-center bg-gray-50 p-2 rounded-lg">
            <span className="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">車牌號碼</span>
            <span className="text-sm font-mono font-black text-gray-800 tracking-widest">
              {bike.plateNumber}
            </span>
          </div>

          <div className="flex justify-between items-center px-2">
            <span className="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">車款顏色</span>
            <span className="text-xs font-bold text-gray-700">
              {bike.color || '未填寫'}
            </span>
          </div>
        </div>
      </div>
      
      <div className="p-5 pt-0">
        <button className="w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition-colors shadow-lg shadow-gray-200">
          查看詳情
        </button>
      </div>
    </div>
  );
};

export default MotorbikeCard;
