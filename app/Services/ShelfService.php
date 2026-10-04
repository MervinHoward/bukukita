<?php

namespace App\Services;

use App\Repositories\ShelfRepository;

class ShelfService {
    private ShelfRepository $shelfRepository;

    public function __construct(ShelfRepository $shelfRepository)
    {
        $this->shelfRepository = $shelfRepository;
    }

    public function getAll() {
        return $this->shelfRepository->getAll();
    }

    public function getById(int $id) {
        $shelf = $this->shelfRepository->getById($id);
        return $shelf;
    }

    public function create(array $data) {
        $shelf = $this->shelfRepository->create($data);
        return $shelf;
    }

    public function update(int $id, array $data) {
        $shelf = $this->shelfRepository->update($id, $data);
        return $shelf;
    }
}
