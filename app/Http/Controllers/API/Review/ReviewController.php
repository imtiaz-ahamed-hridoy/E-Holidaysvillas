<?php

namespace App\Http\Controllers\API\Review;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class ReviewController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/reviews?property_id=&per_page=
     * Fetch reviews for a property — read-only listing for customers.
     */
    public function index(Request $request)
    {
        $request->validate([
            'property_id' => 'required|integer|exists:properties,property_id'
        ]);

        $propertyId = $request->property_id;

        // Stats query — only active reviews
        $baseQuery = Review::where('property_id', $propertyId)
            ->where('status', 'active');

        $totalReviews  = $baseQuery->count();
        $averageRating = $totalReviews > 0 ? round((clone $baseQuery)->avg('rating_stars'), 1) : 0;

        // Star distribution
        $starDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $counts = Review::where('property_id', $propertyId)
            ->where('status', 'active')
            ->selectRaw('rating_stars, count(*) as total')
            ->groupBy('rating_stars')
            ->pluck('total', 'rating_stars')
            ->toArray();

        foreach ($counts as $stars => $count) {
            if (isset($starDistribution[$stars])) {
                $starDistribution[$stars] = (int) $count;
            }
        }

        // Paginated reviews
        $reviews = Review::where('property_id', $propertyId)
            ->where('status', 'active')
            ->latest()
            ->paginate(10);

        $reviews->getCollection()->transform(function ($review) {
            return $this->formatReview($review);
        });

        return $this->ok('Reviews fetched successfully', [
            'average_rating'    => $averageRating,
            'total_reviews'     => $totalReviews,
            'star_distribution' => $starDistribution,
            'comments'          => $reviews,
        ]);
    }

    /**
     * GET /api/reviews/{id}
     * Fetch a single review.
     */
    public function show($id)
    {
        $review = Review::where('status', 'active')
            ->find($id);

        if (!$review) {
            return $this->error('Review not found', 404);
        }

        return $this->ok('Review fetched successfully', $this->formatReview($review));
    }

    /**
     * Transform a review into a clean API response shape.
     * Returns: name, avatar (initials), rating, details, time_ago.
     */
    private function formatReview($review): array
    {
        // Build avatar: use uploaded avatar, else generate initials
        $name = $review->name ?? 'Guest';
        $avatar = null;

        if ($review->avatar) {
            $avatar = \App\Helpers\Helper::imageUrl('uploads/reviews/' . $review->avatar);
        }

        // Generate initials from name (e.g. "Sarah Johnson" → "SJ")
        $words = explode(' ', trim($name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(mb_substr($word, 0, 1));
        }

        return [
            'id'        => $review->review_id,
            'name'      => $name,
            'avatar'    => $avatar,
            'initials'  => $initials,
            'rating'    => $review->rating_stars,
            'details'   => $review->details,
            'time_ago'  => $review->created_at->diffForHumans(),
            'date'      => $review->created_at->format('M d, Y'),
        ];
    }
}
