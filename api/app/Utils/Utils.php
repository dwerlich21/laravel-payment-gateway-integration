<?php

namespace App\Utils;

use Carbon\Carbon;

class Utils
{
    public static function onlyNumbers($value): array|string|null
    {
        return preg_replace('/\D/', '', $value);
    }

    public static function applyMask(string $value, string $mask): string
    {
        $masked = '';
        $k = 0;

        for ($i = 0; $i < strlen($mask); $i++) {
            if ($mask[$i] === '#' && isset($value[$k])) {
                $masked .= $value[$k++];
            } else if (isset($mask[$i])) {
                $masked .= $mask[$i];
            }
        }

        return $masked;
    }

    public static function convertDateIso($value): ?string
    {
        return $value ? Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d') : null;
    }

    public static function convertDate($value): ?string
    {
        return $value ? Carbon::createFromFormat('Y-m-d', $value)->format('d/m/Y') : null;
    }

    public static function floatToMoney($value): ?string
    {
        return $value ? 'R$ ' .  number_format($value, 2, ',', '.') : null;
    }

    public static function moneyToFloat($value): ?float
    {
        if ($value === '') return null;
        $value = str_replace('R$ ', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return (float) $value;
    }
}
