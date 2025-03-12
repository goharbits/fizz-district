<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Upload an image and return the image path.
     */
    public function uploadImages(Request $request)
    {
        // Validate that the request contains an array of images
        $rules = [
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Each file must be an image and max size 2MB
        ];

        // Perform validation
        $validation = ValidatorHelper::validate($request->all(), $rules);

        if (!$validation['success']) {
            return ResponseHelper::formatResponse(false, [], $validation['errors']);
        }

        $uploadedImages = [];

        // Process and upload each image
        foreach ($request->file('images') as $image) {
            // Store the image in the 'public/uploaded_images' directory
            $imagePath = $image->store('uploaded_images', 'public');

            // Add the storage URL to the array
            $uploadedImages[] = asset(Storage::url($imagePath));
        }

        // Return the paths of uploaded images
        return ResponseHelper::formatResponse(true, ['image_paths' => $uploadedImages], 'Images uploaded successfully.');
    }
}
