<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * @unauthenticated
     */
    #[BodyParameter('email', description: 'User email address.', type: 'string', format: 'email', required: true, example: 'test@example.com')]
    #[BodyParameter('password', description: 'User password.', type: 'string', required: true, example: 'password')]
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->fail('Invalid credentials.', 401);
            }
        } catch (JWTException $e) {
            return $this->fail('Could not create token.', 500);
        }

        return $this->ok(['token' => $token], 'Login successful.');
    }
}
