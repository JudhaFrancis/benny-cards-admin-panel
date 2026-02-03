<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::with(['parent', 'addedBy', 'modifiedBy'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->is_parent !== null && $request->is_parent !== '', function ($query) use ($request) {
                $query->where('is_parent', $request->is_parent);
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'summary' => 'nullable|string',
            'photo' => 'required|image|max:2048',
            'is_parent' => 'required|in:0,1',
            'parent_id' => 'nullable',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Category::generateSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            // Create directory if it doesn't exist
            $uploadPath = public_path('uploads/categories');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);
            $data['photo'] = 'uploads/categories/' . $filename;
        }

        // Set added_by to current user
        $data['added_by'] = Auth::id();

        // Validate parent/child logic
        if (!$data['is_parent'] && empty($data['parent_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Parent category is required for child categories'
            ], 422);
        }

        if ($data['is_parent']) {
            $data['parent_id'] = null;
        }

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category->load(['parent', 'addedBy', 'modifiedBy'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'data' => $category->load(['parent', 'children', 'addedBy', 'modifiedBy'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', Rule::unique('categories')->ignore($category->id)],
            'summary' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'is_parent' => 'required|in:0,1',
            'parent_id' => 'nullable',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Category::generateSlug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($category->photo && file_exists(public_path($category->photo))) {
                unlink(public_path($category->photo));
            }

            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadPath = public_path('uploads/categories');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);
            $data['photo'] = 'uploads/categories/' . $filename;
        }

        // Validate parent/child logic
        if (!$data['is_parent'] && empty($data['parent_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Parent category is required for child categories'
            ], 422);
        }

        // Prevent setting self as parent
        if (!empty($data['parent_id']) && $data['parent_id'] == $category->id) {
            return response()->json([
                'success' => false,
                'message' => 'Category cannot be its own parent'
            ], 422);
        }

        if ($data['is_parent']) {
            $data['parent_id'] = null;
        }

        // Track who modified this category
        $data['modified_by'] = Auth::id();

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->load(['parent', 'addedBy', 'modifiedBy'])
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Category $category)
    {
        // Check if category has children
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with child categories'
            ], 422);
        }

        // Delete photo if exists
        if ($category->photo && file_exists(public_path($category->photo))) {
            unlink(public_path($category->photo));
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
