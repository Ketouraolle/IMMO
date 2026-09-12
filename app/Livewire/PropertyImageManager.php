<?php

namespace App\Livewire;

use App\Models\Property;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PropertyImageManager extends Component
{
    use WithFileUploads;

    public Property $property;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $newImages = [];

    public function mount(Property $property): void
    {
        $this->ensureAdmin();
        $this->property = $property;
    }

    protected function rules(): array
    {
        return [
            'newImages' => ['array', 'max:10'],
            'newImages.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function updatedNewImages(): void
    {
        $this->validateOnly('newImages');
    }

    public function removePending(int $index): void
    {
        unset($this->newImages[$index]);
        $this->newImages = array_values($this->newImages);
    }

    public function upload(): void
    {
        $this->ensureAdmin();
        $this->validate();

        if (empty($this->newImages)) {
            return;
        }

        $hasExisting = $this->property->images()->exists();

        foreach ($this->newImages as $i => $file) {
            $path = $file->store('properties', 'public');

            $this->property->images()->create([
                'path' => $path,
                'is_primary' => ! $hasExisting && $i === 0,
            ]);

            $hasExisting = true;
        }

        $this->reset('newImages');
        $this->dispatch('status', message: 'Photo(s) added.');
    }

    public function makePrimary(int $imageId): void
    {
        $this->ensureAdmin();

        $image = $this->property->images()->findOrFail($imageId);
        $this->property->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
    }

    public function remove(int $imageId): void
    {
        $this->ensureAdmin();

        $image = $this->property->images()->findOrFail($imageId);
        $wasPrimary = $image->is_primary;

        Storage::disk('public')->delete($image->path);
        $image->delete();

        if ($wasPrimary) {
            $next = $this->property->images()->oldest('id')->first();
            $next?->update(['is_primary' => true]);
        }

        $this->dispatch('status', message: 'Photo removed.');
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
    }

    public function render()
    {
        return view('livewire.property-image-manager', [
            'images' => $this->property->images()->get(),
        ]);
    }
}
