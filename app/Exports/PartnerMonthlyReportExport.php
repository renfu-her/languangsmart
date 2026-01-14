<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class PartnerMonthlyReportExport implements FromArray, WithTitle
{
    protected $partnerName;
    protected $year;
    protected $month;
    protected $dates;
    protected $models;
    protected $weekdayMap;

    public function __construct($partnerName, $year, $month, $dates, $models)
    {
        $this->partnerName = $partnerName;
        $this->year = $year;
        $this->month = $month;
        $this->dates = $dates;
        $this->models = $models;
        
        $this->weekdayMap = [
            'Monday' => '星期一',
            'Tuesday' => '星期二',
            'Wednesday' => '星期三',
            'Thursday' => '星期四',
            'Friday' => '星期五',
            'Saturday' => '星期六',
            'Sunday' => '星期日',
        ];
    }

    public function array(): array
    {
        $data = [];
        
        // 第一行：標題
        $titleRow = [$this->partnerName . '機車出租月報表'];
        // 計算總列數：日期(1) + 星期(1) + 當日租200/台(1) + 跨日租300/台(1) + 每個型號(4列)
        $totalCols = 2 + 2 + count($this->models) * 4;
        // 填充標題行到總列數
        for ($i = 1; $i < $totalCols; $i++) {
            $titleRow[] = '';
        }
        $data[] = $titleRow;
        
        // 第二行：當日租 200/台、跨日租 300/台，然後是機車型號
        $headerRow1 = ['當日租 200/台', '跨日租 300/台'];
        foreach ($this->models as $model) {
            $headerRow1[] = $model;
            $headerRow1[] = '';
            $headerRow1[] = '';
            $headerRow1[] = '';
        }
        $data[] = $headerRow1;
        
        // 第三行：日期、星期，然後每個型號下分為當日租(1列)和跨日租(3列)
        $headerRow2 = ['日期', '星期', '', ''];
        foreach ($this->models as $model) {
            $headerRow2[] = '當日租';
            $headerRow2[] = '跨日租';
            $headerRow2[] = '';
            $headerRow2[] = '';
        }
        $data[] = $headerRow2;
        
        // 第四行：空白、空白、空白、空白，然後每個型號下：當日租只有台數(1列)，跨日租有台數、天數、金額(3列)
        $headerRow3 = ['', '', '', ''];
        foreach ($this->models as $model) {
            $headerRow3[] = '台數';
            $headerRow3[] = '台數';
            $headerRow3[] = '天數';
            $headerRow3[] = '金額';
        }
        $data[] = $headerRow3;
        
        // 數據行
        foreach ($this->dates as $dateItem) {
            $dateStr = $dateItem['date'];
            $dateObj = \Carbon\Carbon::parse($dateStr . 'T00:00:00');
            $formattedDate = $dateObj->format('Y年m月d日');
            $weekday = $this->weekdayMap[$dateItem['weekday']] ?? $dateItem['weekday'];
            
            $dataRow = [$formattedDate, $weekday, '', ''];
            
            foreach ($this->models as $model) {
                $modelData = $dateItem['models'][$model] ?? [
                    'same_day_count' => 0,
                    'same_day_days' => 0,
                    'same_day_amount' => 0,
                    'overnight_count' => 0,
                    'overnight_days' => 0,
                    'overnight_amount' => 0,
                ];
                
                $hasSameDayFee = $modelData['same_day_amount'] > 0;
                $hasOvernightFee = $modelData['overnight_amount'] > 0;
                
                $dataRow[] = $hasSameDayFee ? $modelData['same_day_count'] : '';
                $dataRow[] = $hasOvernightFee ? $modelData['overnight_count'] : '';
                $dataRow[] = $hasOvernightFee ? $modelData['overnight_days'] : '';
                $dataRow[] = $hasOvernightFee ? $modelData['overnight_amount'] : '';
            }
            
            $data[] = $dataRow;
        }
        
        // 月結總計
        $summaryStartRow = count($data);
        
        // 總台數/天數行
        $totalRow = ['月結總計', '總台數/天數', '', ''];
        $grandTotalAmount = 0;
        
        foreach ($this->models as $model) {
            $modelSameDayTotalCount = 0;
            $modelSameDayTotalDays = 0;
            $modelSameDayTotalAmount = 0;
            $modelOvernightTotalCount = 0;
            $modelOvernightTotalDays = 0;
            $modelOvernightTotalAmount = 0;
            
            foreach ($this->dates as $dateItem) {
                $modelData = $dateItem['models'][$model] ?? [
                    'same_day_count' => 0,
                    'same_day_days' => 0,
                    'same_day_amount' => 0,
                    'overnight_count' => 0,
                    'overnight_days' => 0,
                    'overnight_amount' => 0,
                ];
                $modelSameDayTotalCount += $modelData['same_day_count'] ?? 0;
                $modelSameDayTotalDays += $modelData['same_day_days'] ?? 0;
                $modelSameDayTotalAmount += $modelData['same_day_amount'] ?? 0;
                $modelOvernightTotalCount += $modelData['overnight_count'] ?? 0;
                $modelOvernightTotalDays += $modelData['overnight_days'] ?? 0;
                $modelOvernightTotalAmount += $modelData['overnight_amount'] ?? 0;
            }
            
            $grandTotalAmount += $modelSameDayTotalAmount + $modelOvernightTotalAmount;
            
            $totalRow[] = $modelSameDayTotalCount > 0 ? $modelSameDayTotalCount : '';
            $totalRow[] = $modelOvernightTotalCount > 0 ? $modelOvernightTotalCount : '';
            $totalRow[] = $modelOvernightTotalDays > 0 ? $modelOvernightTotalDays : '';
            $totalRow[] = $modelOvernightTotalAmount > 0 ? $modelOvernightTotalAmount : '';
        }
        
        $data[] = $totalRow;
        
        // 小計行
        $subtotalRow = ['', '小計', '', ''];
        foreach ($this->models as $model) {
            $modelTotalAmount = 0;
            foreach ($this->dates as $dateItem) {
                $modelData = $dateItem['models'][$model] ?? [
                    'same_day_amount' => 0,
                    'overnight_amount' => 0,
                ];
                $modelTotalAmount += ($modelData['same_day_amount'] ?? 0) + ($modelData['overnight_amount'] ?? 0);
            }
            $subtotalRow[] = '';
            $subtotalRow[] = '';
            $subtotalRow[] = '';
            $subtotalRow[] = $modelTotalAmount > 0 ? $modelTotalAmount : '';
        }
        
        $data[] = $subtotalRow;
        
        // 總金額行
        $grandTotalRow = ['', '總金額', '', ''];
        $grandTotalRow[] = '';
        $grandTotalRow[] = '';
        $grandTotalRow[] = '';
        $grandTotalRow[] = $grandTotalAmount > 0 ? $grandTotalAmount : '';
        // 其他型號的欄位留空
        for ($i = 1; $i < count($this->models); $i++) {
            $grandTotalRow[] = '';
            $grandTotalRow[] = '';
            $grandTotalRow[] = '';
            $grandTotalRow[] = '';
        }
        
        $data[] = $grandTotalRow;
        
        return $data;
    }

    public function title(): string
    {
        return '月報表';
    }

}
