<?php
namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        // Format photo URLs
        if (isset($data['photos'])) {
            foreach ($data['photos'] as &$photo) {
                if (!empty($photo['property_photo_file'])) {
                    $photo['property_photo_file'] = \App\Helpers\Helper::imageUrl($photo['property_photo_file']);
                }
            }
        }

        // Format video URLs
        if (isset($data['videos'])) {
            foreach ($data['videos'] as &$video) {
                if (!empty($video['video_file'])) $video['video_file'] = \App\Helpers\Helper::imageUrl($video['video_file']);
                if (!empty($video['poster_file'])) $video['poster_file'] = \App\Helpers\Helper::imageUrl($video['poster_file']);
            }
        }

        // Format Amenities, Facilities, and Policies icon URLs
        foreach (['amenities', 'facilities', 'policies'] as $group) {
            if (isset($data[$group])) {
                foreach ($data[$group] as &$item) {
                    if (isset($item['icon_type']) && $item['icon_type'] === 'image' && !empty($item['icon'])) {
                        $item['icon'] = \App\Helpers\Helper::imageUrl($item['icon']);
                    }
                }
            }
        }

        // Add price attribute
        $data['price'] = $this->price;

        // Add availability calendar
        $data['availability'] = $this->getAvailability();

        return $data;
    }

    /**
     * Fetch and format availability date ranges.
     */
    private function getAvailability()
    {
        $availability = [];

        // 1. Confirmed and Pending Bookings
        $bookings = $this->relationLoaded('bookings') ? $this->bookings : $this->bookings()->get();
        $activeBookings = $bookings->whereIn('booking_status', ['confirmed', 'pending', 'checked_in']);

        foreach ($activeBookings as $booking) {
            if (!$booking->check_in || !$booking->check_out) {
                continue;
            }
            $availability[] = [
                'start'  => \Carbon\Carbon::parse($booking->check_in)->format('Y-m-d'),
                'end'    => \Carbon\Carbon::parse($booking->check_out)->format('Y-m-d'),
                'status' => $booking->booking_status === 'confirmed' ? 'booked' : 'pending',
                'type'   => 'booking'
            ];
        }

        return $availability;
    }
}
