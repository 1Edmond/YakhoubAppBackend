<?php

namespace App\Repositories;

use App\Contracts\Repositories\NniRecordRepositoryInterface;
use App\Models\NniRecord;

class NniRecordRepository implements NniRecordRepositoryInterface
{
    public function find($id)
    {
        return NniRecord::find($id);
    }

    public function findByUserId($userId)
    {
        return NniRecord::where('user_id', $userId)->first();
    }

    public function findByNni($nni)
    {
        return NniRecord::where('nni_number', $nni)->first();
    }

    public function create(array $data)
    {
        return NniRecord::create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    public function getUnverified()
    {
        return NniRecord::unverified()->get();
    }
}
