<?php

namespace App\Repositories;

use App\Enums\BookStatusEnum;
use App\Models\Book;
use App\Models\BookExemplar;

class BookRepository
{
    public function getAll(array $fields = ['*'], ?string $search = null)
    {
        $search = trim($search);
        $books = Book::select($fields)
        ->when($search, function ($query) use ($search) {
            // 1. Bersihkan input khusus untuk pengecekan ISBN (hapus strip dan spasi)
            $cleanIsbn = str_replace(['-', ' '], '', $search);

            // 2. Cek apakah pola input sesuai dengan ISBN-10 atau ISBN-13
            $isIsbn10 = preg_match('/^[0-9]{9}[0-9X]$/i', $cleanIsbn);
            $isIsbn13 = preg_match('/^(978|979)[0-9]{10}$/', $cleanIsbn);

            $query->where(function ($q) use ($search, $cleanIsbn, $isIsbn10, $isIsbn13) {
                if ($isIsbn10 || $isIsbn13) {
                    // Jika terdeteksi ISBN: cari tepat berdasarkan ISBN murni atau teks aslinya
                    $q->where('isbn', 'like', "%{$cleanIsbn}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
                } else {
                    // Jika teks/judul: cari berdasarkan judul atau atribut teks lain (seperti nama penulis)
                    $q->where('title', 'like', "%{$search}%");
                }
            });
        })
        ->with(['author', 'publisher'])
        ->get();
        \Illuminate\Support\Facades\Log::info("Search params: {$search}");
        \Illuminate\Support\Facades\Log::info("Data Buku" . json_encode($books));
        return $books;
    }

    public function getById(int $id, array $fields)
    {
        $book = Book::select($fields)->with(['author', 'publisher', 'shelf', 'categories', 'exemplars'])->findOrFail($id);
        return $book;
    }

    public function create(array $data)
    {
        $book = Book::create($data);
        return $book;
    }

    public function update(int $id, array $data)
    {
        $book = Book::findOrFail($id);
        $book->update($data);
        return $book;
    }

    public function getBookExemplarById(int $id) {
        $exemplar = BookExemplar::findOrFail($id);
        return $exemplar;
    }

    public function addBookExemplar(int $id, array $data) {
        $book = Book::findOrFail($id);
        $book->exemplars()->create($data);
        return $book;
    }

    public function updateExemplarStatus(int $id, BookStatusEnum $status) {
        $exemplar = BookExemplar::findOrFail($id);
        $exemplar->update([
            'status' => $status
        ]);
        return $exemplar;
    }
}
