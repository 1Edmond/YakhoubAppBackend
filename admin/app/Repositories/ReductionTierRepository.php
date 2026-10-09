<?php

namespace App\Repositories;

use App\Contracts\Repositories\ReductionTierRepositoryInterface;
use App\Models\ReductionTier;

class ReductionTierRepository implements ReductionTierRepositoryInterface
{
    public function getAllActive()
    {
        return ReductionTier::active()->orderBy('tier_number')->get();
    }

    public function find($id)
    {
        return ReductionTier::find($id);
    }
}
