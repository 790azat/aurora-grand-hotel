<?php

namespace App\Livewire\Rooms;

use App\Models\RoomType;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(except: '')]
    public string $check_in = '';

    #[Url(except: '')]
    public string $check_out = '';

    #[Url(except: 2)]
    public int $adults = 2;

    #[Url(except: 0)]
    public int $children = 0;

    #[Url(except: 0)]
    public int $max_price = 0; // 0 = any

    #[Url(except: 'recommended')]
    public string $sort = 'recommended';

    public const SORTS = ['recommended', 'price_asc', 'price_desc', 'size_desc', 'popular'];

    public const PRICE_STEPS = [0, 250, 400, 800, 1500];

    public function mount(): void
    {
        $this->sanitize();
    }

    public function updated(): void
    {
        $this->sanitize();
    }

    protected function sanitize(): void
    {
        $this->adults = max(1, min(6, $this->adults));
        $this->children = max(0, min(4, $this->children));
        if (! in_array($this->sort, self::SORTS, true)) {
            $this->sort = 'recommended';
        }
        if (! in_array($this->max_price, self::PRICE_STEPS, true)) {
            $this->max_price = 0;
        }
        foreach (['check_in', 'check_out'] as $field) {
            if ($this->{$field} !== '' && ! $this->validDate($this->{$field})) {
                $this->{$field} = '';
            }
        }
        if ($this->check_in !== '' && $this->check_in < today()->toDateString()) {
            $this->check_in = today()->toDateString();
        }
        if ($this->check_in !== '' && ($this->check_out === '' || $this->check_out <= $this->check_in)) {
            $this->check_out = Carbon::parse($this->check_in)->addDay()->toDateString();
        }
    }

    protected function validDate(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) !== false;
    }

    public function clearDates(): void
    {
        $this->check_in = '';
        $this->check_out = '';
    }

    public function resetFilters(): void
    {
        $this->reset('check_in', 'check_out', 'adults', 'children', 'max_price', 'sort');
    }

    public function hasDates(): bool
    {
        return $this->check_in !== '' && $this->check_out !== '' && $this->check_out > $this->check_in;
    }

    public function render(BookingService $booking)
    {
        $dates = $this->hasDates();

        if ($dates) {
            $results = $booking->search($this->check_in, $this->check_out, $this->adults, $this->children);
        } else {
            $results = RoomType::active()->with('amenities')->get()
                ->filter(fn (RoomType $t) => $t->max_adults >= $this->adults && $t->max_guests >= $this->adults + $this->children)
                ->map(fn (RoomType $t) => ['type' => $t, 'available' => null, 'quote' => null])
                ->values();
        }

        $popularity = RoomType::withCount('bookings')->pluck('bookings_count', 'id');
        $nightly = fn (array $r) => (float) ($r['quote']['avg_nightly'] ?? $r['type']->base_price);

        $results = $results
            ->when($this->max_price > 0, fn (Collection $c) => $c->filter(fn ($r) => $nightly($r) <= $this->max_price))
            ->sortBy(match ($this->sort) {
                'price_asc' => [fn ($a, $b) => $nightly($a) <=> $nightly($b)],
                'price_desc' => [fn ($a, $b) => $nightly($b) <=> $nightly($a)],
                'size_desc' => [fn ($a, $b) => $b['type']->size_m2 <=> $a['type']->size_m2],
                'popular' => [fn ($a, $b) => ($popularity[$b['type']->id] ?? 0) <=> ($popularity[$a['type']->id] ?? 0)],
                default => [fn ($a, $b) => $a['type']->sort <=> $b['type']->sort],
            })
            ->values();

        if ($dates) {
            // Available room types first.
            $results = $results->sortBy(fn ($r) => $r['available'] > 0 ? 0 : 1)->values();
        }

        $params = $dates ? [
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'adults' => $this->adults,
            'children' => $this->children,
        ] : array_filter(['adults' => $this->adults !== 2 ? $this->adults : null, 'children' => $this->children ?: null]);

        return view('livewire.rooms.index', [
            'results' => $results,
            'hasDates' => $dates,
            'params' => $params,
            'nights' => $dates ? Carbon::parse($this->check_in)->diffInDays(Carbon::parse($this->check_out)) : 0,
            'availableTypes' => $dates ? $results->where('available', '>', 0)->count() : null,
            'availableRooms' => $dates ? $results->sum('available') : null,
        ])->title(__('site.rooms.meta_title'))
            ->layoutData(['description' => __('site.rooms.meta_description')]);
    }
}
