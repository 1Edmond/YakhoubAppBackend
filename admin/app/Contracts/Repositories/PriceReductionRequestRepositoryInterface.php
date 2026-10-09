<?php

namespace App\Contracts\Repositories;

interface PriceReductionRequestRepositoryInterface
{
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function getForCustomer($customerId);
    public function getForVendor($vendorId);
}
