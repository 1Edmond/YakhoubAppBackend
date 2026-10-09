<?php

namespace App\Contracts\Repositories;

interface ReductionTierRepositoryInterface
{
    public function getAllActive();
    public function find($id);
}
