<?php

namespace App\Filters;

use App\Models\AppSettingModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }

        $session = session();
        $timeoutSeconds = $this->resolveTimeoutSeconds();
        $now = time();
        $lastActivityAt = (int) ($session->get('last_activity_at') ?? 0);

        if ($lastActivityAt > 0 && ($now - $lastActivityAt) >= $timeoutSeconds) {
            $session->destroy();

            return redirect()->to('/')->with('error', 'Sesi berakhir karena tidak ada aktivitas. Silakan login kembali.');
        }

        $session->set('last_activity_at', $now);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function resolveTimeoutSeconds(): int
    {
        $minutes = 30;

        try {
            $settingModel = new AppSettingModel();
            $storedValue = $settingModel->getValue('auto_logout_minutes', '30');

            if (is_numeric($storedValue)) {
                $minutes = (int) $storedValue;
            }
        } catch (Throwable $e) {
            log_message('warning', 'AUTH_FILTER_TIMEOUT_SETTING_UNAVAILABLE: {message}', ['message' => $e->getMessage()]);
        }

        if ($minutes < 1) {
            $minutes = 1;
        }

        if ($minutes > 1440) {
            $minutes = 1440;
        }

        return $minutes * 60;
    }
}
