<?php

namespace Tests\Feature;

use App\Livewire\PropertyCatalog;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PropertyCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create(['role' => 'owner']);
        $homes = [
            ['Studio Akwa', 'studio', 'Douala', 90000, 'vacant'],
            ['Villa Bastos', 'house', 'Yaoundé', 400000, 'vacant'],
            ['Appartement Odza', 'apartment', 'Yaoundé', 180000, 'vacant'],
            ['Résidence Bonapriso', 'apartment', 'Douala', 250000, 'occupied'],
        ];

        foreach ($homes as [$name, $type, $city, $rent, $status]) {
            Property::create([
                'owner_id' => $owner->id, 'name' => $name, 'address' => '1 Rue Test', 'city' => $city,
                'type' => $type, 'monthly_rent' => $rent, 'status' => $status,
            ]);
        }
    }

    public function test_landing_page_lists_vacant_homes_cheapest_first(): void
    {
        $this->get(route('public.properties.index'))
            ->assertOk()
            ->assertSee('3 homes available now')
            ->assertSeeInOrder(['Studio Akwa', 'Appartement Odza', 'Villa Bastos'])
            ->assertDontSee('Résidence Bonapriso')
            ->assertSee('How it works');
    }

    public function test_type_chip_filters_and_counts_follow_other_filters(): void
    {
        Livewire::test(PropertyCatalog::class)
            ->set('type', 'house')
            ->assertSee('Villa Bastos')
            ->assertDontSee('Studio Akwa')
            ->set('type', '')
            ->set('city', 'Yaoundé')
            ->assertViewHas('typeCounts', fn ($counts) => $counts->sum() === 2 && ! isset($counts['studio']));
    }

    public function test_sort_by_highest_price_and_newest(): void
    {
        Livewire::test(PropertyCatalog::class)
            ->set('sort', 'price_desc')
            ->assertSeeInOrder(['Villa Bastos', 'Appartement Odza', 'Studio Akwa']);

        Property::where('name', 'Studio Akwa')->update(['created_at' => now()->addMinute()]);

        Livewire::test(PropertyCatalog::class)
            ->set('sort', 'newest')
            ->assertSeeInOrder(['Studio Akwa', 'Villa Bastos']);
    }
}
