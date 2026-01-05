<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class BookingController extends Controller
{
    /**
     * Send booking form email
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lineId' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'scooterType' => 'required|string|max:50',
            'date' => 'required|date',
            'days' => 'required|string|max:20',
            'note' => 'nullable|string|max:1000',
            'captcha_id' => 'required|string',
            'captcha_answer' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '驗證錯誤',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 驗證驗證碼
        $captchaId = $request->get('captcha_id');
        $userAnswer = strtoupper(trim($request->get('captcha_answer')));
        $correctAnswer = Cache::get("captcha_{$captchaId}");

        if ($correctAnswer === null) {
            return response()->json([
                'message' => '驗證碼已過期，請重新獲取',
                'errors' => ['captcha_answer' => ['驗證碼已過期，請重新獲取']],
            ], 422);
        }

        if ($userAnswer !== $correctAnswer) {
            return response()->json([
                'message' => '驗證碼錯誤',
                'errors' => ['captcha_answer' => ['驗證碼錯誤']],
            ], 422);
        }

        try {
            $data = $validator->validated();
            // 移除驗證碼相關欄位，只保留郵件需要的資料
            unset($data['captcha_id'], $data['captcha_answer']);
            
            // 發送郵件給管理員（因為沒有 email，無法發送給用戶）
            Mail::to('zau1110216@gmail.com')->send(new BookingMail($data));

            // 驗證成功後刪除驗證碼
            Cache::forget("captcha_{$captchaId}");

            return response()->json([
                'message' => '預約已成功提交，我們會盡快與您聯繫確認詳情！',
            ]);
        } catch (\Exception $e) {
            \Log::error('Booking form error: ' . $e->getMessage());
            
            return response()->json([
                'message' => '發送郵件時發生錯誤，請稍後再試。',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
