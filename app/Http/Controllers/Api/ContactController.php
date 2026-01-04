<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Send contact form email
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '驗證錯誤',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            
            // 發送郵件到指定信箱
            Mail::to('renfu.her@gmail.com')->send(new ContactMail($data));

            return response()->json([
                'message' => '訊息已成功送出，我們會盡快與您聯繫！',
            ]);
        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            
            return response()->json([
                'message' => '發送郵件時發生錯誤，請稍後再試。',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Test email sending
     */
    public function test(Request $request): JsonResponse
    {
        try {
            $testData = [
                'name' => $request->input('name', '測試使用者'),
                'email' => $request->input('email', 'test@example.com'),
                'phone' => $request->input('phone', '0912345678'),
                'message' => $request->input('message', '這是一封測試郵件，用於測試郵件發送功能是否正常運作。'),
            ];

            // 發送測試郵件到指定信箱
            Mail::to('renfu.her@gmail.com')->send(new ContactMail($testData));

            return response()->json([
                'message' => '測試郵件已成功發送！',
                'data' => $testData,
            ]);
        } catch (\Exception $e) {
            \Log::error('Test email error: ' . $e->getMessage());
            
            return response()->json([
                'message' => '發送測試郵件時發生錯誤',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
