# LoanEngine Auth package — activity, risk and API responses

## Included
- Package-loaded API routes at `/api/v1/auth/*`.
- Shared JSON response contract in `src/Support/ApiResponse.php`.
- API exception rendering for validation, authentication, authorization, HTTP and unexpected errors.
- Auth activity log table and logger for API activity and web login/register/logout requests.
- Web and API login success/failure, registration and logout events (explicitly logged by controllers to avoid middleware double-counting).
- IP address, IP network prefix, user agent, route, method, status, optional GeoIP country/country code, region, district, city, postal code, approximate coordinates, timezone, ISP and ASN.
- Device ID (when supplied by client), device category, operating system, browser and session ID.
- Per-user activity summary endpoint: `GET /api/v1/auth/activity/summary`.
- Basic heuristic risk score and low/medium/high risk labels.
- Authenticated user's own activity endpoint and admin-only security events endpoint.
- Rate limits on registration/login and admin security-events listing.

## Install / update
From the host Laravel application's root:
```bash
composer dump-autoload
php artisan package:discover -v
php artisan optimize:clear
php artisan migrate
php artisan route:list --path=api/v1/auth -v
```

The host app's `composer.json` must require `loanengine/auth` and its path repository must point to `packages/loanengine/auth`. The package provider is discovered via `extra.laravel.providers`.

## Endpoints
| Method | Endpoint | Access |
|---|---|---|
| POST | `/api/v1/auth/register` | Public; throttled |
| POST | `/api/v1/auth/login` | Public; throttled |
| GET | `/api/v1/auth/me` | Sanctum token |
| POST | `/api/v1/auth/logout` | Sanctum token |
| GET | `/api/v1/auth/activity` | Sanctum token; own events only |
| GET | `/api/v1/auth/activity/summary` | Sanctum token; own login/logout totals |
| GET | `/api/v1/auth/admin/security-events` | Sanctum token; `role=admin` only |

Protected API requests require `Authorization: Bearer <token>` and `Accept: application/json`.

## IP and geolocation accuracy
`Request::ip()` only returns the correct client address when the deployment's trusted-proxy configuration is correct. Do not blindly trust arbitrary `X-Forwarded-For` headers. Configure Laravel trusted proxies for the actual load balancer/reverse proxy.

Country, region, city, ISP and ASN are optional. If `torann/geoip` is installed and configured, the logger attempts to read local GeoIP data. No external IP-lookup service is called by this package. Without a configured GeoIP provider, location/provider fields remain null; they are not guessed.

`ip_network` is only a coarse network prefix (`/24` IPv4 or `/64` IPv6), not an ISP/network-owner identification. `network_type` means public vs private/reserved IP classification, not Wi-Fi/mobile/VPN detection. ISP/ASN/VPN/proxy identification requires a trusted, configured provider and can be inaccurate.

## Risk score
The score is a basic heuristic (recent failed attempts from an IP, failed login, new IP compared with the previous successful login, invalid IP, server error). It is not a fraud verdict and must not automatically suspend accounts without a separate reviewed policy. It is not device fingerprinting or impossible-travel detection.

## Privacy and operations
IP addresses and user-agent strings are personal data in many jurisdictions. Inform users in your privacy notice, limit access to administrators, define a retention period, and schedule deletion of old audit rows according to your policy. Avoid exposing this data in public endpoints. `email_fingerprint` is an HMAC using the application key; rotate the application key with care because existing fingerprints will no longer match.

Configure:
- `LOANENGINE_AUTH_ACTIVITY_LOGGING=true`
- `LOANENGINE_AUTH_ACTIVITY_RETENTION_DAYS=90`
- `LOANENGINE_AUTH_GEOIP_ENABLED=true`
- `LOANENGINE_AUTH_CAPTURE_DEVICE_ID=true`

