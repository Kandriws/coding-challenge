<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class QuotationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_quotation(): void
    {
        $this->seed(CurrencySeeder::class);

        $user = $this->user();
        $response = $this->quote(user: $user);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Quotation created successfully.',
                'data' => [
                    'total' => '117.00',
                    'currency_id' => 'EUR',
                    'quotation_id' => 1,
                ],
            ]);

        $this->assertDatabaseHas('quotations', [
            'user_id' => $user->id,
            'ages' => '28,35',
            'currency_id' => 'EUR',
            'total' => '117.00',
        ]);
    }

    public function test_it_requires_authentication(): void
    {
        $this->postJson(route('quotation.store'), $this->payload())
            ->assertUnauthorized();
    }

    public function test_it_validates_the_currency(): void
    {
        $this->seed(CurrencySeeder::class);

        $this->quote(['currency_id' => 'ARS'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id');
    }

    public function test_it_validates_date_order(): void
    {
        $this->seed(CurrencySeeder::class);

        $this->quote([
            'start_date' => '2020-10-30',
            'end_date' => '2020-10-01',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('end_date');
    }

    public function test_it_returns_a_business_error_for_invalid_age(): void
    {
        $this->seed(CurrencySeeder::class);

        $this->quote(['age' => '17'])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Age 17 is outside the allowed range.',
            ]);
    }

    private function quote(array $overrides = [], ?User $user = null)
    {
        $user ??= $this->user();

        return $this
            ->withToken(JWTAuth::fromUser($user))
            ->postJson(route('quotation.store'), $this->payload($overrides));
    }

    private function user(): User
    {
        return User::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'age' => '28,35',
            'currency_id' => 'EUR',
            'start_date' => '2020-10-01',
            'end_date' => '2020-10-30',
        ], $overrides);
    }
}
