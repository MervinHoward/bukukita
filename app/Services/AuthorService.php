<?php

namespace App\Services;

use App\Repositories\AuthorRepository;

class AuthorService {
    private AuthorRepository $authorRepository;

    public function __construct(AuthorRepository $authorRepository)
    {
        $this->authorRepository = $authorRepository;
    }

    public function getAll(array $fields = ['*']) {
        return $this->authorRepository->getAll($fields);
    }

    public function getById(int $id, array $fields = ['*']) {
        return $this->authorRepository->getById($id, $fields);
    }

    public function create(array $data) {
        $author = $this->authorRepository->create($data);
        return $author;
    }

    public function update(int $id, array $data) {
        $author = $this->authorRepository->update($id, $data);
        return $author;
    }
}
