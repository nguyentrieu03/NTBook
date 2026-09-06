<?php

namespace App\Support\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function findById(int $id): ?Model;
    public function deleteById(Model $model): bool;
}