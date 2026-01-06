<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingMail;
use App\Models\Booking;
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
            'lineId' => 'nullable|string|max:255', // 改為可選
            'phone' => 'required|string|max:20',
            'appointmentDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:appointmentDate',
            'shippingCompany' => 'required|in:泰富,藍白,聯營,大福',
            'shipArrivalTime' => 'required|date',
            'adults' => 'nullable|integer|min:0',
            'children' => 'nullable|integer|min:0',
            'scooters' => 'required|array|min:1',
            'scooters.*.model' => 'required|string|max:255',
            'scooters.*.count' => 'required|integer|min:1',
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
            // 移除驗證碼相關欄位
            unset($data['captcha_id'], $data['captcha_answer']);
            
            // 儲存到資料庫
            $booking = Booking::create([
                'name' => $data['name'],
                'line_id' => $data['lineId'] ?? null,
                'phone' => $data['phone'],
                'booking_date' => $data['appointmentDate'],
                'end_date' => $data['endDate'],
                'shipping_company' => $data['shippingCompany'],
                'ship_arrival_time' => $data['shipArrivalTime'],
                'adults' => $data['adults'] ?? null,
                'children' => $data['children'] ?? null,
                'scooters' => $data['scooters'],
                'note' => $data['note'] ?? null,
                'status' => '預約中', // 預設狀態為「預約中」
            ]);
            
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

    /**
     * Display a listing of bookings (for backend admin)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Booking::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('line_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Order by created_at desc
        $bookings = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $bookings,
        ]);
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking): JsonResponse
    {
        return response()->json([
            'data' => $booking,
        ]);
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(Request $request, Booking $booking): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'line_id' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'scooter_type' => 'required|string|max:50',
            'booking_date' => 'required|date',
            'rental_days' => 'required|string|max:20',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|in:預約中,執行中,已經回覆,取消',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $booking->update($validator->validated());

        return response()->json([
            'message' => 'Booking updated successfully',
            'data' => $booking,
        ]);
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:預約中,執行中,已經回覆,取消',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $booking->update(['status' => $request->get('status')]);

        return response()->json([
            'message' => 'Booking status updated successfully',
            'data' => $booking,
        ]);
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully',
        ]);
    }
}
