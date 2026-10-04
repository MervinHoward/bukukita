<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShelfRequest;
use App\Http\Resources\ShelfResource;
use App\Models\Shelf;
use App\Services\ShelfService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class ShelfController extends Controller
{
    private ShelfService $shelfService;

    public function __construct(ShelfService $shelfService)
    {
        $this->shelfService = $shelfService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Shelf::class);
        $shelves = $this->shelfService->getAll();
        return ShelfResource::collection($shelves);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ShelfRequest $request)
    {
        Gate::authorize('create', Shelf::class);
        $shelf = $this->shelfService->create($request->validated());
        return new ShelfResource($shelf);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $shelf = $this->shelfService->getById($id);
            Gate::authorize('view', $shelf);
            return new ShelfResource($shelf);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id, ShelfRequest $request)
    {
        try {
            $shelf = $this->shelfService->getById($id);
            Gate::authorize('update', $shelf);
            $updatedShelf = $this->shelfService->update($id, $request->validated());
            return new ShelfResource($updatedShelf);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
