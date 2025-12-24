<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FineResource;
use App\Models\Fine;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FineController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Fine::with(['scooter', 'order']);

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('tenant', 'like', "%{$search}%")
                    ->orWhereHas('scooter', function ($q) use ($search) {
                        $q->where('plate_number', 'like', "%{$search}%");
                    });
            });
        }

        $fines = $query->orderBy('violation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => FineResource::collection($fines),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'scooter_id' => 'required|exists:scooters,id',
            'order_id' => 'nullable|exists:orders,id',
            'tenant' => 'required|string|max:255',
            'violation_date' => 'required|date',
            'violation_type' => 'required|string|max:255',
            'fine_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|in:未繳費,已處理',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fine = Fine::create($validator->validated());

        return response()->json([
            'message' => 'Fine created successfully',
            'data' => new FineResource($fine->load(['scooter', 'order'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Fine $fine): JsonResponse
    {
        return response()->json([
            'data' => new FineResource($fine->load(['scooter', 'order'])),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fine $fine): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'scooter_id' => 'required|exists:scooters,id',
            'order_id' => 'nullable|exists:orders,id',
            'tenant' => 'required|string|max:255',
            'violation_date' => 'required|date',
            'violation_type' => 'required|string|max:255',
            'fine_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|in:未繳費,已處理',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fine->update($validator->validated());

        return response()->json([
            'message' => 'Fine updated successfully',
            'data' => new FineResource($fine->load(['scooter', 'order'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fine $fine): JsonResponse
    {
        // Delete photo if exists
        if ($fine->photo_path) {
            $this->imageService->deleteImage($fine->photo_path);
        }

        $fine->delete();

        return response()->json([
            'message' => 'Fine deleted successfully',
        ]);
    }

    /**
     * Upload fine photo
     */
    public function uploadPhoto(Request $request, Fine $fine): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $photoPath = $this->imageService->uploadImage(
            $request->file('photo'),
            'fines',
            $fine->photo_path
        );

        $fine->update(['photo_path' => $photoPath]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'data' => new FineResource($fine->load(['scooter', 'order'])),
        ]);
    }
}

