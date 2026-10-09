<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NniService;
use App\Models\NniRecord;

class NniController extends Controller
{
    protected $nniService;

    public function __construct(NniService $nniService)
    {
        $this->nniService = $nniService;
    }

    public function verify(Request $request)
    {
        $request->validate(['nni' => 'required|string|max:20']);
        $isUnique = $this->nniService->checkUniqueness($request->nni);
        
        return response()->json([
            'is_unique' => $isUnique,
            'message' => $isUnique ? 'NNI is available' : 'NNI is already in use'
        ]);
    }

    public function uploadDocument(Request $request)
    {
        $request->validate(['document' => 'required|image|max:2048']);
        $user = $request->user();
        $record = NniRecord::where('user_id', $user->id)->firstOrFail();
        
        $path = $this->nniService->uploadDocumentPhoto(
            $request->file('document'), 
            $record, 
            config('mobile_payment.nni_document_storage', 'local')
        );
        
        return response()->json(['path' => $path, 'message' => 'Uploaded successfully']);
    }
}
