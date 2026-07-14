<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Support\ApiResponse;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepo
    ) {}

    public function store(StorePaymentRequest $request)
    {
        $payment = $this->paymentRepo->create($request->validated() + ['status' => 'pending']);
        return ApiResponse::success('Payment created successfully', new PaymentResource($payment), 201);
    }

    public function index()
    {
        $payments = \App\Models\Payment::paginate(15);
        return ApiResponse::success('Payments retrieved', PaymentResource::collection($payments));
    }

    public function confirm(string $id)
    {
        $payment = $this->paymentRepo->findOrFail($id);
        
        if ($payment->status !== 'pending') {
            return ApiResponse::error('Payment already processed', 409);
        }

        $payment = $this->paymentRepo->update($payment, ['status' => 'paid', 'payment_date' => now()]);
        return ApiResponse::success('Payment confirmed', new PaymentResource($payment));
    }
}
