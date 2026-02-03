<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::with(['category', 'childCategory', 'brand', 'addedBy', 'modifiedBy', 'images'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%");
                });
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->cat_id, function ($query, $catId) {
                $query->where('cat_id', $catId);
            })
            ->when($request->brand_id, function ($query, $brandId) {
                $query->where('brand_id', $brandId);
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug',
            'summary' => 'required|string',
            'description' => 'nullable|string',
            'photo' => 'required|image|max:1024',
            'images' => 'nullable|array',
            'images.*' => 'image|max:1024',
            'stock' => 'required|integer|min:0',
            'size' => 'nullable|string',
            'condition' => ['required', Rule::in(['default', 'new', 'hot'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'is_featured' => 'boolean',
            'cat_id' => 'required|exists:categories,id',
            'child_cat_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'type' => ['required', Rule::in(['card', 'gift'])],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Product::generateSlug($data['title']);
        }

        $data['is_featured'] = $request->boolean('is_featured');

        $subFolder = $data['type'] === 'gift' ? 'gifts' : 'invitation_cards';

        // Handle main photo upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadPath = public_path('uploads/products/' . $subFolder);
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);
            $data['photo'] = 'uploads/products/' . $subFolder . '/' . $filename;
        }

        $data['added_by'] = Auth::id();

        $product = Product::create($data);

        // Handle gallery images upload
        if ($request->hasFile('images')) {
            $galleryPath = public_path('uploads/products/' . $subFolder . '/gallery');
            if (!File::exists($galleryPath)) {
                File::makeDirectory($galleryPath, 0777, true);
            }

            foreach ($request->file('images') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($galleryPath, $filename);

                $product->images()->create([
                    'image_path' => 'uploads/products/' . $subFolder . '/gallery/' . $filename
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load(['category', 'brand', 'addedBy', 'images'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product->load(['category', 'childCategory', 'brand', 'addedBy', 'modifiedBy', 'images'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', Rule::unique('products')->ignore($product->id)],
            'summary' => 'required|string',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:1024',
            'images.*' => 'nullable|image|max:1024',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'integer|exists:product_images,id',
            'stock' => 'required|integer|min:0',
            'size' => 'nullable|string',
            'condition' => ['required', Rule::in(['default', 'new', 'hot'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'is_featured' => 'boolean',
            'cat_id' => 'required|exists:categories,id',
            'child_cat_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'type' => ['required', Rule::in(['card', 'gift'])],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Product::generateSlug($data['title']);
        }

        $data['is_featured'] = $request->boolean('is_featured');

        $subFolder = $data['type'] === 'gift' ? 'gifts' : 'invitation_cards';

        // Handle main photo upload
        if ($request->hasFile('photo')) {
            if ($product->photo && File::exists(public_path($product->photo))) {
                File::delete(public_path($product->photo));
            }

            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadPath = public_path('uploads/products/' . $subFolder);
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $filename);
            $data['photo'] = 'uploads/products/' . $subFolder . '/' . $filename;
        }

        // Remove deleted images from gallery
        if ($request->deleted_images) {
            $imagesToDelete = ProductImage::whereIn('id', $request->deleted_images)
                ->where('product_id', $product->id)
                ->get();

            foreach ($imagesToDelete as $img) {
                if (File::exists(public_path($img->image_path))) {
                    File::delete(public_path($img->image_path));
                }
                $img->delete();
            }
        }

        // Handle additional gallery images
        if ($request->hasFile('images')) {
            $galleryPath = public_path('uploads/products/' . $subFolder . '/gallery');
            if (!File::exists($galleryPath)) {
                File::makeDirectory($galleryPath, 0777, true);
            }

            foreach ($request->file('images') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($galleryPath, $filename);

                $product->images()->create([
                    'image_path' => 'uploads/products/' . $subFolder . '/gallery/' . $filename
                ]);
            }
        }

        $data['modified_by'] = Auth::id();
        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->load(['category', 'brand', 'modifiedBy', 'images'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete main photo
        if ($product->photo && File::exists(public_path($product->photo))) {
            File::delete(public_path($product->photo));
        }

        // Delete all gallery images physically
        foreach ($product->images as $img) {
            if (File::exists(public_path($img->image_path))) {
                File::delete(public_path($img->image_path));
            }
            $img->delete();
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Get options for products (categories, brands)
     */
    public function options()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => Category::where('status', 'active')->where('is_parent', 1)->with('children')->get(),
                'brands' => Brand::where('status', 'active')->get(),
            ]
        ]);
    }
}
