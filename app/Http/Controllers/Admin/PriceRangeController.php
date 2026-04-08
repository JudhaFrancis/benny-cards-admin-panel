<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PriceRangeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $ranges = PriceRange::with(['addedBy', 'modifiedBy'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $ranges
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:price_ranges,slug',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|gt:min_price',
            'photo' => 'required|image|max:2048',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = PriceRange::generateSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadPath = public_path('uploads/price_ranges');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);
            $data['photo'] = 'uploads/price_ranges/' . $filename;
        }

        $data['added_by'] = Auth::id();

        $priceRange = PriceRange::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Price Range created successfully',
            'data' => $priceRange->load(['addedBy', 'modifiedBy'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceRange $priceRange)
    {
        return response()->json([
            'success' => true,
            'data' => $priceRange->load(['addedBy', 'modifiedBy'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PriceRange $priceRange)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', Rule::unique('price_ranges')->ignore($priceRange->id)],
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|gt:min_price',
            'photo' => 'nullable|image|max:2048',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = PriceRange::generateSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($priceRange->photo && file_exists(public_path($priceRange->photo))) {
                unlink(public_path($priceRange->photo));
            }

            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadPath = public_path('uploads/price_ranges');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);
            $data['photo'] = 'uploads/price_ranges/' . $filename;
        }

        $data['modified_by'] = Auth::id();

        $priceRange->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Price Range updated successfully',
            'data' => $priceRange->load(['addedBy', 'modifiedBy'])
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(PriceRange $priceRange)
    {
        // Delete photo if exists
        if ($priceRange->photo && file_exists(public_path($priceRange->photo))) {
            unlink(public_path($priceRange->photo));
        }

        $priceRange->delete();

        return response()->json([
            'success' => true,
            'message' => 'Price Range deleted successfully'
        ]);
    }
}
