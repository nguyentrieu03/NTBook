<?php

namespace App\Domains\User\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function created(User $user): void
    {
        Log::channel('single')->info("User #{$user->id} created successfully");
    }

    public function deleted(User $user): void
    {
        Log::warning("User #{$user->id} deleted successfully");
    }
}