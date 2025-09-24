<?php

namespace Database\Factories;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition()
    {
        return [
            'product_id' => $this->faker->numberBetween(1, 50),
            'quantity' => $this->faker->numberBetween(1, 100),
            'minimum_stock' => $this->faker->numberBetween(1, 10),
            'location' => $this->faker->word(),
        ];
    }
}