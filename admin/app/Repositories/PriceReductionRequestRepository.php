<?php

namespace App\Repositories;

use App\Contracts\Repositories\PriceReductionRequestRepositoryInterface;
use App\Models\PriceReductionRequest;

class PriceReductionRequestRepository implements PriceReductionRequestRepositoryInterface
{
    public function find($id)
    {
        return PriceReductionRequest::find($id);
    }

    public function create(array $data)
    {
        return PriceReductionRequest::create($data);
    }

    public function update($id, array $data)
    {
        $request = $this->find($id);
        if ($request) {
            $request->update($data);
            return $request;
        }
        return null;
    }

    public function getForCustomer($customerId)
    {
        return PriceReductionRequest::forCustomer($customerId)->get();
    }

    public function getForVendor($vendorId)
    {
        return PriceReductionRequest::forVendor($vendorId)->get();
    }
}
