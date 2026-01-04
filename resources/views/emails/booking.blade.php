<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>線上預約確認</title>
    <style>
        body {
            font-family: 'Microsoft JhengHei', 'PingFang TC', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #14b8a6;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .field {
            margin-bottom: 20px;
        }
        .field-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
            display: block;
        }
        .field-value {
            color: #6b7280;
            padding: 10px;
            background-color: white;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        .note-content {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>蘭光租賃中心 - 線上預約確認</h1>
    </div>
    
    <div class="content">
        <div class="field">
            <span class="field-label">姓名：</span>
            <div class="field-value">{{ $data['name'] }}</div>
        </div>
        
        <div class="field">
            <span class="field-label">電子信箱：</span>
            <div class="field-value">{{ $data['email'] }}</div>
        </div>
        
        @if(!empty($data['phone']))
        <div class="field">
            <span class="field-label">聯絡電話：</span>
            <div class="field-value">{{ $data['phone'] }}</div>
        </div>
        @endif
        
        <div class="field">
            <span class="field-label">選擇車款：</span>
            <div class="field-value">{{ $data['scooterType'] }}</div>
        </div>
        
        <div class="field">
            <span class="field-label">預約日期：</span>
            <div class="field-value">{{ $data['date'] }}</div>
        </div>
        
        <div class="field">
            <span class="field-label">租借天數：</span>
            <div class="field-value">{{ $data['days'] }}</div>
        </div>
        
        @if(!empty($data['note']))
        <div class="field">
            <span class="field-label">備註：</span>
            <div class="field-value note-content">{{ $data['note'] }}</div>
        </div>
        @endif
        
        <div class="footer">
            <p>此郵件由蘭光租賃中心網站線上預約系統自動發送</p>
            <p>發送時間：{{ now()->format('Y-m-d H:i:s') }}</p>
            <p>預約完成後，我們將有專人與您電話聯繫確認詳情。</p>
        </div>
    </div>
</body>
</html>
