<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            $file = $request->file('file');
            $filename = uniqid('editor_') . '.webp';
            $path = 'uploads/editor/' . $filename;

            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->decode($file->getPathname());
            
            // scale down if too large, max width 1200
            $image->scaleDown(width: 1200);

            // encode as webp
            $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(80));

            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $encoded->toString());
            
            return response()->json([
                'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($path)
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
