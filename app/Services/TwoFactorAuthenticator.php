<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Authenticator-app (TOTP, RFC 6238) second factor plus one-time recovery codes.
 */
class TwoFactorAuthenticator
{
    public const RECOVERY_CODE_COUNT = 8;

    private Google2FA $engine;

    public function __construct()
    {
        $this->engine = new Google2FA;
    }

    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey(32);
    }

    /** SVG QR code encoding the otpauth:// URL for the user's pending secret. */
    public function qrCodeSvg(User $user): string
    {
        $url = $this->engine->getQRCodeUrl(config('app.name'), $user->email, $user->two_factor_secret);

        $svg = (new Writer(new ImageRenderer(
            new RendererStyle(176, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(20, 33, 61))),
            new SvgImageBackEnd,
        )))->writeString($url);

        // Drop the XML declaration so the SVG can be inlined
        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    /**
     * Check a 6-digit code (±30s drift). Each code is accepted once: anything from the same or an
     * earlier time step than the last accepted code is rejected, which blocks replay of a seen code.
     */
    public function verifyCode(User $user, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (! preg_match('/^\d{6}$/', $code) || ! $user->two_factor_secret) {
            return false;
        }

        $step = $this->engine->verifyKeyNewer($user->two_factor_secret, $code, (int) $user->two_factor_last_used_step);

        if ($step === false) {
            return false;
        }

        $user->forceFill(['two_factor_last_used_step' => $step])->save();

        return true;
    }

    /** @return list<string> plain codes, shown to the user once */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODE_COUNT))
            ->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))
            ->all();
    }

    /** Only hashes are stored, so saved codes can't be read back from the database. */
    public function hashRecoveryCodes(array $codes): array
    {
        return array_map(fn (string $code) => hash('sha256', $code), $codes);
    }

    public function useRecoveryCode(User $user, string $code): bool
    {
        $hash = hash('sha256', Str::lower(trim($code)));
        $codes = $user->two_factor_recovery_codes ?? [];

        foreach ($codes as $index => $stored) {
            if (hash_equals($stored, $hash)) {
                unset($codes[$index]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }
}
