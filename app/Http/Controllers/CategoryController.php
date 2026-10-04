<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Category::class);
        $categories = $this->categoryService->getAll();
        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        Gate::authorize('create', Category::class);
        $updatedCategory = $this->categoryService->create($request->validated());
        return new CategoryResource($updatedCategory);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $category = $this->categoryService->getById($id);
            Gate::authorize('view', $category);
            return new CategoryResource($category);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id, CategoryRequest $request)
    {
        try {
            $category = $this->categoryService->getById($id);
            Gate::authorize('update', $category);
            $updatedCategory = $this->categoryService->update($id, $request->validated());
            return new CategoryResource($updatedCategory);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
