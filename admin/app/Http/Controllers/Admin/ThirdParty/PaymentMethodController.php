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
        return view('admin-views.third-party.payment-method.index');
    }

    public function UpdatePaymentConfig(PaymentMethodUpdateRequest $request): RedirectResponse
    {
        ToastMagic::info(translate('Digital_payment_methods_are_disabled'));
        return redirect()->route('admin.third-party.payment-method.index');
    }

    public function UpdateStatus(Request $request): RedirectResponse
    {
        ToastMagic::info(translate('Digital_payment_methods_are_disabled'));
        return redirect()->route('admin.third-party.payment-method.index');
    }
}
