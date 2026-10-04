<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use App\Services\AuthorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class AuthorController extends Controller
{
    private AuthorService $authorService;

    public function __construct(AuthorService $authorService)
    {
        $this->authorService = $authorService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Author::class);
        $authors = $this->authorService->getAll();
        return AuthorResource::collection($authors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AuthorRequest $request)
    {
        Gate::authorize('create', Author::class);
        $author = $this->authorService->create($request->validated());
        return new AuthorResource($author);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $author = $this->authorService->getById($id);
            Gate::authorize('view', $author);
            return new AuthorResource($author);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id, AuthorRequest $request)
    {
        try {
            $author = $this->authorService->getById($id, ['*']);
            Gate::authorize('update', $author);
            $updatedAuthor = $this->authorService->update($id, $request->validated());
            return new AuthorResource($updatedAuthor);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
