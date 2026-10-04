<?php

namespace App\Services;

use App\Repositories\PublisherRepository;

class PublisherService {
    private PublisherRepository $publisherRepository;

    public function __construct(PublisherRepository $publisherRepository)
    {
        $this->publisherRepository = $publisherRepository;
    }

    public function getAll(array $fields = ['*']) {
        return $this->publisherRepository->getAll($fields);
    }

    public function getById(int $id, array $fields = ['*']) {
        return $this->publisherRepository->getById($id, $fields);
    }

    public function create(array $data) {
        $publisher = $this->publisherRepository->create($data);
        return $publisher;
    }

    public function update(int $id, array $data) {
        $publisher = $this->publisherRepository->update($id, $data);
        return $publisher;
    }
}
