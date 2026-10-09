<?php

namespace App\Http\Controllers\Admin\Settings;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SoftwareUpdateRequest;
use App\Traits\ActivationClass;
use App\Traits\SettingsTrait;
use App\Traits\UpdateClass;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class SoftwareUpdateController extends Controller
{
    use ActivationClass;
    use UpdateClass;
    use SettingsTrait;

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, ?string $type = null): View
    {
        abort(404);
    }

    public function update(SoftwareUpdateRequest $request): RedirectResponse|JsonResponse
    {
        abort(404);
    }
}
