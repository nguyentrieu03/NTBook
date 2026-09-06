<?php

namespace App\Support\Repositories;

use App\Support\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /** @return class-string<Model> */
    abstract protected function model(): string;

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