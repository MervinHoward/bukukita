<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublisherRequest;
use App\Http\Resources\PublisherResource;
use App\Models\Publisher;
use App\Services\PublisherService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class PublisherController extends Controller
{
    private PublisherService $publisherService;

    public function __construct(PublisherService $publisherService)
    {
        $this->publisherService = $publisherService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Publisher::class);
        $publishers = $this->publisherService->getAll();
        return PublisherResource::collection($publishers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PublisherRequest $request)
    {
        Gate::authorize('create', Publisher::class);
        $publisher = $this->publisherService->create($request->validated());
        return new PublisherResource($publisher);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $publisher = $this->publisherService->getById($id);
            Gate::authorize('view', $publisher);
            return new PublisherResource($publisher);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id, PublisherRequest $request)
    {
        try {
            $publisher = $this->publisherService->getById($id, ['*']);
            Gate::authorize('update', $publisher);
            $updatedPublisher = $this->publisherService->update($id, $request->validated());
            return new PublisherResource($updatedPublisher);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
