<?php

namespace App\Livewire;

use App\Models\Property;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PropertyCatalog extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $city = '';

    #[Url(history: true)]
    public string $type = '';

    #[Url(history: true)]
    public ?int $maxPrice = null;

    public function updatingCity(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingMaxPrice(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('city', 'type', 'maxPrice');
        $this->resetPage();
    }

    public function render()
    {
        $properties = Property::with('images')
            ->where('status', 'vacant')
            ->when($this->city !== '', fn ($q) => $q->where('city', $this->city))
            ->when($this->type !== '', fn ($q) => $q->where('type', $this->type))
            ->when($this->maxPrice, fn ($q) => $q->where('monthly_rent', '<=', $this->maxPrice))
            ->orderBy('monthly_rent')
            ->paginate(9);

        $cities = Property::where('status', 'vacant')->whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        return view('livewire.property-catalog', compact('properties', 'cities'));
    }
}
