<?php

namespace App\Enums;

enum ActiveEnums: int
{
    case NO = 0;
    case YES = 1;

    /**
     * Set the enum value from a boolean or integer
     */
    public static function set(bool|int|null $value): self
    {
        if ($value === null) {
            return self::NO;
        }

        if (is_bool($value)) {
            return $value ? self::YES : self::NO;
        }

        return match ($value) {
            1, '1' => self::YES,
            0, '0' => self::NO,
            default => self::NO,
        };
    }

    /**
     * Get the boolean value
     */
    public function toBool(): bool
    {
        return $this === self::YES;
    }

    /**
     * Get the integer value
     */
    public function toInt(): int
    {
        return $this->value;
    }

    /**
     * Get the string representation
     */
    public function toString(): string
    {
        return $this === self::YES ? 'Yes' : 'No';
    }

    /**
     * Check if the value is YES
     */
    public function isYes(): bool
    {
        return $this === self::YES;
    }

    /**
     * Check if the value is NO
     */
    public function isNo(): bool
    {
        return $this === self::NO;
    }

    /**
     * Get all cases as an array
     */
    public static function options(): array
    {
        return [
            'No' => self::NO->value,
            'Yes' => self::YES->value,
        ];
    }

    /**
     * Get label for the enum case
     */
    public function label(): string
    {
        return match ($this) {
            self::YES => 'Yes',
            self::NO => 'No',
        };
    }
}
