<?php

namespace Tests\Feature;

use App\Livewire\PropertyImageManager;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PropertyImageManagerTest extends TestCase
{
    use RefreshDatabase;

    private function property(): Property
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);

        return Property::create([
            'owner_id' => $owner->id,
            'name' => 'Villa Test',
            'address' => '1 Rue Test',
            'city' => 'Douala',
            'type' => 'house',
            'monthly_rent' => 150000,
            'status' => 'vacant',
        ]);
    }

    public function test_admin_can_upload_images_and_first_becomes_primary(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $property = $this->property();

        Livewire::actingAs($admin)
            ->test(PropertyImageManager::class, ['property' => $property])
            ->set('newImages', [UploadedFile::fake()->image('photo1.jpg')])
            ->call('upload')
            ->assertHasNoErrors();

        $this->assertCount(1, $property->images()->get());
        $this->assertTrue($property->images()->first()->is_primary);
        Storage::disk('public')->assertExists($property->images()->first()->path);
    }

    public function test_make_primary_and_remove_work(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $property = $this->property();

        $component = Livewire::actingAs($admin)
            ->test(PropertyImageManager::class, ['property' => $property])
            ->set('newImages', [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
            ])
            ->call('upload');

        $images = $property->images()->get();
        $this->assertCount(2, $images);
        $first = $images->first();
        $second = $images->last();
        $this->assertTrue($first->is_primary);

        $component->call('makePrimary', $second->id);
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertFalse($first->fresh()->is_primary);

        $path = $second->path;
        $component->call('remove', $second->id);
        $this->assertModelMissing($second);
        Storage::disk('public')->assertMissing($path);
        // the remaining image becomes primary again
        $this->assertTrue($first->fresh()->is_primary);
    }

    public function test_non_admin_cannot_manage_images(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant', 'is_active' => true]);
        $property = $this->property();

        Livewire::actingAs($tenant)
            ->test(PropertyImageManager::class, ['property' => $property])
            ->assertForbidden();
    }
}
