<?php

namespace App\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Stand-in for Orange Money / MTN MoMo collection APIs. The "waiting for the customer to approve
 * the prompt" delay lives in the UI (x-mobile-money-pay); here the charge always succeeds.
 * Swap this class for a real operator integration later.
 */
class MobileMoneySimulator
{
    public const OPERATORS = [
        'orange_money' => 'OM',
        'mtn_momo' => 'MOMO',
    ];

    // Cameroon mobile number, optional +237 prefix, separators allowed (e.g. "+237 6 77 11 22 33")
    public const PHONE_RULE = 'regex:/^(\+?237)?[\s.-]*6([\s.-]*\d){8}$/';

    /** @return string the operator transaction reference */
    public function charge(string $method, string $phone, float $amount): string
    {
        if (! isset(self::OPERATORS[$method])) {
            throw new InvalidArgumentException("Unsupported mobile money operator [$method].");
        }

        return self::OPERATORS[$method].'-'.Str::upper(Str::random(8));
    }
}
