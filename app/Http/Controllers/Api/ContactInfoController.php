<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactInfo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ContactInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContactInfo::query();

        // Filter by active status for public API
        if ($request->has('active_only') && $request->get('active_only')) {
            $query->where('is_active', true);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('line_id', 'like', "%{$search}%");
            });
        }

        $contactInfos = $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $contactInfos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'line_id' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $contactInfo = ContactInfo::create($validator->validated());

        return response()->json([
            'message' => 'Contact info created successfully',
            'data' => $contactInfo,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactInfo $contactInfo): JsonResponse
    {
        return response()->json([
            'data' => $contactInfo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactInfo $contactInfo): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'line_id' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $contactInfo->update($validator->validated());

        return response()->json([
            'message' => 'Contact info updated successfully',
            'data' => $contactInfo,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactInfo $contactInfo): JsonResponse
    {
        $contactInfo->delete();

        return response()->json([
            'message' => 'Contact info deleted successfully',
        ]);
    }
}
