<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Book extends Model
{
    use HasFactory;


    public function reviews(){
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title): Builder{
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

    public function scopeWithAvgRate(Builder $query, $from = null, $to = null): Builder|QueryBuilder{
        return $query->withAvg([
            'reviews' => fn(Builder $q) => $this->filterDate($q, $from, $to)], 'rating');
    }

    public function scopeWithReviewsCount(Builder $query, $from = null, $to = null): Builder|QueryBuilder{
        return $query->withCount([
            'reviews' => fn(Builder $q) => $this->filterDate($q, $from, $to)]);
    }

    public function scopeHighestRate(Builder $query): Builder|QueryBuilder{
        return $query->withAvgRate()
            ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopePopular(Builder $query): Builder|QueryBuilder{
        return $query->withReviewsCount()
            ->orderBy('reviews_count', 'desc');
    }

    private function filterDate(Builder $query, $from = null, $to = null){
        if ($from && !$to) {
            $query->having('created_at', '>=', $from);
        }elseif (!$from && $to) {
            $query->having('created_at', '<=', $to);
        }elseif ($from && $to) {
            $query->havingBetween('created_at', [$from, $to]);
        }
    }

    public function scopeMinReviews(Builder $query, int $minReviews): Builder|QueryBuilder{
        return $query->having('reviews_count', '>=', $minReviews);
    }


    public function scopePopularLastMonth(Builder $query): Builder|QueryBuilder{
        return $query->popular(now()->subMonth(), now())
            ->highestRate(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopePopularLast6Months(Builder $query): Builder|QueryBuilder{
        return $query->popular(now()->subMonths(6), now())
            ->highestRate(now()->subMonths(6), now())
            ->minReviews(5);
    }

    public function scopeHighestRateLastMonth(Builder $query): Builder|QueryBuilder{
        return $query->highestRate(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopeHighestRateLast6Months(Builder $query): Builder|QueryBuilder{
        return $query->highestRate(now()->subMonths(6), now())
            ->popular(now()->subMonths(6), now())
            ->minReviews(5);
    }

    protected static function booted(){
        static::updated(fn(Book $book) => cache()->forget('book:' . $book->id));
        static::deleted(fn(Book $book) => cache()->forget('book:' . $book->id));
    }
}
