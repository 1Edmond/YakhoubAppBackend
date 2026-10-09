<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait ActivationClass
{
    public function is_local(): bool
    {
        return in_array(request()->ip(), ['127.0.0.1', '::1']);
    }

    public function getDomain(): string
    {
        return str_replace(["http://", "https://", "www."], "", url('/'));
    }

    public function getSystemAddonCacheKey(string|null $app = 'default'): string
    {
        return str_replace('-', '_', Str::slug('cache_system_addons_for_' . $app . '_' . $this->getDomain()));
    }

    public function getAddonsConfig(): array
    {
        if (file_exists(base_path('config/system-addons.php'))) {
            return include(base_path('config/system-addons.php'));
        }

        $apps = ['admin_panel', 'vendor_panel', 'user_app', 'vendor_app', 'deliveryman_app', 'theme_lifestyle'];
        $appConfig = [];
        foreach ($apps as $app) {
            $appConfig[$app] = [
                "active" => "0",
                "name" => "",
                "identifier" => "",
                "username" => "",
                "purchase_key" => "",
                "software_id" => "",
                "domain" => "",
                "software_type" => $app == 'admin_panel' ? "product" : 'addon',
            ];
        }
        return $appConfig;
    }

    public function getCacheTimeoutByDays(int $days = 3): int
    {
        return 60 * 60 * 24 * $days;
    }

    public function getRequestConfig(string|null $username = null, string|null $purchaseKey = null, string|null $softwareId = null, string|null $softwareType = null, string|null $name = null, string|null $identifier = null): array
    {
        return [
            "active" => "1",
            "name" => $name,
            "identifier" => $identifier,
            "username" => trim($username ?? ''),
            "purchase_key" => $purchaseKey,
            "software_id" => $softwareId ?? 'multishop_tchad_core',
            "domain" => $this->getDomain(),
            "software_type" => $softwareType,
            "errors" => [],
        ];
    }

    public function checkActivationCache(string|null $app)
    {
        return true;
    }

    public function updateActivationConfig($app, $response): void
    {
        $config = $this->getAddonsConfig();
        $config[$app] = $response;
        $configContents = "<?php return " . var_export($config, true) . ";";
        file_put_contents(base_path('config/system-addons.php'), $configContents);
    }
}
