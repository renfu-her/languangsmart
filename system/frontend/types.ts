
export type ScooterType = '白牌' | '綠牌' | '電輔車';

export interface Motorbike {
  id: string;
  model: string;         // 機車型號 *
  color?: string;        // 車款顏色 (非必填)
  plateNumber: string;   // 車牌號碼 *
  scooterType: ScooterType; // 車款類型 *
  imageUrl: string;      // 機車外觀照片
  status: '待出租' | '已出租' | '保養中'; // 初始狀態
}

export enum SearchType {
  MODEL = 'model',
  PLATE = 'plate'
}
