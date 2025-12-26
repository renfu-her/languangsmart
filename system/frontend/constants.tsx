
import { Motorbike } from './types';

export const MOCK_BIKES: Motorbike[] = [
  {
    id: '1',
    model: 'KRV-180',
    plateNumber: 'ADD-2233',
    color: '銀河藍/銀',
    scooterType: '白牌',
    status: '待出租',
    imageUrl: ''
  },
  {
    id: '2',
    model: 'BWS-Rugged',
    plateNumber: 'BCC-5566',
    color: '軍武綠',
    scooterType: '白牌',
    status: '待出租',
    imageUrl: ''
  },
  {
    id: '3',
    model: 'Jog Sweet',
    plateNumber: 'EMA-8888',
    color: '珍珠白',
    scooterType: '綠牌',
    status: '待出租',
    imageUrl: ''
  },
  {
    id: '4',
    model: 'ES-2000',
    plateNumber: 'GGO-9900',
    color: '消光黑',
    scooterType: '白牌',
    status: '已出租',
    imageUrl: ''
  }
];

export const BANNER_IMAGE = {
  url: '', // Image removed per user request
  title: '機車出租推薦',
  subtitle: '專業速克達租賃，250cc 以下首選'
};
