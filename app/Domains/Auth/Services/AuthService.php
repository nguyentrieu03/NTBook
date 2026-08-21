<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Contracts\AuthServiceInterface;
use App\Domains\Auth\DTOs\{ForgotPasswordDTO, RegisterDTO, ResetPasswordDTO, UpdatePasswordDTO, UpdateProfileDTO};
use App\Domains\User\Contracts\UserServiceInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\{PasswordReset, Registered};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Password};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserServiceInterface $userService,
    ) {}

    public function register(RegisterDTO $dto): User
    {
        $user = $this->userService->create($dto->toCreateUserDTO());

        event(new Registered($user));

        Auth::login($user);

        return $user;
    }

    public function login(LoginRequest $request): void
    {
        $request->authenticate();

        $request->session()->regenerate();
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }

    public function confirmPassword(User $user, string $password): void
    {
        if (! Auth::guard('web')->validate(['email' => $user->email, 'password' => $password])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }
    }

    public function sendPasswordResetLink(ForgotPasswordDTO $dto): string
    {
        return Password::sendResetLink(['email' => $dto->email]);
    }

    public function resetPassword(ResetPasswordDTO $dto): string
    {
        return Password::reset(
            ['email' => $dto->email, 'password' => $dto->password, 'token' => $dto->token],
            function (User $user) use ($dto) {
                $user->forceFill([
                    'password'       => Hash::make($dto->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
    }

    public function updatePassword(User $user, UpdatePasswordDTO $dto): void
    {
        $user->update(['password' => Hash::make($dto->password)]);
    }

    public function updateProfile(User $user, UpdateProfileDTO $dto): User
    {
        $user->fill(['name' => $dto->name, 'email' => $dto->email]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user->fresh();
    }
}
