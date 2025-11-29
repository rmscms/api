# RMS API Package

یک لایهٔ API مشترک برای تمام ماژول‌های RMS (مثل Shop و Blog) که پاسخ‌دهی یکنواخت، احراز هویت توسعه‌پذیر، پشتیبانی از modifier pipeline و ثبت خودکار ماژول‌ها را فراهم می‌کند.

## نصب

```bash
composer require rmscms/api "@dev"
```

اگر در پروژهٔ مونورپو کار می‌کنید (همین مخزن):

```jsonc
// composer.json (root)
"require": {
    "rmscms/api": "dev-main"
},
"autoload": {
    "psr-4": {
        "RMS\\Api\\": "packages/rms/api/src/"
    }
},
"repositories": [
    { "type": "path", "url": "packages/rms/api", "options": {"symlink": true} }
]
```

پس از نصب:

```bash
php artisan vendor:publish --tag=rms-api-config
```

## ساختار فایل‌ها

```
packages/rms/api
├── config/api.php                 # تنظیمات prefix، middleware، auth، rate limit و modifierها
├── src/ApiServiceProvider.php     # رجیستر سینگلتون‌ها و ماکروها
├── src/Support/Response/*         # Responder, Payload, Pipeline و Trait پاسخ‌دهی
├── src/Http/Middleware/*          # EnsureApiEnabled, ResolveGuard, ApplyRateLimit
├── src/Support/Routing/*          # ماکروهای Route::rmsApi و ModuleRegistrar
├── src/Http/Controllers/Auth/*    # کنترلر auth پیش‌فرض
└── tests/Feature                  # نمونه تست‌های auth و response
```

## تنظیمات کلیدی (`config/rms-api.php`)

| کلید | توضیح |
|------|-------|
| `enabled` | فعال/غیرفعال کردن کل API (برای maintenance یا تست) |
| `routing.prefix` | پیش‌فرض `api/v1` – در تمام ماژول‌ها اعمال می‌شود |
| `routing.middleware` | معمولاً `['api']`، می‌توانید middleware سراسری اضافه کنید |
| `auth.guard` | نگهبان پیش‌فرض (پیشنهاد: `sanctum`) |
| `auth.drivers` | لیست driverها؛ `email` به عنوان نمونه پیاده‌سازی شده |
| `response.modifiers` | کلاس‌هایی که باید `ResponseModifier` را پیاده کنند و قبل از ارسال پاسخ اجرا شوند |
| `rate_limit` | تنظیمات rate limiting قابل فراخوانی توسط middleware `ApplyRateLimit` |

## نحوهٔ تعریف Route

```php
use Illuminate\Support\Facades\Route;
use RMS\Api\Http\Controllers\BaseApiController;

Route::rmsApi([], function () {
    Route::get('health', [HealthController::class, 'index']);
});
```

### ماژول مستقل

```php
use RMS\Api\Facades\Api;

Api::module('blog', function () {
    Route::get('posts', [PostController::class, 'index']);
    Route::post('posts', [PostController::class, 'store'])
        ->middleware(['auth:sanctum']);
});
```

ماکرو `Route::rmsApiModule('blog', fn() => ...)` همین کار را انجام می‌دهد ولی `Api::module` ساده‌تر است و در ServiceProvider خود ماژول می‌توانید آن را ثبت کنید.

## استفاده از پاسخ استاندارد

تمام کنترلرها می‌توانند از `RMS\Api\Support\Response\Concerns\HandlesApiResponse` استفاده کنند یا از `BaseApiController` ارث ببرند:

```php
class PostController extends BaseApiController
{
    public function index()
    {
        return $this->apiSuccess(PostResource::collection(Post::latest()->paginate()));
    }
}
```

## Modifier Pipeline

برای تزریق اطلاعات به متای پاسخ یا تغییر payload قبل از ارسال:

```php
class AppendMetaModifier implements ResponseModifier
{
    public function modify(ApiResponsePayload $payload, Request $request): void
    {
        $metaKey = config('rms-api.response.meta_key', 'meta');
        $meta = $payload->get($metaKey, []);
        $meta['handled_by'] = 'my-modifier';
        $payload->set($metaKey, $meta);
    }
}

// config/rms-api.php
'response' => [
    'modifiers' => [
        \App\Api\Modifiers\AppendMetaModifier::class,
    ],
],
```

## Auth Drivers

`RMS\Api\Support\Auth\Drivers\EmailPasswordDriver` یک نمونهٔ ساده است که از مدل کاربر اصلی استفاده می‌کند. برای driver جدید:

1. interface `RMS\Api\Contracts\AuthDriver` را پیاده کنید.
2. در `config/rms-api.php` زیر کلید `auth.drivers` ثبت کنید.
3. اگر نیاز به guard متفاوت دارید، مقدار `auth.guard` را تغییر دهید.

## Middleware ها

- `EnsureApiEnabled`: با توجه به `config('rms-api.enabled')`، درخواست را قبول یا 404 می‌دهد.
- `ResolveGuard`: guard تعیین‌شده در کانفیگ را روی request می‌گذارد.
- `ApplyRateLimit`: با توجه به تنظیمات `rate_limit`، هدرهای rate-limit را اضافه می‌کند و در صورت نیاز 429 می‌دهد.

## اجرای تست‌ها

```bash
vendor/bin/phpunit tests/Feature/RmsApiAuthTest.php
vendor/bin/phpunit tests/Feature/RmsApiResponseTest.php
```

این تست‌ها سناریوهای زیر را پوشش می‌دهند:
- ثبت‌نام، ورود، دریافت پروفایل و خروج با driver ایمیل/رمز.
- پاسخ استاندارد با modifier pipeline، بررسی rate-limit و غیرفعال کردن API.

## نقشه راه بعدی

- افزودن مستند Swagger/OpenAPI برای ماژول‌ها.
- پیاده‌سازی driverهای OTP یا SSO.
- اتصال ماژول‌های shop/blog به `Api::module()` به جای تعریف مستقیم route.

لطفاً هر ماژول جدیدی که نیاز به API دارد را با استفاده از این پکیج راه‌اندازی کنید تا هماهنگی در احراز هویت، پاسخ‌دهی و logging حفظ شود.

