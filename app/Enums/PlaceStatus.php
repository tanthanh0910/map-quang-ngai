<?php

namespace App\Enums;

enum PlaceStatus: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';
    case PENDING  = 'pending';
    case RESOLVED = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Đang hoạt động',
            self::INACTIVE => 'Ngưng hoạt động',
            self::PENDING  => 'Chờ xử lý',
            self::RESOLVED => 'Đã xử lý',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }

    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }
        return $out;
    }
}
