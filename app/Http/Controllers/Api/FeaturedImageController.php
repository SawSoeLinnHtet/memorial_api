<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeaturedImage;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeaturedImageController extends Controller
{
    public function index(Request $request)
    {
        $featuredImages = FeaturedImage::query()
            ->with('collection')
            ->when($request->filled('collection_id'), function ($query) use ($request) {
                $query->where('collection_id', $request->integer('collection_id'));
            })
            ->latest('memorial_date')
            ->paginate($request->integer('per_page', 15));

        return response()->json($featuredImages);
    }

    public function store(Request $request, GoogleDriveService $googleDrive)
    {
        $validated = $request->validate([
            'memory_text' => ['required', 'string', 'max:10000'],
            'collection_id' => ['nullable', 'integer', 'exists:collections,id'],
            'image' => ['required', 'image', 'max:10240'],
            'memorial_date' => ['required', 'date'],
        ]);

        $validated['image_url'] = $googleDrive->upload($request->file('image'));
        unset($validated['image']);

        $featuredImage = FeaturedImage::query()->create($validated);

        return response()->json($featuredImage->load('collection'), Response::HTTP_CREATED);
    }

    public function show(FeaturedImage $featuredImage)
    {
        return response()->json($featuredImage->load('collection'));
    }

    public function update(Request $request, FeaturedImage $featuredImage, GoogleDriveService $googleDrive)
    {
        $validated = $request->validate([
            'memory_text' => ['sometimes', 'required', 'string', 'max:10000'],
            'collection_id' => ['sometimes', 'nullable', 'integer', 'exists:collections,id'],
            'image' => ['sometimes', 'required', 'image', 'max:10240'],
            'memorial_date' => ['sometimes', 'required', 'date'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $googleDrive->upload($request->file('image'));
        }

        unset($validated['image']);
        $featuredImage->update($validated);

        return response()->json($featuredImage->load('collection'));
    }

    public function destroy(FeaturedImage $featuredImage)
    {
        $featuredImage->delete();

        return response()->noContent();
    }
}
