<?php

namespace App\Enums;

enum PlaceType: string
{
    case RESTAURANT = 'restaurant';
    case HOTEL = 'hotel';
    case OFFICE = 'office';
    case BISTRO = 'bistro';
    case CAFE = 'cafe';
    case GAS = 'gas';
    case STORE = 'store';

    public function label(): string
    {
        return match($this) {
            self::RESTAURANT => 'Nhà hàng',
            self::HOTEL => 'Khách sạn',
            self::OFFICE => 'Cơ quan',
            self::BISTRO => 'Quán ăn nhỏ',
            self::CAFE => 'Cafe',
            self::GAS => 'Trạm xăng',
            self::STORE => 'Cửa hàng',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::RESTAURANT => 'restaurant.svg',
            self::HOTEL => 'hotel.svg',
            self::OFFICE => 'office.svg',
            self::BISTRO => 'bistro.svg',
            self::CAFE => 'cafe.svg',
            self::GAS => 'gas.svg',
            self::STORE => 'store.svg',
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
