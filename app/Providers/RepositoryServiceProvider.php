<?php

namespace App\Providers;

use App\Domains\Auth\Contracts\AuthServiceInterface;
use App\Domains\Auth\Services\AuthService;
use App\Domains\User\Contracts\{UserRepositoryInterface, UserServiceInterface};
use App\Domains\User\Repositories\{CachedUserRepository, UserRepository};
use App\Domains\User\Services\UserService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // User domain
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new CachedUserRepository(
                $app->make(UserRepository::class),
            );
        });

        $this->app->bind(UserServiceInterface::class, UserService::class);

        // Auth domain
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }
}