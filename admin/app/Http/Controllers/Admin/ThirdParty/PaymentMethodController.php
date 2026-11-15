<?php

namespace App\Http\Controllers\Admin\ThirdParty;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Contracts\Repositories\OfflinePaymentMethodRepositoryInterface;
use App\Contracts\Repositories\SettingRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\PaymentMethodUpdateRequest;
use App\Services\SettingService;
use App\Traits\PaymentGatewayTrait;
use App\Traits\Processor;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class PaymentMethodController extends BaseController
{
    use Processor;
    use PaymentGatewayTrait;

    public function __construct(
        private readonly SettingRepositoryInterface              $settingRepo,
        private readonly BusinessSettingRepositoryInterface      $businessSettingRepo,
        private readonly SettingService                          $settingService,
        private readonly CurrencyRepositoryInterface             $currencyRepo,
        private readonly OfflinePaymentMethodRepositoryInterface $offlinePaymentMethodRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View
     */
    public function index(Request|null $request, ?string $type = null): View
    {
        $airtelSetting = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'airtel_money'])?->value;
        $airtel = $airtelSetting ? json_decode($airtelSetting, true) : [
            'status' => 0,
            'mode' => 'sandbox',
            'client_id' => '',
            'client_secret' => '',
            'merchant_id' => '',
            'webhook_secret' => '',
        ];

        $moovSetting = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'moov_money'])?->value;
        $moov = $moovSetting ? json_decode($moovSetting, true) : [
            'status' => 0,
            'mode' => 'sandbox',
            'client_id' => '',
            'client_secret' => '',
            'merchant_id' => '',
            'webhook_secret' => '',
        ];

        return view('admin-views.third-party.payment-method.index', compact('airtel', 'moov'));
    }

    public function UpdatePaymentConfig(Request $request): RedirectResponse
    {
        $gateway = $request->input('gateway');

        if (!in_array($gateway, ['airtel_money', 'moov_money'])) {
            ToastMagic::error(translate('invalid_payment_gateway'));
            return back();
        }

        $config = [
            'status' => (int)$request->input('status', 0),
            'mode' => $request->input('mode', 'sandbox'),
            'client_id' => $request->input('client_id', ''),
            'client_secret' => $request->input('client_secret', ''),
            'merchant_id' => $request->input('merchant_id', ''),
            'webhook_secret' => $request->input('webhook_secret', ''),
        ];

        $this->businessSettingRepo->updateOrInsert(type: $gateway, value: json_encode($config));

        clearWebConfigCacheKeys();
        ToastMagic::success(translate('payment_settings_updated_successfully'));
        return redirect()->route('admin.third-party.payment-method.index');
    }

    public function UpdateStatus(Request $request): RedirectResponse
    {
        $gateway = $request->input('gateway');

        if (!in_array($gateway, ['airtel_money', 'moov_money'])) {
            ToastMagic::error(translate('invalid_payment_gateway'));
            return back();
        }

        $existingSetting = $this->businessSettingRepo->getFirstWhere(params: ['type' => $gateway])?->value;
        $config = $existingSetting ? json_decode($existingSetting, true) : [
            'mode' => 'sandbox',
            'client_id' => '',
            'client_secret' => '',
            'merchant_id' => '',
            'webhook_secret' => '',
        ];

        $config['status'] = (int)$request->input('status', 0);

        $this->businessSettingRepo->updateOrInsert(type: $gateway, value: json_encode($config));

        clearWebConfigCacheKeys();
        ToastMagic::success(translate('payment_status_updated_successfully'));
        return redirect()->route('admin.third-party.payment-method.index');
    }
}
