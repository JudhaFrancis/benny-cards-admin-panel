<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::with(['product', 'user']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('reviewer_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('product_id') && !empty($request->product_id)) {
            $query->where('product_id', $request->product_id);
        }

        $reviews = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'reviewer_name' => 'required_without:user_id|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:active,inactive',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/reviews'), $filename);
                    $imagePaths[] = 'uploads/reviews/' . $filename;
                }
            }

            $review = ProductReview::create([
                'user_id' => $request->user_id,
                'product_id' => $request->product_id,
                'reviewer_name' => $request->user_id ? null : $request->reviewer_name, // Use user name dynamically if user_id present? Model handles relation.
                'title' => $request->title,
                'description' => $request->description,
                'rating' => $request->rating,
                'status' => $request->status,
                'image' => count($imagePaths) > 0 ? $imagePaths : null,
            ]);

            // If user_id is present, we might want to capture reviewer_name snapshot or just rely on relation.
            // Requirement didn't specify, but typical "reviewer_name" is for guests or override.
            if ($request->has('reviewer_name')) {
                $review->reviewer_name = $request->reviewer_name;
                $review->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review created successfully',
                'data' => $review
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $review = ProductReview::with(['product', 'user'])->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    public function update(Request $request, $id)
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'rating' => 'sometimes|integer|min:1|max:5',
            'status' => 'sometimes|in:active,inactive',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'existing_images' => 'nullable|array' // Paths of images to keep
        ]);

        try {
            DB::beginTransaction();

            // Handle images
            $currentImages = $review->image ?? [];
            $newImagePaths = [];

            // 1. Keep existing images that were sent back
            if ($request->has('existing_images')) {
                $newImagePaths = array_intersect($currentImages, $request->existing_images);
            } else {
                // If existing_images not sent at all (e.g. not a form submission that handles files), careful not to wipe? 
                // Using 'sometimes' validation logic usually implies partial update.
                // But for file uploads typically we send everything.
                // Let's assume frontend sends 'existing_images' array of strings.
                // If not present, we assume keeping all? No, usually empty array meant remove all.
                // Let's stick to: if 'images' key exists (even empty), we process images.
                // Better approach: append new to existing?
                // Logic:
                // Frontend sends `existing_images` (list of paths to KEEP)
                // Frontend sends `images` (list of NEW files)
                // Result = Merge(existing_images, new_uploaded_images)
            }

            // Refined Logic
            $finalImages = $request->input('existing_images', []);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/reviews'), $filename);
                    $finalImages[] = 'uploads/reviews/' . $filename;
                }
            }
            // Sort keys reset
            $finalImages = array_values($finalImages);

            $data = $request->except(['images', 'existing_images']);
            $data['image'] = count($finalImages) > 0 ? $finalImages : null;

            $review->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => $review
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $review->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }
}
