<?php

namespace App\Livewire\Account;

use App\Models\Booking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Dashboard extends Component
{
    public const TABS = ['upcoming', 'past', 'cancelled'];

    #[Url(except: 'upcoming')]
    public string $tab = 'upcoming';

    public function mount(): void
    {
        if (! in_array($this->tab, self::TABS, true)) {
            $this->tab = 'upcoming';
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'upcoming';
    }

    /** @return Collection<int, Booking> */
    #[Computed]
    public function bookings(): Collection
    {
        return Booking::with('roomType')->where('user_id', Auth::id())->orderBy('check_in')->get();
    }

    #[Computed]
    public function groups(): array
    {
        $today = today();
        $all = $this->bookings;

        return [
            'upcoming' => $all->filter(fn (Booking $b) => in_array($b->status, ['pending', 'confirmed', 'checked_in'], true) && $b->check_out->gte($today))->values(),
            'past' => $all->filter(fn (Booking $b) => $b->status !== 'cancelled' && ! (in_array($b->status, ['pending', 'confirmed', 'checked_in'], true) && $b->check_out->gte($today)))->sortByDesc('check_in')->values(),
            'cancelled' => $all->where('status', 'cancelled')->sortByDesc('check_in')->values(),
        ];
    }

    #[Computed]
    public function stats(): array
    {
        $active = $this->bookings->whereNotIn('status', ['cancelled', 'no_show']);

        return [
            'stays' => $active->count(),
            'nights' => (int) $active->sum('nights'),
            'spent' => (float) $this->bookings->where('payment_status', '!=', 'refunded')->sum(fn ($b) => (float) $b->amount_paid),
        ];
    }

    public function render()
    {
        return view('livewire.account.dashboard', [
            'next' => $this->groups['upcoming']->first(),
        ])->title(__('account.dashboard.title'));
    }
}
