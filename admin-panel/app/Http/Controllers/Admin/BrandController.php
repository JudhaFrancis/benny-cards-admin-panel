<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brands = Brand::with(['addedBy', 'modifiedBy'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($request->title, function ($query, $title) {
                $query->where('title', 'like', "%{$title}%");
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->created_at, function ($query, $date) {
                $query->whereDate('created_at', $date);
            })
            ->when($request->updated_at, function ($query, $date) {
                $query->whereDate('updated_at', $date);
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:brands,slug',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Brand::generateSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $data['added_by'] = Auth::id();

        $brand = Brand::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data' => $brand->load(['addedBy', 'modifiedBy'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return response()->json([
            'success' => true,
            'data' => $brand->load(['addedBy', 'modifiedBy'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', Rule::unique('brands')->ignore($brand->id)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Brand::generateSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $data['modified_by'] = Auth::id();

        $brand->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data' => $brand->load(['addedBy', 'modifiedBy'])
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully'
        ]);
    }
}
