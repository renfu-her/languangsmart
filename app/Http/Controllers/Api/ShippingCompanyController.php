<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingCompanyResource;
use App\Models\ShippingCompany;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ShippingCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ShippingCompany::query()->with('store');

        if ($request->has('store_id')) {
            $query->where('store_id', $request->get('store_id'));
        }
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('store_id')->orderBy('name')->get();

        return response()->json([
            'data' => ShippingCompanyResource::collection($items),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'store_id' => 'required|exists:stores,id',
        ], [
            'name.required' => '請輸入船班名稱',
            'store_id.required' => '請選擇所屬商店',
            'store_id.exists' => '所選商店不存在',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '驗證錯誤',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        // Unique per store
        $exists = ShippingCompany::where('store_id', $data['store_id'])->where('name', $data['name'])->exists();
        if ($exists) {
            return response()->json([
                'message' => '驗證錯誤',
                'errors' => ['name' => ['此商店下已存在相同船班名稱']],
            ], 422);
        }

        try {
            $item = ShippingCompany::create($data);
            $item->load('store');

            return response()->json([
                'message' => '船運已成功新增',
                'data' => new ShippingCompanyResource($item),
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Shipping company creation error: ' . $e->getMessage());

            return response()->json([
                'message' => '新增船運時發生錯誤',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ShippingCompany $shippingCompany): JsonResponse
    {
        $shippingCompany->load('store');
        return response()->json([
            'data' => new ShippingCompanyResource($shippingCompany),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShippingCompany $shippingCompany): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'store_id' => 'sometimes|required|exists:stores,id',
        ], [
            'name.required' => '請輸入船班名稱',
            'store_id.required' => '請選擇所屬商店',
            'store_id.exists' => '所選商店不存在',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '驗證錯誤',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $storeId = $data['store_id'] ?? $shippingCompany->store_id;
        $name = $data['name'] ?? $shippingCompany->name;
        $exists = ShippingCompany::where('store_id', $storeId)
            ->where('name', $name)
            ->where('id', '!=', $shippingCompany->id)
            ->exists();
        if ($exists) {
            return response()->json([
                'message' => '驗證錯誤',
                'errors' => ['name' => ['此商店下已存在相同船班名稱']],
            ], 422);
        }

        try {
            $shippingCompany->update($data);
            $shippingCompany->load('store');

            return response()->json([
                'message' => '船運已成功更新',
                'data' => new ShippingCompanyResource($shippingCompany),
            ]);
        } catch (\Exception $e) {
            \Log::error('Shipping company update error: ' . $e->getMessage());

            return response()->json([
                'message' => '更新船運時發生錯誤',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShippingCompany $shippingCompany): JsonResponse
    {
        try {
            $shippingCompany->delete();
            return response()->json([
                'message' => '船運已成功刪除',
            ]);
        } catch (\Exception $e) {
            \Log::error('Shipping company deletion error: ' . $e->getMessage());
            return response()->json([
                'message' => '刪除船運時發生錯誤',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
