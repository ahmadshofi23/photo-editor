<?php

declare(strict_types=1);

namespace App\Modules\ImageUpload\Repositories;

use App\Models\Image;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ImageUploadRepository implements BaseRepository
{
    public function find(int|string $id): ?Model
    {
        return Image::find($id);
    }

    public function all(): Collection
    {
        return Image::all();
    }

    public function create(array $data): Model
    {
        return Image::create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $image = Image::find($id);
        if (!$image) return false;
        
        return $image->update($data);
    }

    public function delete(int|string $id): bool
    {
        $image = Image::find($id);
        if (!$image) return false;

        return $image->delete();
    }

    /**
     * @param int|string $userId
     * @return Collection
     */
    public function findByUser(int|string $userId): Collection
    {
        return Image::where('user_id', $userId)->latest()->get();
    }

    /**
     * @param int|string $userId
     * @param array $data
     * @return Model
     */
    public function save(int|string $userId, array $data): Model
    {
        $data['user_id'] = $userId;
        return $this->create($data);
    }

    /**
     * Get count of uploads by user today.
     *
     * @param int|string $userId
     * @return int
     */
    public function countUserUploadsToday(int|string $userId): int
    {
        return Image::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();
    }
}
