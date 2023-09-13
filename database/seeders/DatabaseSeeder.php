<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Book::factory(33)->create()->each(function ($book){
            $numReviews = random_int(5, 30);

            Review::factory()->count($numReviews)
                ->goodReviews()
                ->for($book)
                ->create();
        });

        Book::factory(33)->create()->each(function ($book){
            $numReviews = random_int(5, 30);

            Review::factory()->count($numReviews)
                ->badReviews()
                ->for($book)
                ->create();
        });

        Book::factory(34)->create()->each(function ($book){
            $numReviews = random_int(5, 30);

            Review::factory()->count($numReviews)
                ->averageReviews()
                ->for($book)
                ->create();
        });
    }
}
