<?php

namespace App\Domains\Auth\Contracts;

use App\Domains\Auth\DTOs\{ForgotPasswordDTO, RegisterDTO, ResetPasswordDTO, UpdatePasswordDTO, UpdateProfileDTO};
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    public function register(RegisterDTO $dto): User;

    public function login(LoginRequest $request): void;

    public function logout(Request $request): void;

    public function confirmPassword(User $user, string $password): void;

    public function sendPasswordResetLink(ForgotPasswordDTO $dto): string;

    public function resetPassword(ResetPasswordDTO $dto): string;

    public function updatePassword(User $user, UpdatePasswordDTO $dto): void;

    public function updateProfile(User $user, UpdateProfileDTO $dto): User;
}
