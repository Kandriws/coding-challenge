<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Currency::query()->upsert([
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
            ],
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
            ],
        ], ['code'], ['name', 'symbol']);
    }
}
