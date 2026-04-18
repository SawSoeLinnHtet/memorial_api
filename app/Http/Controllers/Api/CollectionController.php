<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $collections = Collection::query()
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($collections);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $collection = Collection::query()->create($validated);

        return response()->json($collection, Response::HTTP_CREATED);
    }

    public function show(Collection $collection)
    {
        return response()->json($collection->load('featuredImages'));
    }

    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $collection->update($validated);

        return response()->json($collection);
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return response()->noContent();
    }
}
