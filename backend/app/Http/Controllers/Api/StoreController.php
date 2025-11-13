<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display a listing of stores for current user
     */
    public function index(Request $request)
    {
        $stores = Store::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($stores);
    }

    /**
     * Store a newly created store
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:255|unique:stores,subdomain|alpha_dash',
            'domain' => 'nullable|string|max:255|unique:stores,domain',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'required|string|max:20',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        try {
            $store = $this->tenantService->createStore($validated);

            return response()->json([
                'store' => $store,
                'message' => 'Store created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create store',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified store
     */
    public function show(Store $store)
    {
        // Ensure user owns the store
        if ($store->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($store);
    }

    /**
     * Update the specified store
     */
    public function update(Request $request, Store $store)
    {
        // Ensure user owns the store
        if ($store->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'nullable|string|max:255|unique:stores,domain,'.$store->id,
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'sometimes|string|max:20',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'logo' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $store->update($validated);

        return response()->json([
            'store' => $store,
            'message' => 'Store updated successfully',
        ]);
    }

    /**
     * Remove the specified store
     */
    public function destroy(Store $store)
    {
        // Ensure user owns the store
        if ($store->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $this->tenantService->deleteStore($store);

            return response()->json([
                'message' => 'Store deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete store',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
