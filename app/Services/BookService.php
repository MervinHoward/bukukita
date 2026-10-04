<?php

namespace App\Services;

use App\Enums\BookStatusEnum;
use App\Repositories\BookRepository;
use Illuminate\Support\Facades\DB;

class BookService
{
    private BookRepository $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function getAll(array $fields, ?string $search = null)
    {
        $books = $this->bookRepository->getAll($fields, $search);
        return $books;
    }

    public function getById(int $id, array $fields)
    {
        $book = $this->bookRepository->getById($id, $fields);
        return $book;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $book = $this->bookRepository->create($data);
            if (!empty($data['categories'])) {
                $book->categories()->sync($data['categories']);
            }
            $book->load(['author', 'publisher', 'shelf', 'categories']);
            return $book;
        });
    }

    public function update(int $id, array $data) {
        return DB::transaction(function () use ($data, $id) {
            $book = $this->bookRepository->update($id, $data);
            $book->categories()->sync($data['categories']);
            $book->load(['author', 'publisher', 'shelf', 'categories']);
            return $book;
        });
    }

    public function getExemplarById(int $id) {
        $exemplar = $this->bookRepository->getBookExemplarById($id);
        return $exemplar;
    }

    public function addBookExemplar(int $id, array $data) {
        $exemplar = $this->bookRepository->addBookExemplar($id, $data);
        return $exemplar;
    }

    public function updateExemplarStatus(int $id, BookStatusEnum $status) {
        $exemplar = $this->bookRepository->updateExemplarStatus($id, $status);
        return $exemplar;
    }
}
