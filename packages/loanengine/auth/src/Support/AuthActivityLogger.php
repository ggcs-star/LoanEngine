<?php

namespace LoanEngine\Auth\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthActivityLogger
{
    private const TABLE = 'loanengine_auth_activities';

    public static function record(
        string $event,
        ?int $userId = null,
        ?string $email = null,
        array $metadata = [],
        ?Request $request = null
    ): void {
        try {
            if (!config('loanengine-auth.activity_logging', true)) {
                return;
            }

            $request ??= request();

            $ip = $request->ip();
            $userAgent = (string) $request->userAgent();

            // Read status code supplied by the controller.
            $statusCode = isset($metadata['status_code'])
                ? (int) $metadata['status_code']
                : null;

            $geo = self::geo($ip);
            $deviceInfo = self::clientDeviceInfo($request);

            $deviceId = self::deviceId($request);

            $browser = self::browser($userAgent);
            $operatingSystem = self::operatingSystem($userAgent);
            $deviceType = self::deviceType($userAgent);

            $previousLogin = null;

            if ($userId) {
                $previousLogin = DB::table(self::TABLE)
                    ->where('user_id', $userId)
                    ->where('event', 'login_success')
                    ->latest('created_at')
                    ->value('ip_address');
            }

            $failedAttempts = DB::table(self::TABLE)
                ->where('event', 'login_failed')
                ->where('created_at', '>=', now()->subMinutes(15))
                ->where(function ($query) use ($ip) {
                    if ($ip) {
                        $query->where('ip_address', $ip);
                    } else {
                        $query->whereNull('ip_address');
                    }
                })
                ->count();

            $risk = self::calculateRisk(
                $event,
                $ip,
                $previousLogin,
                (int) $failedAttempts,
                $deviceId
            );

            $data = [
                'user_id' => $userId,
                'event' => $event,

                'ip_address' => $ip,
                'ip_network' => self::ipNetwork($ip),
                'network_type' => self::networkType($ip),

                'country' => $geo['country'] ?? null,
                'country_code' => $geo['country_code'] ?? null,
                'region' => $geo['region'] ?? null,
                'district' => $geo['district'] ?? null,
                'city' => $geo['city'] ?? null,
                'postal_code' => $geo['postal_code'] ?? null,
                'latitude' => $geo['latitude'] ?? null,
                'longitude' => $geo['longitude'] ?? null,
                'timezone' => $geo['timezone'] ?? null,
                'isp' => $geo['isp'] ?? null,
                'asn' => $geo['asn'] ?? null,

                'device_id' => $deviceId,
                'device_type' => $deviceInfo['device_type'] ?? $deviceType,
                'operating_system' => $deviceInfo['operating_system'] ?? $operatingSystem,
                'browser' => $deviceInfo['browser'] ?? $browser,

                'user_agent' => $userAgent,
                'http_method' => $request->method(),
                'route_name' => $request->route()?->getName(),
                'path' => $request->path(),
                'status_code' => $statusCode,
                'session_id' => self::sessionId($request),

                'email_fingerprint' => $email
                    ? hash_hmac(
                        'sha256',
                        strtolower(trim($email)),
                        (string) config('app.key')
                    )
                    : null,

                'risk_score' => $risk['score'],
                'risk_level' => $risk['level'],

                'metadata' => json_encode(
                    self::safeMetadata($metadata),
                    JSON_UNESCAPED_SLASHES
                ),

                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table(self::TABLE)->insert($data);

        } catch (Throwable $exception) {
            Log::warning('LoanEngine activity logging failed.', [
                'event' => $event,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private static function geo(?string $ip): array
    {
        if (
            !config('loanengine-auth.geoip_enabled', true) ||
            !$ip ||
            !filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            )
        ) {
            return [];
        }

        $cacheKey = 'loanengine_geoip_' . sha1($ip);

        return Cache::remember(
            $cacheKey,
            now()->addDays(7),
            function () use ($ip) {
                try {
                    $provider = config(
                        'loanengine-auth.geoip_provider',
                        'ipwhois'
                    );

                    if ($provider === 'ipwhois') {
                        return self::ipWhoIs($ip);
                    }

                    if ($provider === 'torann') {
                        return self::torannGeo($ip);
                    }

                    return [];

                } catch (Throwable $exception) {
                    Log::warning('LoanEngine GeoIP lookup failed.', [
                        'ip' => $ip,
                        'message' => $exception->getMessage(),
                    ]);

                    return [];
                }
            }
        );
    }

    private static function ipWhoIs(string $ip): array
    {
        $endpoint = rtrim(
            (string) config(
                'loanengine-auth.geoip_endpoint',
                'https://ipwho.is'
            ),
            '/'
        );

        $response = Http::timeout(5)
            ->acceptJson()
            ->get($endpoint . '/' . urlencode($ip));

        if (!$response->successful()) {
            return [];
        }

        $result = $response->json();

        if (!is_array($result) || ($result['success'] ?? false) !== true) {
            return [];
        }

        $connection = $result['connection'] ?? [];

        return [
            'country' => $result['country'] ?? null,
            'country_code' => $result['country_code'] ?? null,
            'region' => $result['region'] ?? null,
            'district' => $result['district'] ?? null,
            'city' => $result['city'] ?? null,
            'postal_code' => $result['postal'] ?? null,
            'latitude' => $result['latitude'] ?? null,
            'longitude' => $result['longitude'] ?? null,
            'timezone' => $result['timezone']['id'] ?? null,
            'isp' => $connection['isp'] ?? null,
            'asn' => isset($connection['asn'])
                ? (string) $connection['asn']
                : null,
        ];
    }

    private static function torannGeo(string $ip): array
    {
        if (!class_exists(\Torann\GeoIP\Facades\GeoIP::class)) {
            return [];
        }

        $location = \Torann\GeoIP\Facades\GeoIP::getLocation($ip);

        return [
            'country' => $location->country ?? null,
            'country_code' => $location->iso_code ?? null,
            'region' => $location->state_name ?? null,
            'district' => $location->district ?? null,
            'city' => $location->city ?? null,
            'postal_code' => $location->postal_code ?? null,
            'latitude' => $location->lat ?? null,
            'longitude' => $location->lon ?? null,
            'timezone' => $location->timezone ?? null,
            'isp' => $location->isp ?? null,
            'asn' => $location->asn ?? null,
        ];
    }

    private static function deviceId(Request $request): ?string
    {
        $deviceId = $request->attributes->get('_loanengine_device_id');

        if (!$deviceId) {
            $deviceId = $request->header('X-Device-ID');
        }

        if (!$deviceId) {
            $deviceId = $request->input('device_id');
        }

        if (!$deviceId) {
            $deviceId = $request->cookie('loanengine_device_id');
        }

        if (!$deviceId && $request->hasSession()) {
            $deviceId = $request->session()->get('_loanengine_device_id');
        }

        if (!is_string($deviceId) || strlen($deviceId) > 120) {
            return null;
        }

        return trim($deviceId) ?: null;
    }

    private static function clientDeviceInfo(Request $request): array
    {
        $info = $request->input('client_device_info', []);

        if (!is_array($info)) {
            return [];
        }

        $allowed = [
            'device_type',
            'operating_system',
            'browser',
        ];

        return array_intersect_key($info, array_flip($allowed));
    }

    private static function browser(string $ua): ?string
    {
        $patterns = [
            '/Edg\/([0-9.]+)/i' => 'Edge',
            '/OPR\/([0-9.]+)/i' => 'Opera',
            '/SamsungBrowser\/([0-9.]+)/i' => 'Samsung Internet',
            '/Firefox\/([0-9.]+)/i' => 'Firefox',
            '/CriOS\/([0-9.]+)/i' => 'Chrome',
            '/FxiOS\/([0-9.]+)/i' => 'Firefox',
            '/Chrome\/([0-9.]+)/i' => 'Chrome',
            '/Version\/([0-9.]+).*Safari/i' => 'Safari',
        ];

        foreach ($patterns as $pattern => $name) {
            if (preg_match($pattern, $ua, $matches)) {
                return $name . ' ' . ($matches[1] ?? '');
            }
        }

        return null;
    }

    private static function operatingSystem(string $ua): ?string
    {
        if (preg_match('/Windows NT 10\.0/i', $ua)) {
            return 'Windows 10/11';
        }

        if (preg_match('/Windows NT 6\.3/i', $ua)) {
            return 'Windows 8.1';
        }

        if (preg_match('/Windows NT 6\.2/i', $ua)) {
            return 'Windows 8';
        }

        if (preg_match('/Windows NT 6\.1/i', $ua)) {
            return 'Windows 7';
        }

        if (preg_match('/Android ([0-9.]+)/i', $ua, $matches)) {
            return 'Android ' . $matches[1];
        }

        if (preg_match('/iPhone OS ([0-9_]+)/i', $ua, $matches)) {
            return 'iOS ' . str_replace('_', '.', $matches[1]);
        }

        if (preg_match('/iPad.*OS ([0-9_]+)/i', $ua, $matches)) {
            return 'iPadOS ' . str_replace('_', '.', $matches[1]);
        }

        if (preg_match('/Mac OS X ([0-9_]+)/i', $ua, $matches)) {
            return 'macOS ' . str_replace('_', '.', $matches[1]);
        }

        if (stripos($ua, 'Linux') !== false) {
            return 'Linux';
        }

        return null;
    }

    private static function deviceType(string $ua): string
    {
        if (preg_match('/iPad|Tablet/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/Mobile|Android|iPhone|iPod/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private static function networkType(?string $ip): string
    {
        if (!$ip) {
            return 'unknown';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 'ipv4';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 'ipv6';
        }

        return 'unknown';
    }

    private static function ipNetwork(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);

            return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0/24';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return substr($ip, 0, 19) . '::/64';
        }

        return null;
    }

    private static function sessionId(Request $request): ?string
    {
        if (!$request->hasSession()) {
            return null;
        }

        return $request->session()->getId();
    }

    private static function calculateRisk(
        string $event,
        ?string $ip,
        ?string $previousLogin,
        int $failedAttempts,
        ?string $deviceId
    ): array {
        $score = 0;

        if ($event === 'login_failed') {
            $score += 25;
        }

        if ($failedAttempts >= 3) {
            $score += 20;
        }

        if ($failedAttempts >= 5) {
            $score += 20;
        }

        if (
            $event === 'login_success' &&
            $previousLogin &&
            $ip &&
            $previousLogin !== $ip
        ) {
            $score += 25;
        }

        if (!$deviceId && $event === 'login_success') {
            $score += 10;
        }

        $score = min(100, $score);

        $level = match (true) {
            $score >= 70 => 'high',
            $score >= 35 => 'medium',
            default => 'low',
        };

        return [
            'score' => $score,
            'level' => $level,
        ];
    }

    private static function safeMetadata(array $metadata): array
    {
        $blockedKeys = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'token',
            'access_token',
            'refresh_token',
            'authorization',
            'otp',
            'secret',
            'api_key',
            'cookie',
        ];

        $safe = [];

        foreach ($metadata as $key => $value) {
            if (in_array(strtolower((string) $key), $blockedKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                $value = self::safeMetadata($value);
            }

            if (is_object($value) || is_resource($value)) {
                continue;
            }

            $safe[$key] = $value;
        }

        return $safe;
    }
}
