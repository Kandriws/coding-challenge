<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

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
