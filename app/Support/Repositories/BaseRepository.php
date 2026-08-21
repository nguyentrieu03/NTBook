<?php

namespace App\Support\Repositories;

use App\Support\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseRepository implements BaseRepositoryInterface
{
    abstract protected function model(): Model;

    protected function query(): Builder 
    {
        return $this->model()::query();
    }

    public function findById(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function deleteById(Model $model): bool
    {
        return $model->delete();
    }
}