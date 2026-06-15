<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QuickPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class QuickPhotoController extends Controller
{
    public function uploadQuickPhoto(Request $request): JsonResponse
    {
        $uploadedPath = null;

        try {

            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            // image file
            $file = $request->file('image');

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image file not found',
                ], 422);
            }

            // store image
            $uploadedPath = $file->store('uploads', 'public');

            // save DB
            $quickPhoto = DB::transaction(function () use ($uploadedPath) {

                // create first
                $photo = QuickPhoto::create([
                    'image_path' => $uploadedPath,
                    'is_active' => true,
                ]);

                // generate custom id using db id
                $photo->custom_id =
                    'QP-' . str_pad($photo->id, 3, '0', STR_PAD_LEFT);

                $photo->save();

                return $photo;
            });

            return response()->json([
                'success' => true,
                'message' => 'Image upload success',
                'data' => [
                    'id' => $quickPhoto->id,
                    'custom_id' => $quickPhoto->custom_id,
                    'image_path' => $quickPhoto->image_path,
                    'image_url' => asset('storage/' . $quickPhoto->image_path),
                ],
            ], 201);
        } catch (Throwable $e) {

            // delete uploaded file if DB failed
            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            // log error
            Log::error('Quick photo upload failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Image upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
