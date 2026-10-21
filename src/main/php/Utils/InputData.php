<?php
declare(strict_types=1);

namespace Br\Udesc\Alsap\Utils;

use InvalidArgumentException;

final class InputData
{
    public static function strings(array $input, array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $value = $input[$field] ?? null;
            if ($value !== null && !is_string($value)) {
                throw new InvalidArgumentException('O campo ' . $field . ' deve ser texto ou nulo.');
            }
            $result[$field] = $value;
        }
        return $result;
    }

    public static function number(array $input, string $field, bool $integer = false): mixed
    {
        $value = $input[$field] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_int($value) && !is_float($value) && !(is_string($value) && is_numeric($value))) {
            throw new InvalidArgumentException('O campo ' . $field . ' deve ser numérico ou nulo.');
        }
        if ($integer && (float) $value !== (float) (int) $value) {
            throw new InvalidArgumentException('O campo ' . $field . ' deve ser um número inteiro.');
        }
        return $integer ? (int) $value : (float) $value;
    }

    public static function optionalId(array $input, string $field): ?int
    {
        $value = $input[$field] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            throw new InvalidArgumentException('O campo ' . $field . ' deve ser um identificador numérico ou nulo.');
        }
        return (int) $value;
    }
}