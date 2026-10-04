<?php

namespace App\Http\Controllers;

use App\Enums\BookStatusEnum;
use App\Http\Requests\BookRequest;
use App\Http\Requests\StoreBookExemplarRequest;
use App\Http\Requests\UpdateBookExemplarStatusRequest;
use App\Http\Resources\BookExemplarResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\BookExemplar;
use App\Services\BookService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookController extends Controller
{
    private BookService $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Book::class);
        $books = $this->bookService->getAll(['*'], $request->input("search") ?? null);
        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookRequest $request)
    {
        Gate::authorize('create', Book::class);
        $book = $this->bookService->create($request->validated());
        return response()->json(new BookResource($book), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $book = $this->bookService->getById($id, ['*']);
            Gate::authorize('view', $book);
            return new BookResource($book);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id, BookRequest $request)
    {
        try {
            $book = $this->bookService->getById($id, ['*']);
            Gate::authorize('update', $book);
            $updatedBook = $this->bookService->update($book->id, $request->validated());
            return new BookResource($updatedBook);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function createBookExemplar(int $id, StoreBookExemplarRequest $request)
    {
        Gate::authorize('create', BookExemplar::class);
        $exemplar = $this->bookService->addBookExemplar($id, $request->validated());
        return (new BookExemplarResource($exemplar))->response()->setStatusCode(201);
    }

    public function updateBookExemplarStatus(int $id, UpdateBookExemplarStatusRequest $request)
    {
        try {
            $bookExemplar = $this->bookService->getExemplarById($id);
            $status = $request->enum('status', BookStatusEnum::class);
            Gate::authorize('update', $bookExemplar);
            $updatedExemplar = $this->bookService->updateExemplarStatus($id, $status);
            return new BookExemplarResource($updatedExemplar);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
