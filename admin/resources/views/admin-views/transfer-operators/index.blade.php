@extends('layouts.admin.app')

@section('title', translate('transfer_operators'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{asset('/public/assets/back-end/img/seller-list.png')}}" alt="">
                {{translate('transfer_operators')}}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{translate('add_new_operator')}}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.transfer-operators.store')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="title-color" for="name">{{translate('name')}}</label>
                                    <input type="text" name="name" class="form-control" id="name" placeholder="{{translate('ex')}}: Wave" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="title-color" for="is_active">{{translate('status')}}</label>
                                    <select class="form-control" name="is_active" id="is_active">
                                        <option value="1">{{translate('active')}}</option>
                                        <option value="0">{{translate('inactive')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn--primary">{{translate('submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 text-capitalize">{{translate('operator_list')}}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                                <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{translate('sl')}}</th>
                                    <th>{{translate('name')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th class="text-center">{{translate('fees_rules')}}</th>
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($operators as $key => $operator)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$operator->name}}</td>
                                        <td>
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher_input"
                                                       {{$operator->is_active == 1 ? 'checked' : ''}} disabled>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            @foreach($operator->fees as $fee)
                                                <span class="badge badge-soft-info mb-1">
                                                    {{$fee->min_amount}} - {{$fee->max_amount ?? '∞'}} : 
                                                    {{$fee->fee_amount}} {{$fee->fee_type == 'percent' ? '%' : 'FCFA'}}
                                                </span><br>
                                            @endforeach
                                            <form action="{{route('admin.transfer-operators.add-fee', $operator->id)}}" method="post" class="mt-2 d-flex gap-2">
                                                @csrf
                                                <input type="number" name="min_amount" class="form-control form-control-sm" placeholder="Min" required>
                                                <input type="number" name="max_amount" class="form-control form-control-sm" placeholder="Max">
                                                <input type="number" name="fee_amount" class="form-control form-control-sm" placeholder="Fee" required>
                                                <select name="fee_type" class="form-control form-control-sm">
                                                    <option value="fixed">Fixed</option>
                                                    <option value="percent">%</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn--primary">+</button>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-outline-info btn-sm square-btn"
                                               href="#">
                                                <i class="tio-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
