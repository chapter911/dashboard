<?php

namespace App\Controllers;

use App\Models\LoginAuditModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Auth extends BaseController
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const MAX_NETWORK_ATTEMPTS = 25;
    private const LOCKOUT_SECONDS = 900;

    public function index(): ResponseInterface|string
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', [
            'title' => 'Login Dashboard PLN',
        ]);
    }

    public function login()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'password' => 'required|min_length[8]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $context = $this->getRequestContext();

        $throttleUserKey = $this->makeUserThrottleKey($username, $context['ip_network'], $context['user_agent']);
        $throttleNetworkKey = $this->makeNetworkThrottleKey($context['ip_network']);

        if ($this->isLockedOut($throttleUserKey, self::MAX_LOGIN_ATTEMPTS)) {
            $this->notifyLockout($username, $context, 'ACCOUNT_FINGERPRINT_LOCKOUT');

            return redirect()->back()->withInput()->with(
                'error',
                'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.'
            );
        }

        if ($this->isLockedOut($throttleNetworkKey, self::MAX_NETWORK_ATTEMPTS)) {
            $this->notifyLockout($username, $context, 'NETWORK_LOCKOUT');

            return redirect()->back()->withInput()->with(
                'error',
                'Akses dari jaringan ini dibatasi sementara. Coba lagi dalam 15 menit.'
            );
        }

        if (! $this->isStrongPassword($password)) {
            $this->logAudit($username, 'LOGIN_FAILED', false, $context, 'WEAK_PASSWORD_POLICY');

            return redirect()->back()->withInput()->with(
                'error',
                'Password harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, serta simbol.'
            );
        }

        $userModel = new UserModel();

        try {
            $user = $userModel->findByUsername($username);
        } catch (Throwable $e) {
            log_message('critical', 'AUTH_DB_UNAVAILABLE: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()->withInput()->with(
                'error',
                'Layanan autentikasi sedang tidak tersedia. Silakan coba beberapa saat lagi.'
            );
        }

        if ($user === null) {
            $this->recordFailedAttempt($throttleUserKey, self::MAX_LOGIN_ATTEMPTS, $username, $context, 'UNKNOWN_USER');
            $this->recordFailedAttempt($throttleNetworkKey, self::MAX_NETWORK_ATTEMPTS, $username, $context, 'NETWORK_FAIL');
            $this->logAudit($username, 'LOGIN_FAILED', false, $context, 'UNKNOWN_USER');

            return redirect()->back()->withInput()->with('error', 'Username atau password tidak valid.');
        }

        if ((int) ($user['is_active'] ?? 0) !== 1 || (int) ($user['web_access'] ?? 0) !== 1) {
            $this->recordFailedAttempt($throttleUserKey, self::MAX_LOGIN_ATTEMPTS, $username, $context, 'INACTIVE_OR_NO_WEB_ACCESS');
            $this->recordFailedAttempt($throttleNetworkKey, self::MAX_NETWORK_ATTEMPTS, $username, $context, 'NETWORK_FAIL');
            $this->logAudit($username, 'LOGIN_FAILED', false, $context, 'INACTIVE_OR_NO_WEB_ACCESS');

            return redirect()->back()->withInput()->with('error', 'Akun tidak aktif atau tidak memiliki akses web.');
        }

        $storedPassword = (string) ($user['password'] ?? '');
        $passwordIsValid = $this->verifyPasswordHashOnly($password, $storedPassword);

        if (! $passwordIsValid) {
            $this->recordFailedAttempt($throttleUserKey, self::MAX_LOGIN_ATTEMPTS, $username, $context, 'INVALID_CREDENTIAL');
            $this->recordFailedAttempt($throttleNetworkKey, self::MAX_NETWORK_ATTEMPTS, $username, $context, 'NETWORK_FAIL');
            $this->logAudit($username, 'LOGIN_FAILED', false, $context, 'INVALID_CREDENTIAL');

            return redirect()->back()->withInput()->with('error', 'Username atau password tidak valid.');
        }

        $this->clearFailedAttempts($throttleUserKey);
        $this->clearFailedAttempts($throttleNetworkKey);

        session()->regenerate(true);
        session()->set([
            'isLoggedIn' => true,
            'username' => $user['username'],
            'nama' => $user['nama'] ?? '',
            'group_id' => $user['group_id'] ?? null,
            'unit_id' => $user['unit_id'] ?? null,
        ]);

        $this->logAudit((string) $user['username'], 'LOGIN_SUCCESS', true, $context, 'OK');

        return redirect()->to('/dashboard')->with('success', 'Login berhasil.');
    }

    public function logout()
    {
        $username = (string) session('username');
        $context = $this->getRequestContext();

        if ($username !== '') {
            $this->logAudit($username, 'LOGOUT', false, $context, 'USER_LOGOUT');
        }

        session()->destroy();

        return redirect()->to('/')->with('success', 'Logout berhasil.');
    }

    private function makeUserThrottleKey(string $username, string $network, string $userAgent): string
    {
        return 'login_user_throttle_' . sha1(strtolower($username) . '|' . strtolower($network) . '|' . strtolower($userAgent));
    }

    private function makeNetworkThrottleKey(string $network): string
    {
        return 'login_network_throttle_' . sha1(strtolower($network));
    }

    private function isLockedOut(string $key, int $maxAttempts): bool
    {
        $attemptData = cache()->get($key);

        if (! is_array($attemptData)) {
            return false;
        }

        $attempts = (int) ($attemptData['attempts'] ?? 0);
        $lastAttempt = isset($attemptData['last']) ? (int) $attemptData['last'] : 0;
        $isWithinLockWindow = $lastAttempt > 0 && (time() - $lastAttempt) < self::LOCKOUT_SECONDS;

        return $attempts >= $maxAttempts && $isWithinLockWindow;
    }

    private function recordFailedAttempt(string $key, int $maxAttempts, string $username, array $context, string $reason): void
    {
        $cache = cache();
        $attemptData = $cache->get($key);

        if (! is_array($attemptData)) {
            $attemptData = ['attempts' => 0, 'last' => 0];
        }

        $attemptData['attempts'] = (int) ($attemptData['attempts'] ?? 0) + 1;
        $attemptData['last'] = time();

        $cache->save($key, $attemptData, self::LOCKOUT_SECONDS);

        if ((int) $attemptData['attempts'] === $maxAttempts) {
            $this->notifyLockout($username, $context, $reason);
        }
    }

    private function clearFailedAttempts(string $key): void
    {
        cache()->delete($key);
    }

    private function verifyPasswordHashOnly(string $inputPassword, string $storedPassword): bool
    {
        if ($storedPassword === '') {
            return false;
        }

        $hashInfo = password_get_info($storedPassword);

        if (($hashInfo['algo'] ?? 0) === 0) {
            return false;
        }

        return password_verify($inputPassword, $storedPassword);
    }

    private function isStrongPassword(string $password): bool
    {
        $isLongEnough = strlen($password) >= 8;
        $hasUpper = preg_match('/[A-Z]/', $password) === 1;
        $hasLower = preg_match('/[a-z]/', $password) === 1;
        $hasDigit = preg_match('/\d/', $password) === 1;
        $hasSymbol = preg_match('/[^a-zA-Z\d]/', $password) === 1;

        return $isLongEnough && $hasUpper && $hasLower && $hasDigit && $hasSymbol;
    }

    /**
     * @return array{ip_address:string, ip_network:string, user_agent:string}
     */
    private function getRequestContext(): array
    {
        $ip = (string) $this->request->getIPAddress();
        $agent = $this->request->getUserAgent();
        $agentString = trim($agent->getAgentString());
        $userAgent = $agentString !== '' ? $agentString : 'unknown';

        return [
            'ip_address' => $ip,
            'ip_network' => $this->resolveIpNetwork($ip),
            'user_agent' => mb_substr($userAgent, 0, 255),
        ];
    }

    private function resolveIpNetwork(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);

            if (count($parts) === 4) {
                return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0/24';
            }
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $packed = @inet_pton($ip);

            if ($packed !== false) {
                $prefix = substr($packed, 0, 8);
                $hex = bin2hex($prefix);

                return implode(':', str_split($hex, 4)) . '::/64';
            }
        }

        return 'unknown';
    }

    private function notifyLockout(string $username, array $context, string $reason): void
    {
        $notificationKey = 'lockout_notified_' . sha1(strtolower($username) . '|' . strtolower($context['ip_network']) . '|' . $reason);

        if (cache()->get($notificationKey)) {
            return;
        }

        cache()->save($notificationKey, 1, 120);

        $message = sprintf(
            'SECURITY_LOCKOUT username=%s ip=%s network=%s ua=%s reason=%s',
            $username,
            $context['ip_address'],
            $context['ip_network'],
            $context['user_agent'],
            $reason
        );

        log_message('critical', $message);
        $this->logAudit($username, 'LOCKOUT', false, $context, $reason);
    }

    private function logAudit(string $username, string $eventType, bool $isLoggedIn, array $context, string $notes): void
    {
        $payload = [
            'username' => mb_substr($username, 0, 100),
            'event_type' => mb_substr($eventType, 0, 30),
            'is_logged_in' => $isLoggedIn ? 1 : 0,
            'ip_address' => mb_substr($context['ip_address'], 0, 45),
            'ip_network' => mb_substr($context['ip_network'], 0, 80),
            'user_agent' => mb_substr($context['user_agent'], 0, 255),
            'notes' => mb_substr($notes, 0, 255),
        ];

        try {
            $model = new LoginAuditModel();
            $model->insert($payload, false);
        } catch (Throwable $e) {
            // Fallback in case migration for additional audit columns has not run yet.
            try {
                $legacyModel = new LoginAuditModel();
                $legacyModel->insert([
                    'username' => mb_substr($username, 0, 100),
                    'is_logged_in' => $isLoggedIn ? 1 : 0,
                ], false);
            } catch (Throwable $ignored) {
                log_message('error', 'Failed to write login audit log: {message}', ['message' => $e->getMessage()]);
            }
        }
    }
}
