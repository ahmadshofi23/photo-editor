<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface BaseRepository
{
    /**
     * Find a model by its ID.
     *
     * @param int|string $id
     * @return Model|null
     */
    public function find(int|string $id): ?Model;

    /**
     * Get all models.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Create a new model.
     *
     * @param array<string, mixed> $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update an existing model.
     *
     * @param int|string $id
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(int|string $id, array $data): bool;

    /**
     * Delete a model.
     *
     * @param int|string $id
     * @return bool
     */
    public function delete(int|string $id): bool;
}
