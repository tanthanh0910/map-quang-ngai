<?php

namespace Database\Seeders;

use App\Enums\PlaceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Place;

class PlaceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Place::truncate();
        
        Place::create([
            'type' => PlaceType::RESTAURANT,
            'icon' => 'restaurant.svg',
            'name' => 'Nhà Hàng Thu Thảo',
            'address' => '114 Đ. Đỗ Quang Thắng, Nguyễn Nghiêm, Đức Phổ, Quảng Ngãi, Vietnam',
            'phone' => '02553859332',
            'lat' => 14.8104465,
            'lng' => 108.9570977,
            'thumbnail' => 'images/nha-hang-thu-thao.jpg',
            'time' => 'Mở cửa: 10:00 - 22:00',
            'status' => 'active'
        ]);
        Place::create([
            'type' => PlaceType::OFFICE,
            'icon' => 'government-office.svg',
            'name' => 'UBND Phường Đức Phổ',
            'address' => 'RX54+MWC, Nguyễn Nghiêm, Đức Phổ, Quảng Ngãi, Vietnam',
            'phone' => '0235-123456',
            'lat' => 14.809082,
            'lng' => 108.957069,
            'status' => 'active'
        ]);

        Place::create([
            'type' => PlaceType::HOTEL,
            'icon' => 'hotel.svg',
            'name' => 'ADA Hotel',
            'address' => '50 Trần Quang Diệu, Thị xã, Đức Phổ, Quảng Ngãi 54306, Vietnam',
            'phone' => '0235-987654',
            'lat' => 14.809595,
            'lng' => 108.955349,
            'status' => 'active'
        ]);

        Place::create([
            'type' => PlaceType::HOTEL,
            'icon' => 'hotel.svg',
            'name' => 'Khách Sạn Tứ Phương',
            'address' => 'Phan Thái Ất. TDP4, Đức Phổ, Quảng Ngãi, Vietnam',
            'phone' => '0235-555555',
            'lat' => 14.815639,
            'lng' => 108.950348,
            'status' => 'active'
        ]);

        Place::create([
            'type' => PlaceType::GAS,
            'icon' => 'gas.svg',
            'name' => 'Petrolimex Gas Station Store No.07',
            'address' => '571 Đ. Nguyễn Nghiêm, p, Đức Phổ, Quảng Ngãi, Vietnam',
            'phone' => '0235-555555',
            'lat' => 14.80635,
            'lng' => 108.958241,
            'status' => 'active'
        ]);

    }
}
