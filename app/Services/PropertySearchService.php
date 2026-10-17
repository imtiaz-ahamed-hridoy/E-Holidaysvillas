<?php

namespace App\Services;

use App\Models\Property;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PropertySearchService
{
    private const ACTIVE_BOOKING_STATUSES = ['pending', 'confirmed', 'checked_in'];

    public function search(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters = $this->normalizeFilters($filters);

        return $this->buildSearchQuery($filters)
            ->orderByDesc('properties.created_at')
            ->paginate($filters['per_page']);
    }

    private function buildSearchQuery(array $filters): Builder
    {
        return Property::query()
            ->select([
                'properties.property_id',
                'properties.property_category_id',
                'properties.property_type_id',
                'properties.title',
                'properties.description',
                'properties.stars',
                'properties.no_guests',
                'properties.no_bedrooms',
                'properties.no_bathrooms',
                'properties.status',
                'properties.allow_bookings',
                'properties.created_at',
            ])
            ->with([
                'location:location_id,property_id,city,region,country,area',
                'photos' => fn ($query) => $query
                    ->select('property_photo_id', 'property_id', 'property_photo_file', 'status', 'created_at')
                    ->where('status', 'active')
                    ->orderByDesc('created_at'),
                'propertyType:property_type_id,title',
                'rates' => fn ($query) => $query
                    ->select('rate_id', 'property_id', 'nightly_rate', 'start_date', 'end_date', 'status')
                    ->where('status', 'active')
                    ->orderBy('start_date'),
            ])
            ->where('properties.status', 'active')
            ->where('properties.allow_bookings', true)
            ->when($filters['location'], fn (Builder $query) => $this->applyLocationFilter($query, $filters['location']))
            ->when($filters['property_type_id'], fn (Builder $query) => $this->applyPropertyTypeFilter($query, $filters['property_type_id']))
            ->when($filters['guests'], fn (Builder $query) => $this->applyGuestsFilter($query, $filters['guests']))
            ->when(
                $filters['check_in'] && $filters['check_out'],
                fn (Builder $query) => $this->applyAvailabilityFilter($query, $filters['check_in'], $filters['check_out'])
            )
            ->when(
                $filters['min_price'] !== null || $filters['max_price'] !== null,
                fn (Builder $query) => $this->applyPriceFilter(
                    $query,
                    $filters['min_price'],
                    $filters['max_price'],
                    $filters['check_in']
                )
            )
            ->when($filters['search'], fn (Builder $query) => $this->applyGlobalSearch($query, $filters['search']));
    }

    private function applyLocationFilter(Builder $query, string $location): Builder
    {
        $pattern = $this->containsPattern($location);

        return $query->whereHas('location', function (Builder $locationQuery) use ($pattern) {
            $locationQuery->where(function (Builder $grouped) use ($pattern) {
                $grouped->where('locations.city', 'LIKE', $pattern)
                    ->orWhere('locations.region', 'LIKE', $pattern)
                    ->orWhere('locations.area', 'LIKE', $pattern)
                    ->orWhere('locations.country', 'LIKE', $pattern);
            });
        });
    }

    private function applyPropertyTypeFilter(Builder $query, int $propertyTypeId): Builder
    {
        return $query->where('properties.property_type_id', $propertyTypeId);
    }

    private function applyGuestsFilter(Builder $query, int $guests): Builder
    {
        return $query->where('properties.no_guests', '>=', $guests);
    }

    private function applyAvailabilityFilter(Builder $query, string $checkIn, string $checkOut): Builder
    {
        $checkInDate = CarbonImmutable::createFromFormat('Y-m-d', $checkIn)->toDateString();
        $checkOutDate = CarbonImmutable::createFromFormat('Y-m-d', $checkOut)->toDateString();

        return $query->whereDoesntHave('bookings', function (Builder $bookingQuery) use ($checkInDate, $checkOutDate) {
            $bookingQuery
                ->where('bookings.status', 'active')
                ->whereIn('bookings.booking_status', self::ACTIVE_BOOKING_STATUSES)
                ->where('bookings.check_in', '<', $checkOutDate)
                ->where('bookings.check_out', '>', $checkInDate);
        });
    }

    private function applyPriceFilter(Builder $query, $minPrice, $maxPrice, ?string $checkIn): Builder
    {
        return $query->whereHas('rates', function (Builder $rateQuery) use ($minPrice, $maxPrice, $checkIn) {
            $rateQuery->where('rates.status', 'active');

            if ($checkIn !== null) {
                $rateDate = CarbonImmutable::createFromFormat('Y-m-d', $checkIn)->toDateString();

                $rateQuery
                    ->where('rates.start_date', '<=', $rateDate)
                    ->where('rates.end_date', '>=', $rateDate);
            }

            if ($minPrice !== null) {
                $rateQuery->where('rates.nightly_rate', '>=', (float) $minPrice);
            }

            if ($maxPrice !== null) {
                $rateQuery->where('rates.nightly_rate', '<=', (float) $maxPrice);
            }
        });
    }

    private function applyGlobalSearch(Builder $query, string $search): Builder
    {
        $pattern = $this->containsPattern($search);

        return $query->where(function (Builder $grouped) use ($pattern) {
            $grouped->where('properties.title', 'LIKE', $pattern)
                ->orWhere('properties.description', 'LIKE', $pattern)
                ->orWhereHas('location', function (Builder $locationQuery) use ($pattern) {
                    $locationQuery->where(function (Builder $locationGroup) use ($pattern) {
                        $locationGroup->where('locations.city', 'LIKE', $pattern)
                            ->orWhere('locations.region', 'LIKE', $pattern)
                            ->orWhere('locations.area', 'LIKE', $pattern)
                            ->orWhere('locations.country', 'LIKE', $pattern);
                    });
                });
        });
    }

    private function normalizeFilters(array $filters): array
    {
        return [
            'search' => $this->nullableString($filters['search'] ?? null),
            'location' => $this->nullableString($filters['location'] ?? null),
            'check_in' => $this->nullableString($filters['check_in'] ?? null),
            'check_out' => $this->nullableString($filters['check_out'] ?? null),
            'guests' => isset($filters['guests']) ? (int) $filters['guests'] : null,
            'property_type_id' => isset($filters['property_type_id']) ? (int) $filters['property_type_id'] : null,
            'min_price' => array_key_exists('min_price', $filters) && $filters['min_price'] !== null ? (float) $filters['min_price'] : null,
            'max_price' => array_key_exists('max_price', $filters) && $filters['max_price'] !== null ? (float) $filters['max_price'] : null,
            'per_page' => $this->validatePerPage($filters['per_page'] ?? 14),
        ];
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function containsPattern(string $value): string
    {
        return '%' . addcslashes($value, '\\%_') . '%';
    }

    private function validatePerPage($perPage): int
    {
        return min(100, max(1, (int) $perPage));
    }
}
