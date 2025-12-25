<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CaptchaController extends Controller
{
    /**
     * Generate a new captcha image.
     */
    public function generate(): JsonResponse
    {
        // 生成 6 位驗證碼（排除 O 和 0）
        $characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789'; // 排除 O 和 0
        $captchaCode = '';
        for ($i = 0; $i < 6; $i++) {
            $captchaCode .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // 生成唯一 ID
        $captchaId = Str::random(16);
        
        // 將答案存儲在 Cache 中，5 分鐘過期
        Cache::put("captcha_{$captchaId}", $captchaCode, now()->addMinutes(5));
        
        // 生成驗證碼圖片
        $imageData = $this->generateCaptchaImage($captchaCode);
        
        return response()->json([
            'data' => [
                'captcha_id' => $captchaId,
                'image' => 'data:image/png;base64,' . base64_encode($imageData),
            ],
        ]);
    }

    /**
     * Generate captcha image with noise and interference.
     */
    private function generateCaptchaImage(string $code): string
    {
        // 圖片尺寸
        $width = 200;
        $height = 60;
        
        // 創建圖片
        $image = imagecreatetruecolor($width, $height);
        
        // 背景顏色（淺色）
        $bgColor = imagecolorallocate($image, 245, 245, 245);
        imagefill($image, 0, 0, $bgColor);
        
        // 添加隨機背景雜訊點
        for ($i = 0; $i < 200; $i++) {
            $noiseColor = imagecolorallocate($image, rand(180, 220), rand(180, 220), rand(180, 220));
            imagesetpixel($image, rand(0, $width), rand(0, $height), $noiseColor);
        }
        
        // 添加干擾線條
        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate($image, rand(150, 200), rand(150, 200), rand(150, 200));
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }
        
        // 添加波浪線干擾
        for ($i = 0; $i < 3; $i++) {
            $waveColor = imagecolorallocate($image, rand(160, 210), rand(160, 210), rand(160, 210));
            for ($x = 0; $x < $width; $x++) {
                $y = $height / 2 + sin($x / 10 + $i) * 10;
                imagesetpixel($image, $x, (int)$y, $waveColor);
            }
        }
        
        // 字體大小和位置
        $fontSize = 28;
        $fontX = 20;
        $fontY = 40;
        
        // 繪製每個字符
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            
            // 隨機字符顏色（深色）
            $textColor = imagecolorallocate($image, rand(30, 80), rand(30, 80), rand(30, 80));
            
            // 隨機字符位置（輕微偏移）
            $charX = $fontX + ($i * 28) + rand(-3, 3);
            $charY = $fontY + rand(-5, 5);
            
            // 隨機旋轉角度（-15 到 15 度）
            $angle = rand(-15, 15);
            
            // 使用內建字體繪製字符（需要安裝 GD 庫）
            // 如果系統有 TTF 字體，可以使用 imagettftext
            if (function_exists('imagettftext')) {
                // 使用系統字體（如果有的話）
                $fontPath = $this->getFontPath();
                if ($fontPath) {
                    imagettftext($image, $fontSize, $angle, $charX, $charY, $textColor, $fontPath, $char);
                } else {
                    // 使用內建字體
                    imagestring($image, 5, $charX, $charY - 20, $char, $textColor);
                }
            } else {
                // 使用內建字體
                imagestring($image, 5, $charX, $charY - 20, $char, $textColor);
            }
        }
        
        // 添加更多雜訊點在字符上
        for ($i = 0; $i < 50; $i++) {
            $noiseColor = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(100, 200));
            imagesetpixel($image, rand(20, $width - 20), rand(10, $height - 10), $noiseColor);
        }
        
        // 輸出圖片為 PNG
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // 釋放記憶體
        imagedestroy($image);
        
        return $imageData;
    }

    /**
     * Get font path for TTF text rendering.
     */
    private function getFontPath(): ?string
    {
        // 嘗試常見的字體路徑
        $fontPaths = [
            storage_path('fonts/arial.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            'C:/Windows/Fonts/arial.ttf',
        ];
        
        foreach ($fontPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }

    /**
     * Verify captcha answer.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'captcha_id' => 'required|string',
            'answer' => 'required|string|size:6',
        ]);

        $captchaId = $request->get('captcha_id');
        $userAnswer = strtoupper(trim($request->get('answer'))); // 強制大寫並去除空格
        $correctAnswer = Cache::get("captcha_{$captchaId}");

        if ($correctAnswer === null) {
            return response()->json([
                'valid' => false,
                'message' => '驗證碼已過期，請重新獲取',
            ], 400);
        }

        if ($userAnswer !== $correctAnswer) {
            return response()->json([
                'valid' => false,
                'message' => '驗證碼錯誤',
            ], 400);
        }

        // 驗證成功後刪除驗證碼
        Cache::forget("captcha_{$captchaId}");

        return response()->json([
            'valid' => true,
            'message' => '驗證碼正確',
        ]);
    }
}
