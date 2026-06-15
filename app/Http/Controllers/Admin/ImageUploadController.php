<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileUpload\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageUploadController extends Controller
{
    protected ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    public function uploadStudentImage(Request $request)
    {
        return $this->uploadImage($request, 'uploads');
    }

    public function uploadQuickPhoto(Request $request)
    {
        return $this->uploadImage($request, 'uploads');
    }

    private function uploadImage(Request $request, string $folder)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid image upload.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $path = $this->imageUploadService->upload(
                $request->file('image'),
                $folder
            );

            return response()->json([
                'status' => true,
                'message' => 'Image uploaded successfully.',
                'path' => $path,
                'url' => asset('storage/' . $path),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Image upload failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $this->imageUploadService->delete($request->path);

            return response()->json([
                'status' => true,
                'message' => 'Image deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Image delete failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
