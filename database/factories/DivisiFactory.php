<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Divisi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Divisi>
 */
class DivisiFactory extends Factory
{
    protected $model = Divisi::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' '.fake()->numberBetween(10, 99);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(8),
        ];
    }
}

