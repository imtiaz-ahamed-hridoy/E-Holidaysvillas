<?php
namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'property_id'          => $this->property_id,
            'property_category_id' => $this->property_category_id,
            'title'                => $this->title,
            'description'          => $this->formatDescription($this->description),
            'stars'                => $this->stars,
            'no_guests'            => $this->no_guests,
            'no_bedrooms'          => $this->no_bedrooms,
            'no_bathrooms'         => $this->no_bathrooms,
            'price'                => $this->price,
            'location'             => $this->whenLoaded('location', function () {
                if (!$this->location) {
                    return null;
                }

                return [
                    'city'    => $this->location->city,
                    'region'  => $this->location->region,
                    'country' => $this->location->country,
                    'area'    => $this->location->area,
                ];
            }),
            'property_type'        => $this->whenLoaded('propertyType', fn () => $this->propertyType?->title),
            'main_photo'           => $this->getMainPhotoUrl(),
            'created_at'           => $this->created_at?->toDateTimeString(),
        ];
    }

    private function formatDescription($description)
    {
        if (empty($description)) return null;
        $stripped = strip_tags($description);
        return mb_strimwidth($stripped, 0, 150, '...');
    }

    private function getMainPhotoUrl()
    {
        if ($this->relationLoaded('photos')) {
            $photo = $this->photos?->first();
        } else {
            $photo = $this->photos()->first();
        }

        if (!$photo || empty($photo->property_photo_file)) {
            return null;
        }

        return \App\Helpers\Helper::imageUrl($photo->property_photo_file);
    }
}
