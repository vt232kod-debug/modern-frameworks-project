<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends ApiController
{
    public function index(): JsonResponse
    {
        return response()->json(Customer::all());
    }

    public function store(Request $request): JsonResponse
    {
        $customer = Customer::create($this->validatePayload($request, $this->rules()));

        return response()->json($customer, 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $customer->update($this->validatePayload($request, $this->rules($customer)));

        return response()->json($customer);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(null, 204);
    }

    private function rules(?Customer $customer = null): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:180', Rule::unique('customers')->ignore($customer)],
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date_format:Y-m-d', 'before:today'],
        ];
    }
}
