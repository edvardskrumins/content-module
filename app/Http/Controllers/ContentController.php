<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Http\Resources\ContentResourceCollection;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ContentController extends Controller
{
    /**
     * Display a listing of contents.
     */
    public function index(Request $request): ContentResourceCollection
    {
        $contents = Content::paginate($request->get('per_page', 15));

        return new ContentResourceCollection($contents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContentRequest $request): ContentResource
    {
        $content = Content::create($request->validated());

        return new ContentResource($content);
    }

    /**
     * Display the specified content.
     */
    public function show(string $id): ContentResource
    {
        $content = Content::findOrFail($id);

        return new ContentResource($content);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContentRequest $request, string $id): ContentResource
    {
        $content = Content::findOrFail($id);
        $content->update($request->validated());

        return new ContentResource($content);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): Response
    {
        $content = Content::findOrFail($id);
        $content->delete();

        return response()->noContent(HttpResponse::HTTP_NO_CONTENT);
    }
}
