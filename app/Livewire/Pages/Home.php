<?php

namespace App\Livewire\Pages;

use App\Models\Facility;
use App\Models\GalleryImage;
use App\Models\Offer;
use App\Models\Post;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use Livewire\Component;

class Home extends Component
{
    public const HERO_IMAGE = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=2000&q=80';

    public function render()
    {
        $reviews = Review::approved();
        $rating = round((float) (clone $reviews)->avg('rating'), 1);
        $reviewCount = (clone $reviews)->count();

        $featured = RoomType::active()->where('is_featured', true)->with('amenities')->take(3)->get();
        if ($featured->count() < 3) {
            $featured = RoomType::active()->with('amenities')->take(3)->get();
        }

        return view('livewire.pages.home', [
            'featured' => $featured,
            'roomTypeCount' => RoomType::active()->count(),
            'minPrice' => RoomType::active()->min('base_price'),
            'stats' => [
                'rooms' => Room::count(),
                'beach' => 300,
                'years' => (int) now()->year - 1998,
                'rating' => $rating ?: 4.9,
            ],
            'facilities' => Facility::where('is_active', true)->orderBy('sort')->take(6)->get(),
            'offers' => Offer::where('is_active', true)->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', today()))->orderBy('sort')->take(3)->get(),
            'reviews' => Review::approved()->with('roomType')->take(6)->get(),
            'rating' => $rating,
            'reviewCount' => $reviewCount,
            'gallery' => GalleryImage::orderBy('sort')->take(5)->get(),
            'posts' => Post::published()->take(3)->get(),
        ])->title(__('site.home.meta_title'))
            ->layoutData([
                'description' => __('site.meta_description'),
                'ogImage' => self::HERO_IMAGE,
                'schema' => $this->schema($rating, $reviewCount),
            ]);
    }

    /** JSON-LD Hotel structured data. */
    protected function schema(float $rating, int $reviewCount): string
    {
        $prices = RoomType::active()->pluck('base_price');
        $symbol = Setting::get('currency_symbol', '$');

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Hotel',
            'name' => Setting::get('hotel_name'),
            'description' => __('site.meta_description'),
            'url' => route('home'),
            'image' => self::HERO_IMAGE,
            'telephone' => Setting::get('hotel_phone'),
            'email' => Setting::get('hotel_email'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => Setting::localized('hotel_address'),
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) Setting::get('map_lat'),
                'longitude' => (float) Setting::get('map_lng'),
            ],
            'starRating' => ['@type' => 'Rating', 'ratingValue' => (int) Setting::get('hotel_stars', 5)],
            'priceRange' => $symbol.number_format((float) $prices->min()).' – '.$symbol.number_format((float) $prices->max()),
            'checkinTime' => Setting::get('check_in_time'),
            'checkoutTime' => Setting::get('check_out_time'),
            'currenciesAccepted' => Setting::get('currency', 'USD'),
        ];

        if ($reviewCount > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $rating,
                'reviewCount' => $reviewCount,
                'bestRating' => 5,
            ];
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    }
}