The retention setting documents the intended retention period; automatic deletion scheduling is application-specific and must be added to the host scheduler before it takes effect. The package includes the additive migration `2026_01_01_000003...` for extra location/device/session columns; run `php artisan migrate` after updating.

## Important compatibility checks
- The host User model must support Sanctum's `HasApiTokens` trait for API token login/register. Web routes use the package controller and package-prefixed Blade views; the host must provide the `users` table and `App\Models\User` model expected by the web registration and password-reset flow.
- The host users table must contain `name`, `email`, `password`, and `role` columns for the current registration behavior. If your schema differs, adapt the registration fields.
- `role` must be a server-controlled field; public requests cannot set it.
- This ZIP is the package subtree, not the whole Laravel host project. Run tests against the actual host app and database before production deployment.


## Why location fields can be NULL
- `127.0.0.1`, `::1`, RFC1918 LAN addresses and other private/reserved IPs do not have public GeoIP locations. Local XAMPP/Laragon testing will therefore correctly store NULL for country, state, city, postal code, coordinates, ISP and ASN.
- To populate GeoIP fields on a live server, `Request::ip()` must resolve to the visitor's public IP, Laravel trusted proxies must be configured for the actual proxy/load balancer, and the Torann GeoIP provider/database must be installed and configured with a current dataset. The package does not guess missing location data.
- Postal code, district, ISP and ASN are not guaranteed by every GeoIP database. Latitude/longitude are approximate IP geolocation, not GPS.
- Web activity now uses a random first-party HttpOnly `loanengine_device_id` cookie (with session fallback) so a browser can be recognized across visits. API/mobile clients should send a stable random `X-Device-ID` header on login and logout; do not send hardware identifiers.
- Existing activity rows are historical snapshots and will not be backfilled automatically. New records after deployment use the updated logger.


## Dynamic GeoIP and stable browser identity (updated)
The package supports `LOANENGINE_AUTH_GEOIP_PROVIDER=ipwhois` (HTTPS lookup through ipwho.is) or `torann` (local Torann/GeoIP database). With `ipwhois`, the visitor's public IP is sent to the external provider; disclose this in your privacy notice and review the provider's current terms/rate limits. Results are cached for 7 days. Set `LOANENGINE_AUTH_GEOIP_ENABLED=false` to disable external lookup, or choose `torann` when using a local database.

Example `.env`:
```env
LOANENGINE_AUTH_ACTIVITY_LOGGING=true
LOANENGINE_AUTH_GEOIP_ENABLED=true
LOANENGINE_AUTH_GEOIP_PROVIDER=ipwhois
LOANENGINE_AUTH_GEOIP_ENDPOINT=https://ipwho.is
LOANENGINE_AUTH_CAPTURE_DEVICE_ID=true
```

The `IdentifyWebDevice` middleware creates a random first-party HttpOnly cookie before the controller logs login/logout. It is a persistent browser identifier, not a hardware fingerprint. Users can clear cookies or use another browser/profile, which creates a different ID. API/mobile clients must create one random UUID once, persist it in their secure client storage, and send it as `X-Device-ID` on every login/logout request. The server deliberately does not generate a new API device ID for each request.

**Local development:** requests arriving as `127.0.0.1`/`::1` are not geolocatable. To test genuine GeoIP, deploy to a public host or test through a publicly reachable environment so Laravel receives the actual public client IP. Do not replace a loopback IP with the server's outbound IP; that would identify the server/network, not reliably the visitor.

**Reverse proxies:** configure Laravel's trusted proxy middleware for only the proxy/load balancer addresses you control. Do not trust arbitrary `X-Forwarded-For` values from the internet. Otherwise `Request::ip()` can show the proxy address or be spoofed.

Browser, OS and device category are parsed from the real User-Agent header. Some browsers intentionally reduce or omit version/platform details, so fields can legitimately be `Unknown`/NULL. Postal code, district, ISP and ASN depend on the GeoIP provider's coverage; they are never fabricated.
