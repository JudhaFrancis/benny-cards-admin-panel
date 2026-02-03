<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $banners = Banner::with(['addedBy', 'modifiedBy'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('text', 'like', "%{$search}%");
                });
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $banners
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:banners,slug',
            'photo' => 'required|image|max:2048',
            'text' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Banner::generateSlug($data['title']);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadPath = public_path('uploads/banners');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);
            $data['photo'] = 'uploads/banners/' . $filename;
        }

        $data['added_by'] = Auth::id();

        $banner = Banner::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully',
            'data' => $banner->load(['addedBy', 'modifiedBy'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return response()->json([
            'success' => true,
            'data' => $banner->load(['addedBy', 'modifiedBy'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', Rule::unique('banners')->ignore($banner->id)],
            'photo' => 'nullable|image|max:2048',
            'text' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Banner::generateSlug($data['title']);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($banner->photo && file_exists(public_path($banner->photo))) {
                unlink(public_path($banner->photo));
            }

            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadPath = public_path('uploads/banners');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);
            $data['photo'] = 'uploads/banners/' . $filename;
        }

        $data['modified_by'] = Auth::id();

        $banner->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully',
            'data' => $banner->load(['addedBy', 'modifiedBy'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        // Delete photo from storage even on soft delete as per user request
        if ($banner->photo && file_exists(public_path($banner->photo))) {
            unlink(public_path($banner->photo));
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully'
        ]);
    }
}
