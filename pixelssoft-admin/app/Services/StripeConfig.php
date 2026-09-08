<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Crypt;
use Stripe\StripeClient;
use Throwable;

class StripeConfig
{
    public const KEY_PUBLISHABLE = 'stripe_publishable_key';
    public const KEY_SECRET = 'stripe_secret_key';
    public const KEY_WEBHOOK = 'stripe_webhook_secret';

    public static function publishableKey(): ?string
    {
        return static::plain(self::KEY_PUBLISHABLE);
    }

    public static function secretKey(): ?string
    {
        return static::decrypt(self::KEY_SECRET);
    }

    public static function webhookSecret(): ?string
    {
        return static::decrypt(self::KEY_WEBHOOK);
    }

    public static function isConfigured(): bool
    {
        return (bool) (static::publishableKey() && static::secretKey());
    }

    public static function client(): StripeClient
    {
        $secret = static::secretKey();
        if (!$secret) {
            throw new \RuntimeException('Stripe secret key is not configured.');
        }

        return new StripeClient($secret);
    }

    public static function setPublishable(?string $value): void
    {
        if ($value !== null && $value !== '') {
            SiteSetting::setValue(self::KEY_PUBLISHABLE, trim($value));
        }
    }

    public static function setSecret(?string $value): void
    {
        if ($value !== null && $value !== '') {
            SiteSetting::setValue(self::KEY_SECRET, Crypt::encryptString(trim($value)));
        }
    }

    public static function setWebhookSecret(?string $value): void
    {
        if ($value !== null && $value !== '') {
            SiteSetting::setValue(self::KEY_WEBHOOK, Crypt::encryptString(trim($value)));
        }
    }

    public static function maskedSecret(): string
    {
        $value = static::secretKey();

        return $value ? static::mask($value) : '';
    }

    public static function maskedWebhook(): string
    {
        $value = static::webhookSecret();

        return $value ? static::mask($value) : '';
    }

    private static function plain(string $key): ?string
    {
        $value = SiteSetting::getValue($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private static function decrypt(string $key): ?string
    {
        $value = SiteSetting::getValue($key);
        if (!is_string($value) || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }

    private static function mask(string $value): string
    {
        $len = strlen($value);
        if ($len <= 8) {
            return str_repeat('•', $len);
        }

        return substr($value, 0, 7) . str_repeat('•', max(4, $len - 11)) . substr($value, -4);
    }
}
