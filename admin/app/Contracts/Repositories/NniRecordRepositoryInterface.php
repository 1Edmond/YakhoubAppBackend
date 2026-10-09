<?php

namespace App\Contracts\Repositories;

interface NniRecordRepositoryInterface
{
    public function find($id);
    public function findByUserId($userId);
    public function findByNni($nni);
    public function create(array $data);
    public function update($id, array $data);
    public function getUnverified();
}
