<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransferOperator;
use App\Models\TransferOperatorFee;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class TransferOperatorController extends Controller
{
    public function index()
    {
        $operators = TransferOperator::with('fees')->get();
        return view('admin-views.transfer-operators.index', compact('operators'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        
        TransferOperator::create($data);
        Toastr::success(translate('Operator_added_successfully'));
        return back();
    }

    public function addFee(Request $request, TransferOperator $operator)
    {
        $data = $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'fee_amount' => 'required|numeric|min:0',
            'fee_type' => 'required|in:fixed,percent',
        ]);
        
        $operator->fees()->create($data);
        Toastr::success(translate('Fee_added_successfully'));
        return back();
    }
}
