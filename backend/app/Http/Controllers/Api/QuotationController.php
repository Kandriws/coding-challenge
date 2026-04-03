<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Services\QuotationService;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class QuotationController extends Controller
{
    public function __construct(
        private readonly QuotationService $quotationService,
    ) {}

    public function store(StoreQuotationRequest $request)
    {
        try {
            $quotation = $this->quotationService->create(
                $request->validated(),
                $request->user()->id,
            );

            return $this->ok(
                QuotationResource::make($quotation)->resolve(),
                'Quotation created successfully.',
                Response::HTTP_CREATED,
            );
        } catch (InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
