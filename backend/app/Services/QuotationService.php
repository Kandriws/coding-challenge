<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Quotation;
use Carbon\Carbon;
use InvalidArgumentException;

class QuotationService
{
    private const int FIXED_RATE = 3;

    private const array AGE_LOADS = [
        [18, 30, 0.6],
        [31, 40, 0.7],
        [41, 50, 0.8],
        [51, 60, 0.9],
        [61, 70, 1.0],
    ];

    /**
     * Create a new quotation based on the provided data and user ID.
     *
     * @param array $data The validated input data for the quotation.
     * @param int $userId The ID of the user creating the quotation.
     * @return Quotation The created Quotation model instance.
     * @throws InvalidArgumentException If any validation fails during processing.
     */
    public function create(array $data, int $userId): Quotation
    {
        $ages = $this->parseAges($data['age']);

        [$startDate, $endDate] = $this->parseAndValidateDates($data['start_date'], $data['end_date']);

        $tripLength = $this->calculateInclusiveTripLength($startDate, $endDate);

        $total = $this->calculateTotal($ages, $tripLength);

        $pricingRules = $this->buildPricingRulesSnapshot($tripLength);

        return Quotation::create([
            'user_id' => $userId,
            'ages' => $data['age'],
            'currency_id' => $data['currency_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total' => $total,
            'pricing_rules' => $pricingRules,
        ]);
    }

    public function parseAges(string $ages): array
    {
        $parsedAges = array_map('trim', explode(',', $ages));

        foreach ($parsedAges as $age) {
            if (! ctype_digit($age)) {
                throw new InvalidArgumentException("Invalid age value: $age");
            }
        }

        $normalizedAges = array_map('intval', $parsedAges);

        foreach ($normalizedAges as $age) {
            if ($this->getAgeLoad($age) === null) {
                throw new InvalidArgumentException("Age $age is outside the allowed range.");
            }
        }

        return $normalizedAges;
    }

    public function calculateTotal(array $ages, int $tripLength): float
    {
        $total = 0;

        foreach ($ages as $age) {
            $ageLoad = $this->getAgeLoad($age);

            if ($ageLoad === null) {
                throw new InvalidArgumentException("Age $age is outside the allowed range.");
            }

            $total += self::FIXED_RATE * $ageLoad * $tripLength;
        }

        return round($total, 2);
    }

    private function getAgeLoad(int $age): ?float
    {
        foreach (self::AGE_LOADS as [$min, $max, $load]) {
            if ($age >= $min && $age <= $max) {
                return $load;
            }
        }

        return null;
    }

    private function calculateInclusiveTripLength(Carbon $startDate, Carbon $endDate): int
    {
        return (int) $startDate->diffInDays($endDate) + 1;
    }

    private function parseAndValidateDates(string $start, string $end): array
    {
        $startDate = $this->parseDate($start, 'start_date');
        $endDate = $this->parseDate($end, 'end_date');

        if ($startDate->gt($endDate)) {
            throw new InvalidArgumentException('start_date must be before or equal to end_date.');
        }

        return [$startDate, $endDate];
    }

    private function parseDate(string $date, string $field): Carbon
    {
        $parsedDate = Carbon::createFromFormat('Y-m-d', $date);

        if ($parsedDate === false || $parsedDate->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException("Invalid $field format, expected Y-m-d.");
        }

        return $parsedDate;
    }

    private function buildPricingRulesSnapshot(int $tripLength): array
    {
        return [
            'fixed_rate' => self::FIXED_RATE,
            'trip_length' => $tripLength,
            'age_loads' => self::AGE_LOADS,
            'generated_at' => Carbon::now()->toIsoString(),
        ];
    }
}
