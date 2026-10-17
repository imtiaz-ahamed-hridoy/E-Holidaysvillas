<?php

namespace Tests\Feature\API;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use App\Models\Rate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySearchApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Currency $currency;
    private PropertyType $villaType;
    private PropertyType $chaletType;
    private PropertyCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->currency = Currency::create([
            'title' => 'Euro',
            'currency_code' => 'EUR',
            'currency_rate' => 1,
            'status' => 'active',
            'is_default' => 'yes',
        ]);
        $this->villaType = PropertyType::forceCreate([
            'property_type_id' => 7,
            'title' => 'Villa',
            'slug' => 'villa',
            'status' => 'active',
        ]);
        $this->chaletType = PropertyType::forceCreate([
            'property_type_id' => 8,
            'title' => 'Chalet',
            'slug' => 'chalet',
            'status' => 'active',
        ]);
        $this->category = PropertyCategory::create([
            'title' => 'Holiday Homes',
            'slug' => 'holiday-homes',
            'status' => 'active',
        ]);
    }

    public function test_issue_payload_returns_only_matching_valencia_properties(): void
    {
        $valencia = $this->createProperty(
            ['title' => 'Villa Valencia Central', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 6],
            ['city' => 'Valencia', 'region' => 'Valencia', 'area' => 'City Center'],
            220
        );
        $this->createProperty(
            ['title' => 'Villa Marbella Beach', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 8],
            ['city' => 'Marbella', 'region' => 'Costa del Sol', 'area' => 'Los Monteros'],
            330
        );
        $this->createProperty(
            ['title' => 'Villa Tenerife Coast', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 8],
            ['city' => 'Tenerife', 'region' => 'Canary Islands', 'area' => 'Santa Cruz'],
            180
        );
        $this->createProperty(
            ['title' => 'Villa Girona Hills', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 8],
            ['city' => 'Girona', 'region' => 'Catalonia', 'area' => 'Old Town'],
            180
        );

        $response = $this->postJson('/api/properties/search', [
            'search' => '',
            'location' => 'Valencia',
            'check_in' => '2026-05-01',
            'check_out' => '2026-05-10',
            'guests' => 4,
            'property_type_id' => 7,
            'min_price' => null,
            'max_price' => null,
            'per_page' => 10,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.data.0.property_id', $valencia->property_id)
            ->assertJsonPath('data.data.0.location.city', 'Valencia');

        $cities = collect($response->json('data.data'))->pluck('location.city')->all();

        $this->assertNotContains('Marbella', $cities);
        $this->assertNotContains('Tenerife', $cities);
        $this->assertNotContains('Girona', $cities);
    }

    public function test_filters_are_combined_with_and_conditions(): void
    {
        $matching = $this->createProperty(
            ['title' => 'Matching Valencia Villa', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 5],
            ['city' => 'Valencia', 'region' => 'Valencia', 'area' => 'City Center'],
            150
        );
        $this->createProperty(
            ['title' => 'Wrong Type Valencia Chalet', 'property_type_id' => $this->chaletType->property_type_id, 'no_guests' => 5],
            ['city' => 'Valencia', 'region' => 'Valencia', 'area' => 'City Center'],
            150
        );
        $this->createProperty(
            ['title' => 'Too Small Valencia Villa', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 2],
            ['city' => 'Valencia', 'region' => 'Valencia', 'area' => 'Beach Zone'],
            150
        );
        $booked = $this->createProperty(
            ['title' => 'Booked Valencia Villa', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 5],
            ['city' => 'Valencia', 'region' => 'Valencia', 'area' => 'Marina'],
            150
        );
        $this->createBooking($booked, '2026-05-05', '2026-05-08');

        $response = $this->postJson('/api/properties/search', [
            'location' => 'Valencia',
            'check_in' => '2026-05-01',
            'check_out' => '2026-05-10',
            'guests' => 4,
            'property_type_id' => 7,
            'per_page' => 10,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.data.0.property_id', $matching->property_id);
    }

    public function test_availability_allows_checkout_checkin_boundary_but_blocks_overlap(): void
    {
        $property = $this->createProperty(
            ['title' => 'Boundary Villa', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 4],
            ['city' => 'Valencia', 'region' => 'Valencia', 'area' => 'City Center'],
            120
        );
        $this->createBooking($property, '2026-05-01', '2026-05-10');

        $this->postJson('/api/properties/search', [
            'location' => 'Valencia',
            'check_in' => '2026-05-10',
            'check_out' => '2026-05-15',
            'property_type_id' => 7,
            'per_page' => 10,
        ])
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1);

        $this->postJson('/api/properties/search', [
            'location' => 'Valencia',
            'check_in' => '2026-05-09',
            'check_out' => '2026-05-15',
            'property_type_id' => 7,
            'per_page' => 10,
        ])
            ->assertOk()
            ->assertJsonPath('data.meta.total', 0)
            ->assertJsonPath('data.data', []);
    }

    public function test_price_filters_ignore_null_and_apply_present_bounds(): void
    {
        $cheap = $this->createProperty(
            ['title' => 'Cheap Valencia Villa', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 4],
            ['city' => 'Valencia', 'region' => 'Valencia', 'area' => 'City Center'],
            90
        );
        $this->createProperty(
            ['title' => 'Expensive Valencia Villa', 'property_type_id' => $this->villaType->property_type_id, 'no_guests' => 4],
            ['city' => 'Valencia', 'region' => 'Valencia', 'area' => 'City Center'],
            300
        );

        $this->postJson('/api/properties/search', [
            'location' => 'Valencia',
            'min_price' => null,
            'max_price' => null,
            'per_page' => 10,
        ])
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2);

        $this->postJson('/api/properties/search', [
            'location' => 'Valencia',
            'min_price' => 80,
            'max_price' => 100,
            'per_page' => 10,
        ])
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.data.0.property_id', $cheap->property_id);
    }

    public function test_validation_and_empty_results_keep_api_contract(): void
    {
        $this->postJson('/api/properties/search', [
            'check_in' => '2026-05-10',
            'check_out' => '2026-05-01',
            'guests' => 0,
            'min_price' => 100,
            'max_price' => 50,
        ])->assertStatus(422);

        $this->postJson('/api/properties/search', [
            'location' => 'Nowhere',
            'per_page' => 10,
        ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.data', [])
            ->assertJsonPath('data.meta.total', 0)
            ->assertJsonStructure([
                'data' => ['links', 'meta'],
            ]);
    }

    public function test_property_detail_endpoint_does_not_include_removed_request_relation(): void
    {
        $property = $this->createProperty(
            ['title' => 'Detail Villa', 'property_type_id' => $this->villaType->property_type_id],
            ['city' => 'Valencia', 'region' => 'Valencia', 'area' => 'City Center'],
            180
        );

        $response = $this->getJson("/api/properties/{$property->property_id}");

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.property_id', $property->property_id);

        $removedRelation = 'booking_' . 'requests';

        $this->assertArrayNotHasKey($removedRelation, $response->json('data'));
    }

    private function createProperty(array $overrides, array $location, float $nightlyRate): Property
    {
        $property = Property::create(array_merge([
            'user_id' => $this->user->id,
            'property_status' => 'active',
            'property_main_type' => 'rent',
            'property_type_id' => $this->villaType->property_type_id,
            'unit_type_id' => null,
            'property_category_id' => $this->category->property_category_id,
            'currency_id' => $this->currency->currency_id,
            'discount_id' => null,
            'addon_id' => null,
            'title' => 'Search Test Property',
            'description' => 'A property created for search filter coverage.',
            'stars' => '5 Stars',
            'available_from' => '2026-01-01',
            'no_bedrooms' => 3,
            'no_bathrooms' => 2,
            'no_floors' => 1,
            'no_guests' => 4,
            'min_no_of_stay' => 1,
            'parking' => true,
            'total_square_feet' => 1200,
            'allow_bookings' => true,
            'status' => 'active',
        ], $overrides));

        Location::create(array_merge([
            'property_id' => $property->property_id,
            'country' => 'Spain',
            'region' => 'Valencia',
            'city' => 'Valencia',
            'area' => 'City Center',
        ], $location));

        Rate::create([
            'property_id' => $property->property_id,
            'unit_type_id' => null,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'nightly_rate' => $nightlyRate,
            'nightly_rate_weekend' => null,
            'min_stay' => 1,
            'changeover_day' => null,
            'persons' => 1,
            'extra_person_price_night' => 0,
            'status' => 'active',
            'is_special_offer' => 'inactive',
            'is_rate_overwrite' => 'inactive',
        ]);

        return $property;
    }

    private function createBooking(Property $property, string $checkIn, string $checkOut): Booking
    {
        $bookingId = Booking::query()->insertGetId([
            'booking_code' => 'BK-' . $property->property_id . '-' . str_replace('-', '', $checkIn),
            'booking_source' => 'website',
            'property_id' => $property->property_id,
            'user_id' => null,
            'created_by' => null,
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '123456789',
            'guest_address' => null,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'number_adults' => 2,
            'number_children' => 0,
            'number_nights' => 1,
            'admin_base_price_per_night' => 100,
            'rental_price' => 100,
            'addon_total' => 0,
            'discount_amount' => 0,
            'coupon_discount' => 0,
            'subtotal' => 100,
            'grand_total' => 100,
            'addon_id' => null,
            'discount_id' => null,
            'payment_method' => 'manual_cash',
            'payment_status' => 'unpaid',
            'booking_status' => 'confirmed',
            'confirmation_email_sent' => false,
            'guest_message' => null,
            'internal_notes' => null,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Booking::findOrFail($bookingId);
    }
}
