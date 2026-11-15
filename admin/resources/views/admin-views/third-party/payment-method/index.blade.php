@extends('layouts.admin.app')

@section('title', translate('Digital_payment'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/3rd-party.png') }}" alt="">
                {{ translate('Payment & Withdraw Methods Setup') }}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- In-page Navigation Tabs -->
        @include('admin-views.third-party._third-party-payment-method-menu')

        <!-- Notice Alert -->
        <div class="alert alert-soft-primary d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fi fi-sr-info text-primary fs-18"></i>
            <div>
                <strong>{{ translate('Notice') }} :</strong> {{ translate('Conformément au cahier des charges, seuls Airtel Money et Moov Money sont autorisés comme passerelles de paiement mobile au Tchad. Vous pouvez ici configurer leurs identifiants API marchands respectifs.') }}
            </div>
        </div>

        <div class="row g-4">
            <!-- Airtel Money Setup Card -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft-danger font-weight-bold fs-14 px-3 py-2">
                                <i class="fi fi-sr-phone-call mr-1"></i> Airtel Money Tchad
                            </span>
                        </div>
                        <span class="badge {{ ($airtel['status'] ?? 0) ? 'badge-success' : 'badge-danger' }} px-3 py-1 text-uppercase">
                            {{ ($airtel['status'] ?? 0) ? translate('Active') : translate('Inactive') }}
                        </span>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.third-party.payment-method.addon-payment-set') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="gateway" value="airtel_money">

                            <div class="form-group mb-3">
                                <label class="title-color d-flex justify-content-between">
                                    <span>{{ translate('Activation') }}</span>
                                    <span class="text-muted fs-12">{{ translate('Activer pour accepter les paiements via API Airtel') }}</span>
                                </label>
                                <select name="status" class="form-control">
                                    <option value="1" {{ ($airtel['status'] ?? 0) == 1 ? 'selected' : '' }}>{{ translate('Active') }}</option>
                                    <option value="0" {{ ($airtel['status'] ?? 0) == 0 ? 'selected' : '' }}>{{ translate('Inactive') }}</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="title-color">{{ translate('Environnement') }}</label>
                                <select name="mode" class="form-control">
                                    <option value="sandbox" {{ ($airtel['mode'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox / Test</option>
                                    <option value="live" {{ ($airtel['mode'] ?? '') == 'live' ? 'selected' : '' }}>Production / Live</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="title-color" for="airtel_client_id">{{ translate('Client ID / API Key') }}</label>
                                <input type="text" class="form-control" id="airtel_client_id" name="client_id"
                                       placeholder="ex: art_live_xxxx" value="{{ $airtel['client_id'] ?? '' }}">
                            </div>

                            <div class="form-group mb-3">
                                <label class="title-color" for="airtel_client_secret">{{ translate('Client Secret') }}</label>
                                <input type="password" class="form-control" id="airtel_client_secret" name="client_secret"
                                       placeholder="ex: ••••••••••••••••" value="{{ $airtel['client_secret'] ?? '' }}">
                            </div>

                            <div class="form-group mb-3">
                                <label class="title-color" for="airtel_merchant_id">{{ translate('Merchant ID / Code Marchand') }}</label>
                                <input type="text" class="form-control" id="airtel_merchant_id" name="merchant_id"
                                       placeholder="ex: AIRTEL_TCHAD_001" value="{{ $airtel['merchant_id'] ?? '' }}">
                            </div>

                            <div class="form-group mb-4">
                                <label class="title-color" for="airtel_webhook_secret">{{ translate('Webhook Secret (Validation)') }}</label>
                                <input type="text" class="form-control" id="airtel_webhook_secret" name="webhook_secret"
                                       placeholder="ex: whsec_xxxx" value="{{ $airtel['webhook_secret'] ?? '' }}">
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn--primary px-4">
                                    <i class="fi fi-sr-disk mr-1"></i> {{ translate('Enregistrer Airtel Money') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Moov Money Setup Card -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft-info font-weight-bold fs-14 px-3 py-2">
                                <i class="fi fi-sr-phone-call mr-1"></i> Moov Money Tchad
                            </span>
                        </div>
                        <span class="badge {{ ($moov['status'] ?? 0) ? 'badge-success' : 'badge-danger' }} px-3 py-1 text-uppercase">
                            {{ ($moov['status'] ?? 0) ? translate('Active') : translate('Inactive') }}
                        </span>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.third-party.payment-method.addon-payment-set') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="gateway" value="moov_money">

                            <div class="form-group mb-3">
                                <label class="title-color d-flex justify-content-between">
                                    <span>{{ translate('Activation') }}</span>
                                    <span class="text-muted fs-12">{{ translate('Activer pour accepter les paiements via API Moov') }}</span>
                                </label>
                                <select name="status" class="form-control">
                                    <option value="1" {{ ($moov['status'] ?? 0) == 1 ? 'selected' : '' }}>{{ translate('Active') }}</option>
                                    <option value="0" {{ ($moov['status'] ?? 0) == 0 ? 'selected' : '' }}>{{ translate('Inactive') }}</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="title-color">{{ translate('Environnement') }}</label>
                                <select name="mode" class="form-control">
                                    <option value="sandbox" {{ ($moov['mode'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox / Test</option>
                                    <option value="live" {{ ($moov['mode'] ?? '') == 'live' ? 'selected' : '' }}>Production / Live</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="title-color" for="moov_client_id">{{ translate('Client ID / API Key') }}</label>
                                <input type="text" class="form-control" id="moov_client_id" name="client_id"
                                       placeholder="ex: moov_live_xxxx" value="{{ $moov['client_id'] ?? '' }}">
                            </div>

                            <div class="form-group mb-3">
                                <label class="title-color" for="moov_client_secret">{{ translate('Client Secret') }}</label>
                                <input type="password" class="form-control" id="moov_client_secret" name="client_secret"
                                       placeholder="ex: ••••••••••••••••" value="{{ $moov['client_secret'] ?? '' }}">
                            </div>

                            <div class="form-group mb-3">
                                <label class="title-color" for="moov_merchant_id">{{ translate('Merchant ID / Code Marchand') }}</label>
                                <input type="text" class="form-control" id="moov_merchant_id" name="merchant_id"
                                       placeholder="ex: MOOV_TCHAD_001" value="{{ $moov['merchant_id'] ?? '' }}">
                            </div>

                            <div class="form-group mb-4">
                                <label class="title-color" for="moov_webhook_secret">{{ translate('Webhook Secret (Validation)') }}</label>
                                <input type="text" class="form-control" id="moov_webhook_secret" name="webhook_secret"
                                       placeholder="ex: whsec_xxxx" value="{{ $moov['webhook_secret'] ?? '' }}">
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn--primary px-4">
                                    <i class="fi fi-sr-disk mr-1"></i> {{ translate('Enregistrer Moov Money') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
