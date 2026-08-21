<?php

namespace App\Http\Controllers\Auth;

use App\Domains\Auth\Contracts\AuthServiceInterface;
use App\Domains\Auth\DTOs\UpdatePasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {}

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->authService->updatePassword(
            $request->user(),
            UpdatePasswordDTO::fromRequest($request->validated()),
        );

        return back()->with('status', 'password-updated');
    }
}
