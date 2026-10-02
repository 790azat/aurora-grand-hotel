<?php

namespace App\Livewire\Booking;

use App\Models\Extra;
use App\Models\PromoCode;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Wizard extends Component
{
    public const MAX_ADULTS = 6;

    public const MAX_CHILDREN = 4;

    public const MAX_NIGHTS = 30;

    public const ARRIVAL_TIMES = ['12:00–14:00', '14:00–16:00', '16:00–18:00', '18:00–20:00', '20:00–22:00', '22:00–00:00', '00:00–06:00'];

    #[Url(except: '')]
    public string $check_in = '';

    #[Url(except: '')]
    public string $check_out = '';

    #[Url]
    public $adults = 2;

    #[Url(except: 0)]
    public $children = 0;

    #[Url(except: '')]
    public string $room_type = '';

    public int $step = 1;

    /** @var array<int|string, bool> extra_id => selected */
    public array $extras = [];

    public string $promo_input = '';

    /** Applied promo code; links like /booking?promo=WELCOME10 (offers page) prefill it. */
    #[Url(except: null)]
    public ?string $promo = null;

    // Guest details
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public string $country = '';

    public string $arrival_time = '';

    public string $special_requests = '';

    public bool $create_account = false;

    public string $password = '';

    public bool $terms = false;

    public string $payment_method = 'card';

    public ?string $error = null;

    public function mount(): void
    {
        $this->normalizeSearch(initial: true);

        if ($user = Auth::user()) {
            [$first, $last] = array_pad(explode(' ', trim((string) $user->name), 2), 2, '');
            $this->first_name = $first;
            $this->last_name = $last;
            $this->email = (string) $user->email;
            $this->phone = (string) $user->phone;
            $this->country = (string) $user->country;
        }

        // Apply a promo code from the URL if it is valid for the stay, otherwise prefill the field.
        if ($this->promo) {
            $code = Str::upper(trim($this->promo));
            $promo = PromoCode::whereRaw('upper(code) = ?', [$code])->first();
            if ($promo && $promo->isUsable($this->nights)) {
                $this->promo = $promo->code;
            } else {
                $this->promo = null;
                $this->promo_input = Str::limit($code, 30, '');
            }
        }

        // Drop a preselected room type that doesn't exist / doesn't fit / isn't bookable.
        if ($this->room_type && ! $this->results->firstWhere('type.slug', $this->room_type)) {
            $this->room_type = '';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Step 1 — dates, guests, room
    |--------------------------------------------------------------------------
    */

    public function updated(string $property): void
    {
        if (in_array($property, ['check_in', 'check_out', 'adults', 'children'], true)) {
            $this->error = null;
            $this->normalizeSearch(changed: $property);
            $this->resetComputed();

            // Keep the selection only if it still fits and is available.
            if ($this->room_type && ! $this->selectableResult($this->room_type)) {
                if ($this->step > 1) {
                    $this->step = 1;
                    $this->dispatch('notify', message: __('booking.selection_unavailable'), type: 'error');
                }
                if (! $this->results->firstWhere('type.slug', $this->room_type)) {
                    $this->room_type = '';
                }
            }

            $this->revalidatePromo();
        }

        if (str_starts_with($property, 'extras')) {
            unset($this->quote);
        }
    }

    public function changeGuests(string $field, int $delta): void
    {
        if (! in_array($field, ['adults', 'children'], true)) {
            return;
        }
        $this->{$field} = (int) $this->{$field} + $delta;
        $this->updated($field);
    }

    public function extendStay(int $nights): void
    {
        $this->check_out = CarbonImmutable::parse($this->check_in)->addDays(max(1, min($nights, self::MAX_NIGHTS)))->toDateString();
        $this->updated('check_out');
    }

    public function selectRoom(string $slug): void
    {
        $this->resetComputed();
        $result = $this->results->firstWhere('type.slug', $slug);
        if (! $result || $this->dateError) {
            return;
        }
        if ($result['available'] < 1) {
            $this->dispatch('notify', message: __('booking.sold_out_notice'), type: 'error');

            return;
        }
        if ($result['quote']['nights'] < $result['quote']['min_nights']) {
            $this->dispatch('notify', message: trans_choice('booking.min_nights_notice', $result['quote']['min_nights']), type: 'error');

            return;
        }

        $this->room_type = $slug;
        $this->error = null;
        $this->revalidatePromo();
        $this->step = 2;
        $this->dispatch('booking-step');
    }

    /*
    |--------------------------------------------------------------------------
    | Step 2 — extras & promo
    |--------------------------------------------------------------------------
    */

    public function toggleExtra(int $id): void
    {
        $this->extras[$id] = ! ($this->extras[$id] ?? false);
        $this->extras = array_filter($this->extras);
        unset($this->quote);
    }

    public function applyPromo(): void
    {
        $code = Str::upper(trim($this->promo_input));
        $this->resetErrorBag('promo_input');

        if ($code === '') {
            $this->addError('promo_input', __('booking.promo.empty'));

            return;
        }

        $nights = $this->nights;
        $promo = PromoCode::whereRaw('upper(code) = ?', [$code])->first();

        if (! $promo || ! $promo->is_active || ($promo->valid_until && $promo->valid_until->lt(now()->startOfDay())) || ($promo->valid_from && $promo->valid_from->gt(now()->startOfDay())) || ($promo->max_uses && $promo->used_count >= $promo->max_uses)) {
            $this->addError('promo_input', __('booking.promo.invalid'));

            return;
        }

        if ($promo->min_nights && $nights < $promo->min_nights) {
            $this->addError('promo_input', trans_choice('booking.promo.min_nights', $promo->min_nights, ['code' => $promo->code]));

            return;
        }

        $this->promo = $promo->code;
        $this->promo_input = '';
        unset($this->quote);
        $this->dispatch('notify', message: __('booking.promo.applied', ['code' => $promo->code]), type: 'success');
    }

    public function removePromo(): void
    {
        $this->promo = null;
        $this->resetErrorBag('promo_input');
        unset($this->quote);
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    public function goToStep(int $step): void
    {
        $step = max(1, min(4, $step));
        if ($step <= $this->maxStep()) {
            $this->step = $step;
            $this->dispatch('booking-step');
        }
    }

    public function continueToDetails(): void
    {
        if (! $this->selectedResult()) {
            $this->step = 1;

            return;
        }
        $this->step = 3;
        $this->dispatch('booking-step');
    }

    public function continueToReview(): void
    {
        if (! $this->selectedResult()) {
            $this->step = 1;

            return;
        }
        $this->validate($this->guestRules());
        $this->step = 4;
        $this->dispatch('booking-step');
    }

    protected function maxStep(): int
    {
        if (! $this->selectedResult()) {
            return 1;
        }

        return $this->step >= 3 ? 4 : 3;
    }

    /*
    |--------------------------------------------------------------------------
    | Step 4 — create the booking
    |--------------------------------------------------------------------------
    */

    public function book(BookingService $service)
    {
        $this->error = null;
        $this->resetComputed();

        $result = $this->selectedResult();
        if (! $result) {
            $this->step = 1;
            $this->error = __('booking.errors.sold_out_meanwhile');

            return null;
        }

        try {
            $this->validate($this->guestRules());
        } catch (ValidationException $e) {
            $this->step = 3;
            throw $e;
        }
        $this->validate(['payment_method' => ['required', Rule::in(['card', 'idram', 'on_arrival'])]]);

        $user = Auth::user();

        try {
            $booking = DB::transaction(function () use ($service, $result, &$user) {
                if (! $user && $this->create_account) {
                    $user = User::create([
                        'name' => trim($this->first_name.' '.$this->last_name),
                        'email' => Str::lower(trim($this->email)),
                        'password' => $this->password,
                        'phone' => $this->phone,
                        'country' => $this->country,
                        'locale' => app()->getLocale(),
                        'role' => 'guest',
                    ]);
                }

                return $service->create($result['type'], [
                    'check_in' => $this->check_in,
                    'check_out' => $this->check_out,
                    'adults' => (int) $this->adults,
                    'children' => (int) $this->children,
                    'first_name' => trim($this->first_name),
                    'last_name' => trim($this->last_name),
                    'email' => Str::lower(trim($this->email)),
                    'phone' => trim($this->phone),
                    'country' => $this->country,
                    'arrival_time' => $this->arrival_time ?: null,
                    'special_requests' => trim($this->special_requests) ?: null,
                    'payment_method' => $this->payment_method,
                    'source' => 'website',
                    'user_id' => $user?->id,
                ], $this->extras, $this->promo);
            });
        } catch (\RuntimeException $e) {
            report($e);
            $this->room_type = '';
            $this->step = 1;
            $this->resetComputed();
            $this->error = __('booking.errors.sold_out_meanwhile');
            $this->dispatch('booking-step');

            return null;
        }

        if ($user && ! Auth::check()) {
            Auth::login($user);
        }

        $service->rememberAccess($booking);

        return in_array($booking->payment_method, ['card', 'idram'], true)
            ? $this->redirectRoute('booking.pay', ['booking' => $booking, 'method' => $booking->payment_method])
            : $this->redirectRoute('booking.confirmation', $booking);
    }

    protected function guestRules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[\d\s()+\-.]{6,}$/'],
            'country' => ['required', Rule::in(array_keys(__('booking.countries')))],
            'arrival_time' => ['nullable', Rule::in(self::ARRIVAL_TIMES)],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'terms' => ['accepted'],
        ];

        if (! Auth::check() && $this->create_account) {
            $rules['email'][] = Rule::unique('users', 'email');
            $rules['password'] = ['required', 'string', 'min:8', 'max:100'];
        }

        return $rules;
    }

    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function dateError(): ?string
    {
        try {
            $in = CarbonImmutable::parse($this->check_in)->startOfDay();
            $out = CarbonImmutable::parse($this->check_out)->startOfDay();
        } catch (\Throwable) {
            return __('booking.errors.dates_invalid');
        }

        return match (true) {
            $in->lt(today()) => __('booking.errors.check_in_past'),
            $out->lte($in) => __('booking.errors.check_out_before'),
            $in->diffInDays($out) > self::MAX_NIGHTS => __('booking.errors.too_long', ['max' => self::MAX_NIGHTS]),
            default => null,
        };
    }

    #[Computed]
    public function nights(): int
    {
        if ($this->dateError) {
            return 0;
        }

        return (int) CarbonImmutable::parse($this->check_in)->diffInDays(CarbonImmutable::parse($this->check_out));
    }

    /** @return Collection<int, array{type: RoomType, available: int, quote: array}> */
    #[Computed]
    public function results(): Collection
    {
        if ($this->dateError) {
            return collect();
        }

        return app(BookingService::class)
            ->search($this->check_in, $this->check_out, (int) $this->adults, (int) $this->children)
            ->sortBy(fn ($r) => [$r['type']->slug === $this->room_type ? 0 : 1, $r['available'] > 0 ? 0 : 1, $r['type']->sort])
            ->values();
    }

    #[Computed]
    public function selectedType(): ?RoomType
    {
        return $this->room_type ? RoomType::where('slug', $this->room_type)->where('is_active', true)->first() : null;
    }

    #[Computed]
    public function quote(): ?array
    {
        if (! $this->selectedType || $this->dateError) {
            return null;
        }

        return app(BookingService::class)->quote(
            $this->selectedType, $this->check_in, $this->check_out,
            (int) $this->adults, (int) $this->children, $this->extras, $this->promo,
        );
    }

    #[Computed]
    public function extraOptions(): Collection
    {
        return Extra::where('is_active', true)->orderBy('sort')->orderBy('id')->get();
    }

    /** The selected room type's search result, if it is bookable for the current search. */
    protected function selectedResult(): ?array
    {
        return $this->room_type ? $this->selectableResult($this->room_type) : null;
    }

    protected function selectableResult(string $slug): ?array
    {
        $result = $this->results->firstWhere('type.slug', $slug);

        return $result && $result['available'] > 0 && $result['quote']['nights'] >= $result['quote']['min_nights'] ? $result : null;
    }

    protected function resetComputed(): void
    {
        unset($this->dateError, $this->nights, $this->results, $this->selectedType, $this->quote);
    }

    protected function revalidatePromo(): void
    {
        if (! $this->promo) {
            return;
        }
        $promo = PromoCode::where('code', $this->promo)->first();
        if (! $promo || ! $promo->isUsable($this->nights)) {
            $code = $this->promo;
            $this->promo = null;
            unset($this->quote);
            $this->dispatch('notify', message: __('booking.promo.removed_auto', ['code' => $code]), type: 'error');
        }
    }

    protected function normalizeSearch(bool $initial = false, ?string $changed = null): void
    {
        $this->adults = max(1, min(self::MAX_ADULTS, (int) $this->adults ?: 1));
        $this->children = max(0, min(self::MAX_CHILDREN, (int) $this->children));

        $today = CarbonImmutable::today();
        $in = $this->parseDate($this->check_in);
        $out = $this->parseDate($this->check_out);

        if ($initial) {
            if (! $in || $in->lt($today)) {
                $in = $today->addDays(7);
            }
            if (! $out || $out->lte($in)) {
                $out = $in->addDays(3);
            }
        } elseif ($changed === 'check_in' && $in && $out && $out->lte($in)) {
            // Keep a sensible stay when the check-in date moves past check-out.
            $out = $in->addDay();
        }

        if ($in) {
            $this->check_in = $in->toDateString();
        }
        if ($out) {
            $this->check_out = $out->toDateString();
        }
    }

    protected function parseDate(?string $value): ?CarbonImmutable
    {
        if (! $value || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.booking.wizard', [
            'countries' => __('booking.countries'),
            'arrivalTimes' => self::ARRIVAL_TIMES,
            'selected' => $this->selectedResult(),
        ])->title(__('booking.meta_title'))
            ->layoutData(['description' => __('booking.meta_description')]);
    }
}
