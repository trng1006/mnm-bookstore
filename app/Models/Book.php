<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int|null $category_id
 * @property int|null $author_id
 * @property int $price_original
 * @property int $price_current
 * @property string|null $cover_image
 * @property string|null $summary
 * @property string|null $description
 * @property string|null $publisher
 * @property int|null $publication_year
 * @property int|null $pages
 * @property int $stock
 * @property float $rating
 * @property int $reviews_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Author|null $author
 * @property-read Category|null $category
 * @property-read Collection<int, Review> $reviews
 * @property-read Collection<int, OrderItem> $orderItems
 */
class Book extends Model
{
    protected $guarded = [];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function hasInStock(): bool
    {
        return ($this->stock ?? 0) > 0;
    }

    public function updateRatingStats(): void
    {
        $avgRating = $this->reviews()->avg('rating') ?? 0;
        $count = $this->reviews()->count();
        $this->update([
            'rating' => round($avgRating, 1),
            'reviews_count' => $count,
        ]);
    }
}
