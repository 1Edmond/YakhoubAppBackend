<?php

namespace App\Services;

use App\Models\User;
use App\Models\NniRecord;
use App\Contracts\Repositories\NniRecordRepositoryInterface;
use Illuminate\Http\UploadedFile;

class NniService
{
    protected $nniRepo;

    public function __construct(NniRecordRepositoryInterface $nniRepo)
    {
        $this->nniRepo = $nniRepo;
    }

    public function validateNniFormat(string $nni): bool
    {
        return strlen($nni) >= 5 && strlen($nni) <= 20;
    }

    public function checkUniqueness(string $nni, ?int $excludeUserId = null): bool
    {
        $query = NniRecord::where('nni_number', $nni);
        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }
        return $query->doesntExist();
    }

    public function storeNniRecord(User $user, array $data)
    {
        $record = $this->nniRepo->findByUserId($user->id);
        if ($record) {
            return $this->nniRepo->update($record->id, $data);
        }
        $data['user_id'] = $user->id;
        return $this->nniRepo->create($data);
    }

    public function verifyNni(int $nniRecordId, int $adminId)
    {
        return $this->nniRepo->update($nniRecordId, [
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $adminId
        ]);
    }

    public function uploadDocumentPhoto(UploadedFile $file, NniRecord $nniRecord, string $storage = 'local')
    {
        $path = $file->store('nni_documents', $storage);
        $this->nniRepo->update($nniRecord->id, [
            'document_photo_url' => $path,
            'document_photo_storage' => $storage
        ]);
        return $path;
    }
}
