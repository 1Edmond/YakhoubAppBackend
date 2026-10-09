<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransferOperator;
use App\Models\TransferOperatorFee;
use Illuminate\Http\Request;

class TransferOperatorController extends Controller
{
    public function index()
    {
        $operators = TransferOperator::with('fees')->get();
        return response()->json($operators);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $operator = TransferOperator::create($data);
        return response()->json($operator);
    }

    public function addFee(Request $request, TransferOperator $operator)
    {
        $data = $request->validate([
            'min_amount' => 'numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'fee_amount' => 'required|numeric|min:0',
            'fee_type' => 'in:fixed,percent',
        ]);
        
        $fee = $operator->fees()->create($data);
        return response()->json($fee);
    }
}
