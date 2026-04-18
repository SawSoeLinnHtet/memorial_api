<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeaturedImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'memory_text' => ['required', 'string', 'max:10000'],
            'collection_id' => ['nullable', 'integer', 'exists:collections,id'],
            'image' => ['required', 'image', 'max:10240'],
            'memorial_date' => ['required', 'date'],
        ]);

        $path = $request->file('image')->store('featured-images', 'public');
        $validated['image_url'] = Storage::disk('public')->url($path);
        unset($validated['image']);

        $featuredImage = FeaturedImage::query()->create($validated);

        return response()->json($featuredImage->load('collection'), Response::HTTP_CREATED);
    }

    public function show(FeaturedImage $featuredImage)
    {
        return response()->json($featuredImage->load('collection'));
    }

    public function update(Request $request, FeaturedImage $featuredImage)
    {
        $validated = $request->validate([
            'memory_text' => ['sometimes', 'required', 'string', 'max:10000'],
            'collection_id' => ['sometimes', 'nullable', 'integer', 'exists:collections,id'],
            'image' => ['sometimes', 'required', 'image', 'max:10240'],
            'memorial_date' => ['sometimes', 'required', 'date'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('featured-images', 'public');
            $validated['image_url'] = Storage::disk('public')->url($path);
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
