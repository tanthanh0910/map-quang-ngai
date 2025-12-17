<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Place;

class PlaceFactory extends Factory
{
    protected $model = Place::class;

    public function definition()
    {
        $types = ['restaurant','hotel','office'];
        $type = $this->faker->randomElement($types);
        return [
            'type' => $type,
            'name' => $this->faker->company . ' ' . ucfirst($type),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'lat' => $this->faker->latitude(15,15.5),
            'lng' => $this->faker->longitude(108.5,109.0),
            'status' => 'active'
        ];
    }
}
