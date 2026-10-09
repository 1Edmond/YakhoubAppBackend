@extends('layouts.admin.app')

@section('title', translate('Digital_payment'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/3rd-party.png') }}" alt="">
                {{ translate('Payment & Withdraw Methods Setup') }}
            </h2>
        </div>

        @include('admin-views.third-party._third-party-payment-method-menu')

        <div class="card bg-white border-0 shadow-none" style="min-height: 500px;">
            <div class="card-body"></div>
        </div>
    </div>
@endsection
