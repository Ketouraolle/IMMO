<?php

namespace App\Livewire;

use App\Models\Property;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PropertyCatalog extends Component
{
    use WithPagination;

    public const TYPES = ['apartment', 'house', 'studio', 'commercial'];

    public const SORTS = ['price_asc' => 'Lowest price', 'price_desc' => 'Highest price', 'newest' => 'Newest'];

    #[Url(history: true)]
    public string $city = '';

    #[Url(history: true)]
    public string $type = '';

    #[Url(history: true)]
    public ?int $maxPrice = null;

    #[Url(history: true, except: 'price_asc')]
    public string $sort = 'price_asc';

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

    public function updatingSort(): void
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
        // Vacant homes matching city and budget; type is applied separately so the chips can show counts
        $matching = fn () => Property::where('status', 'vacant')
            ->when($this->city !== '', fn ($q) => $q->where('city', $this->city))
            ->when($this->maxPrice, fn ($q) => $q->where('monthly_rent', '<=', $this->maxPrice));

        $properties = $matching()
            ->with('images')
            ->when($this->type !== '', fn ($q) => $q->where('type', $this->type))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderByDesc('monthly_rent'))
            ->when($this->sort === 'newest', fn ($q) => $q->latest())
            ->when(! in_array($this->sort, ['price_desc', 'newest'], true), fn ($q) => $q->orderBy('monthly_rent'))
            ->paginate(9);

        $typeCounts = $matching()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $cities = Property::where('status', 'vacant')->whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        return view('livewire.property-catalog', compact('properties', 'cities', 'typeCounts'));
    }
}
