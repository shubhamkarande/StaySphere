<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Cloudinary;

class UploadController extends Controller
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ]
        ]);
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'folder' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $folder = $request->get('folder', 'staysphere');
            $file = $request->file('image');
            
            $result = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => $folder,
                    'transformation' => [
                        'quality' => 'auto',
                        'fetch_format' => 'auto'
                    ]
                ]
            );

            return response()->json([
                'message' => 'Image uploaded successfully',
                'data' => [
                    'url' => $result['secure_url'],
                    'public_id' => $result['public_id'],
                    'width' => $result['width'],
                    'height' => $result['height'],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadMultipleImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'folder' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $folder = $request->get('folder', 'staysphere');
            $uploadedImages = [];

            foreach ($request->file('images') as $file) {
                $result = $this->cloudinary->uploadApi()->upload(
                    $file->getRealPath(),
                    [
                        'folder' => $folder,
                        'transformation' => [
                            'quality' => 'auto',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );

                $uploadedImages[] = [
                    'url' => $result['secure_url'],
                    'public_id' => $result['public_id'],
                    'width' => $result['width'],
                    'height' => $result['height'],
                ];
            }

            return response()->json([
                'message' => 'Images uploaded successfully',
                'data' => $uploadedImages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'public_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->cloudinary->uploadApi()->destroy($request->public_id);

            if ($result['result'] === 'ok') {
                return response()->json([
                    'message' => 'Image deleted successfully'
                ]);
            }

            return response()->json([
                'message' => 'Failed to delete image'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}