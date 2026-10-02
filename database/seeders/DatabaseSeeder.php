<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Extra;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\GalleryImage;
use App\Models\Offer;
use App\Models\Post;
use App\Models\PromoCode;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Season;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class DatabaseSeeder extends Seeder
{
    public static function img(string $id, int $w = 1600): string
    {
        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$w}&q=80";
    }

    public function run(): void
    {
        // Seeding confirms bookings; don't render/send confirmation emails for them.
        Mail::fake();
        DB::transaction(fn () => $this->seed());
        Setting::put('demo_date', now()->toDateString());
    }

    protected function seed(): void
    {
        mt_srand(2026);
        $img = fn ($id, $w = 1600) => self::img($id, $w);

        // --- Users -------------------------------------------------------
        $password = Hash::make('password');
        User::create(['name' => 'Admin Demo', 'email' => 'admin@demo.com', 'password' => $password, 'role' => 'admin', 'email_verified_at' => now()]);
        User::create(['name' => 'Maria Manager', 'email' => 'manager@demo.com', 'password' => $password, 'role' => 'manager', 'email_verified_at' => now()]);
        User::create(['name' => 'Rick Reception', 'email' => 'reception@demo.com', 'password' => $password, 'role' => 'reception', 'email_verified_at' => now()]);
        $guest = User::create(['name' => 'Guest Demo', 'email' => 'guest@demo.com', 'password' => $password, 'role' => 'guest', 'phone' => '+1 555 0100', 'country' => 'United States', 'email_verified_at' => now()]);

        // --- Amenities ---------------------------------------------------
        $amenities = collect([
            ['Free Wi‑Fi', 'Бесплатный Wi‑Fi', 'wifi'],
            ['Air conditioning', 'Кондиционер', 'sun'],
            ['Smart TV', 'Smart TV', 'tv'],
            ['Minibar', 'Мини-бар', 'beaker'],
            ['Coffee machine', 'Кофемашина', 'fire'],
            ['Rain shower', 'Тропический душ', 'sparkles'],
            ['Bathtub', 'Ванна', 'sparkles'],
            ['Safe', 'Сейф', 'lock-closed'],
            ['Balcony', 'Балкон', 'home'],
            ['Sea view', 'Вид на море', 'eye'],
            ['Bathrobes & slippers', 'Халаты и тапочки', 'gift'],
            ['Work desk', 'Рабочий стол', 'computer-desktop'],
            ['Private pool', 'Личный бассейн', 'sparkles'],
            ['Butler service', 'Услуги дворецкого', 'user'],
            ['Kitchenette', 'Мини-кухня', 'cake'],
            ['Room service 24/7', 'Обслуживание 24/7', 'clock'],
        ])->map(fn ($a, $i) => Amenity::create(['name_en' => $a[0], 'name_ru' => $a[1], 'icon' => $a[2], 'sort' => $i]));
        $a = fn (...$idx) => $amenities->only($idx)->pluck('id');

        // --- Room types & rooms -----------------------------------------
        $types = [
            [
                'slug' => 'classic-room', 'name_en' => 'Classic Room', 'name_ru' => 'Классический номер',
                'short_en' => 'Elegant comfort with garden views, perfect for couples.',
                'short_ru' => 'Элегантный комфорт с видом на сад, идеально для пары.',
                'description_en' => "Our Classic Rooms blend warm natural textures with refined design. Unwind on a king-size bed dressed in Egyptian cotton, enjoy a freshly brewed espresso and wake up to the sound of the garden.\n\nEvery room features a marble bathroom with rain shower, premium toiletries and plush bathrobes.",
                'description_ru' => "Классические номера сочетают тёплые природные фактуры и изысканный дизайн. Отдыхайте на кровати king-size с бельём из египетского хлопка, наслаждайтесь свежим эспрессо и просыпайтесь под звуки сада.\n\nВ каждом номере мраморная ванная комната с тропическим душем, премиальная косметика и мягкие халаты.",
                'base_price' => 180, 'weekend_price' => 210, 'max_adults' => 2, 'max_children' => 1, 'size_m2' => 32,
                'beds_en' => '1 King bed or 2 Twin beds', 'beds_ru' => '1 кровать king-size или 2 односпальные',
                'view_en' => 'Garden view', 'view_ru' => 'Вид на сад',
                'images' => [$img('1611892440504-42a792e24d32'), $img('1590490360182-c33d57733427'), $img('1584132967334-10e028bd69f7')],
                'amenities' => $a(0, 1, 2, 3, 4, 5, 7, 10, 11), 'rooms' => 12, 'floors' => [1, 2], 'featured' => false,
            ],
            [
                'slug' => 'deluxe-sea-view', 'name_en' => 'Deluxe Sea View', 'name_ru' => 'Делюкс с видом на море',
                'short_en' => 'Spacious room with a private balcony overlooking the bay.',
                'short_ru' => 'Просторный номер с балконом и видом на бухту.',
                'description_en' => "Wake up to panoramic views of Azure Bay from your private balcony. The Deluxe Sea View room offers generous space, a lounge corner and floor-to-ceiling windows that flood the room with Mediterranean light.\n\nIdeal for couples and small families who want the sea always in sight.",
                'description_ru' => "Просыпайтесь с панорамным видом на Лазурную бухту с собственного балкона. Номер Делюкс предлагает много пространства, лаунж-зону и панорамные окна, наполняющие комнату светом.\n\nИдеально для пар и небольших семей, которые хотят видеть море всегда.",
                'base_price' => 260, 'weekend_price' => 300, 'max_adults' => 3, 'max_children' => 1, 'size_m2' => 42,
                'beds_en' => '1 King bed + sofa bed', 'beds_ru' => '1 кровать king-size + диван-кровать',
                'view_en' => 'Sea view', 'view_ru' => 'Вид на море',
                'images' => [$img('1582719478250-c89cae4dc85b'), $img('1631049307264-da0ec9d70304'), $img('1596394516093-501ba68a0ba6')],
                'amenities' => $a(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10), 'rooms' => 12, 'floors' => [3, 4], 'featured' => true,
            ],
            [
                'slug' => 'family-suite', 'name_en' => 'Family Suite', 'name_ru' => 'Семейный люкс',
                'short_en' => 'Two bedrooms and a living room for the whole family.',
                'short_ru' => 'Две спальни и гостиная для всей семьи.',
                'description_en' => "Designed for families, this two-bedroom suite features a separate kids' room, a spacious living area and a kitchenette. Children receive a welcome gift and access to the Aurora Kids Club.\n\nConnecting balconies offer sweeping views of the gardens and pools.",
                'description_ru' => "Созданный для семей люкс с двумя спальнями: отдельная детская, просторная гостиная и мини-кухня. Дети получают приветственный подарок и доступ в детский клуб Aurora.\n\nС балконов открывается вид на сады и бассейны.",
                'base_price' => 390, 'weekend_price' => 440, 'max_adults' => 4, 'max_children' => 2, 'size_m2' => 75,
                'beds_en' => '1 King bed + 2 Single beds', 'beds_ru' => '1 кровать king-size + 2 односпальные',
                'view_en' => 'Pool & garden view', 'view_ru' => 'Вид на бассейн и сад',
                'images' => [$img('1578683010236-d716f9a3f461'), $img('1595576508898-0ad5c879a061'), $img('1505693416388-ac5ce068fe85')],
                'amenities' => $a(0, 1, 2, 3, 4, 5, 6, 7, 8, 10, 14), 'rooms' => 6, 'floors' => [2, 3], 'featured' => true,
            ],
            [
                'slug' => 'junior-suite', 'name_en' => 'Junior Suite', 'name_ru' => 'Джуниор-сюит',
                'short_en' => 'Open-plan suite with a freestanding bathtub and sea views.',
                'short_ru' => 'Сюит открытой планировки с отдельно стоящей ванной и видом на море.',
                'description_en' => 'A romantic open-plan suite with a freestanding bathtub facing the sea, a lounge area and a curated minibar. Evening turndown service and a bottle of sparkling wine on arrival are included.',
                'description_ru' => 'Романтичный сюит открытой планировки с отдельно стоящей ванной у окна с видом на море, лаунж-зоной и авторским мини-баром. Включены вечерняя подготовка номера и бутылка игристого при заезде.',
                'base_price' => 340, 'weekend_price' => 390, 'max_adults' => 2, 'max_children' => 1, 'size_m2' => 55,
                'beds_en' => '1 Emperor bed', 'beds_ru' => '1 кровать emperor-size',
                'view_en' => 'Panoramic sea view', 'view_ru' => 'Панорамный вид на море',
                'images' => [$img('1618773928121-c32242e63f39'), $img('1591088398332-8a7791972843'), $img('1600011689032-8b628b8a8747')],
                'amenities' => $a(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15), 'rooms' => 6, 'floors' => [4, 5], 'featured' => true,
            ],
            [
                'slug' => 'presidential-suite', 'name_en' => 'Presidential Suite', 'name_ru' => 'Президентский люкс',
                'short_en' => 'The crown jewel: 180 m² penthouse with a private pool.',
                'short_ru' => 'Жемчужина отеля: пентхаус 180 м² с личным бассейном.',
                'description_en' => 'Occupying the entire top floor, the Presidential Suite offers a private infinity pool, a terrace with 270° views, a dining room for eight and 24-hour butler service. Airport transfer by limousine is included.',
                'description_ru' => 'Президентский люкс занимает весь верхний этаж: личный инфинити-бассейн, терраса с обзором 270°, столовая на восемь персон и круглосуточный дворецкий. Трансфер из аэропорта на лимузине включён.',
                'base_price' => 1450, 'weekend_price' => 1650, 'max_adults' => 4, 'max_children' => 2, 'size_m2' => 180,
                'beds_en' => '2 King bedrooms', 'beds_ru' => '2 спальни с кроватями king-size',
                'view_en' => '270° sea panorama', 'view_ru' => 'Панорама моря 270°',
                'images' => [$img('1566665797739-1674de7a421a'), $img('1564078516393-cf04bd966897'), $img('1571896349842-33c89424de2d')],
                'amenities' => $a(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15), 'rooms' => 2, 'floors' => [6], 'featured' => true,
            ],
            [
                'slug' => 'beach-villa', 'name_en' => 'Beach Villa', 'name_ru' => 'Пляжная вилла',
                'short_en' => 'Private villa steps from the sand with its own plunge pool.',
                'short_ru' => 'Частная вилла в шаге от пляжа с собственным бассейном.',
                'description_en' => 'Hidden among palm trees, each Beach Villa has a private garden, plunge pool, outdoor rain shower and direct beach access. Breakfast is served on your terrace every morning.',
                'description_ru' => 'Спрятанная среди пальм, каждая вилла имеет частный сад, бассейн, уличный тропический душ и прямой выход на пляж. Завтрак каждое утро подают на вашу террасу.',
                'base_price' => 780, 'weekend_price' => 880, 'max_adults' => 4, 'max_children' => 2, 'size_m2' => 120,
                'beds_en' => '2 King bedrooms', 'beds_ru' => '2 спальни king-size',
                'view_en' => 'Beachfront', 'view_ru' => 'Первая линия пляжа',
                'images' => [$img('1520250497591-112f2f40a3f4'), $img('1540541338287-41700207dee6'), $img('1439066615861-d1af74d74000')],
                'amenities' => $a(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 14, 15), 'rooms' => 4, 'floors' => [1], 'featured' => false,
            ],
        ];

        $roomNo = [];
        foreach ($types as $i => $t) {
            $type = RoomType::create(collect($t)->except(['amenities', 'rooms', 'floors', 'featured'])->merge([
                'is_featured' => $t['featured'], 'sort' => $i, 'min_nights' => $t['slug'] === 'presidential-suite' ? 2 : 1,
            ])->all());
            $type->amenities()->sync($t['amenities']);
            for ($n = 0; $n < $t['rooms']; $n++) {
                $floor = $t['floors'][$n % count($t['floors'])];
                $roomNo[$floor] = ($roomNo[$floor] ?? 0) + 1;
                $number = $t['slug'] === 'beach-villa' ? 'V'.($n + 1) : $floor.str_pad($roomNo[$floor], 2, '0', STR_PAD_LEFT);
                Room::create(['room_type_id' => $type->id, 'number' => $number, 'floor' => $floor, 'status' => 'available']);
            }
        }
        Room::where('number', '207')->update(['status' => 'maintenance', 'notes' => 'Bathroom renovation']);

        // --- Pricing rules ----------------------------------------------
        $y = now()->year;
        Season::create(['name' => 'Summer high season', 'starts_on' => "$y-06-15", 'ends_on' => "$y-08-31", 'price_modifier' => 25, 'min_nights' => 2]);
        Season::create(['name' => 'New Year holidays', 'starts_on' => "$y-12-27", 'ends_on' => ($y + 1).'-01-08', 'price_modifier' => 40, 'min_nights' => 3]);
        Season::create(['name' => 'Low season', 'starts_on' => ($y + 1).'-01-09', 'ends_on' => ($y + 1).'-03-15', 'price_modifier' => -20]);
        Season::create(['name' => 'Autumn offer', 'starts_on' => "$y-10-15", 'ends_on' => "$y-11-30", 'price_modifier' => -10]);

        $extras = collect([
            ['Breakfast buffet', 'Завтрак «шведский стол»', 'Rich buffet with live cooking station', 'Богатый шведский стол с живой кухней', 25, 'per_guest_night', 'cake'],
            ['Airport transfer', 'Трансфер из аэропорта', 'Mercedes E-class, one way', 'Mercedes E-класса, в одну сторону', 60, 'per_stay', 'truck'],
            ['Late check-out (16:00)', 'Поздний выезд (16:00)', 'Enjoy your room until 4 PM', 'Номер в вашем распоряжении до 16:00', 50, 'per_stay', 'clock'],
            ['Spa ritual for two', 'СПА-ритуал для двоих', '90-minute signature massage', '90-минутный фирменный массаж', 220, 'per_stay', 'sparkles'],
            ['Romantic package', 'Романтический пакет', 'Champagne, roses and chocolates in room', 'Шампанское, розы и шоколад в номере', 120, 'per_stay', 'heart'],
            ['Parking', 'Парковка', 'Secure underground parking', 'Охраняемая подземная парковка', 15, 'per_night', 'map-pin'],
            ['Baby cot', 'Детская кроватка', 'Cot with linen', 'Кроватка с бельём', 0, 'per_stay', 'gift'],
        ])->map(fn ($e, $i) => Extra::create([
            'name_en' => $e[0], 'name_ru' => $e[1], 'description_en' => $e[2], 'description_ru' => $e[3],
            'price' => $e[4], 'pricing' => $e[5], 'icon' => $e[6], 'sort' => $i,
        ]));

        PromoCode::create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);
        PromoCode::create(['code' => 'AURORA20', 'type' => 'percent', 'value' => 20, 'min_nights' => 3, 'valid_until' => now()->addMonths(6)]);
        PromoCode::create(['code' => 'SPRING50', 'type' => 'fixed', 'value' => 50, 'max_uses' => 100, 'used_count' => 37]);
        PromoCode::create(['code' => 'SUMMER2025', 'type' => 'percent', 'value' => 15, 'valid_until' => now()->subMonths(3), 'is_active' => false]);

        // --- Bookings (past 12 months + next 3 months) -------------------
        $service = app(BookingService::class);
        $first = ['James', 'Olivia', 'Liam', 'Emma', 'Noah', 'Sophia', 'Lucas', 'Mia', 'Alexander', 'Anna', 'Dmitry', 'Elena', 'Ivan', 'Maria', 'Hans', 'Clara', 'Marco', 'Giulia', 'Pierre', 'Chloé', 'Yuki', 'Ahmed', 'Sara', 'Daniel'];
        $last = ['Smith', 'Johnson', 'Brown', 'Ivanov', 'Petrova', 'Müller', 'Rossi', 'Dubois', 'Tanaka', 'Garcia', 'Wilson', 'Novak', 'Kowalski', 'Andersen', 'Silva', 'Kim'];
        $countries = ['United States', 'United Kingdom', 'Germany', 'France', 'Italy', 'Kazakhstan', 'Russia', 'UAE', 'Turkey', 'Japan', 'Spain', 'Netherlands'];
        $sources = ['website', 'website', 'website', 'booking_com', 'booking_com', 'expedia', 'phone', 'walk_in'];
        $roomTypes = RoomType::all();
        $today = CarbonImmutable::today();

        for ($i = 0; $i < 1300; $i++) {
            $type = $roomTypes[mt_rand(0, 9) < 7 ? mt_rand(0, 3) : mt_rand(0, $roomTypes->count() - 1)];
            $checkIn = $today->addDays(mt_rand(-360, 90));
            $nights = mt_rand(1, 7);
            $checkOut = $checkIn->addDays($nights);
            if ($service->freeRooms($type, $checkIn, $checkOut)->isEmpty()) {
                continue;
            }
            $fn = $first[array_rand($first)];
            $ln = $last[array_rand($last)];
            $past = $checkOut->lt($today);
            $current = $checkIn->lte($today) && $checkOut->gte($today);
            $status = match (true) {
                $past => mt_rand(1, 100) <= 8 ? 'cancelled' : (mt_rand(1, 100) <= 3 ? 'no_show' : 'checked_out'),
                $current => $checkIn->eq($today) ? 'confirmed' : 'checked_in',
                default => mt_rand(1, 100) <= 85 ? 'confirmed' : (mt_rand(0, 1) ? 'pending' : 'cancelled'),
            };
            $extraIds = $extras->random(mt_rand(0, 3))->pluck('id')->mapWithKeys(fn ($id) => [$id => 1])->all();
            $method = mt_rand(1, 100) <= 55 ? 'card' : (mt_rand(1, 100) <= 45 ? 'idram' : 'on_arrival');
            $created = $checkIn->subDays(mt_rand(3, 60))->setTime(mt_rand(7, 22), mt_rand(0, 59));

            $booking = $service->create($type, [
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'adults' => mt_rand(1, $type->max_adults),
                'children' => mt_rand(0, $type->max_children),
                'first_name' => $fn, 'last_name' => $ln,
                'email' => strtolower($fn.'.'.$ln).mt_rand(1, 99).'@mail.example',
                'phone' => '+1 555 '.mt_rand(1000, 9999),
                'country' => $countries[array_rand($countries)],
                'payment_method' => $method,
                'source' => $sources[array_rand($sources)],
                'status' => 'pending',
                'locale' => mt_rand(0, 1) ? 'en' : 'ru',
            ], $extraIds, mt_rand(1, 100) <= 12 ? 'WELCOME10' : null);

            $paid = in_array($status, ['checked_out', 'checked_in'], true) || ($method !== 'on_arrival' && $status === 'confirmed');
            $booking->forceFill([
                'status' => $status,
                'payment_status' => $status === 'cancelled' ? ($method !== 'on_arrival' ? 'refunded' : 'unpaid') : ($paid ? 'paid' : 'unpaid'),
                'amount_paid' => $paid || ($status === 'cancelled' && $method !== 'on_arrival') ? $booking->total : 0,
                'confirmed_at' => $status !== 'pending' ? $created->addMinutes(5) : null,
                'cancelled_at' => $status === 'cancelled' ? $created->addDays(2) : null,
                'checked_in_at' => in_array($status, ['checked_in', 'checked_out'], true) ? $checkIn->setTime(15, 10) : null,
                'checked_out_at' => $status === 'checked_out' ? $checkOut->setTime(11, 30) : null,
                'created_at' => $created,
                'updated_at' => $created,
            ])->save();
            if ($status === 'cancelled') {
                $booking->update(['room_id' => null]);
            }
            if ($paid) {
                $booking->payments()->create([
                    'amount' => $booking->total, 'method' => $method === 'on_arrival' ? 'cash' : $method, 'status' => 'succeeded',
                    'transaction_id' => ($method === 'idram' ? 'idram_' : 'demo_').substr(md5((string) $booking->id), 0, 14),
                    'card_brand' => match ($method) {
                        'card' => 'Visa', 'idram' => 'Idram', default => null
                    },
                    'card_last4' => $method === 'on_arrival' ? null : (string) mt_rand(1000, 9999),
                    'created_at' => $created->addMinutes(5), 'updated_at' => $created->addMinutes(5),
                ]);
            }
        }
        Booking::query()->where('status', 'cancelled')->update(['room_id' => null]);

        // Demo guest's own bookings: one past (reviewable), one upcoming.
        foreach ([[-40, 4, 'checked_out', 1], [21, 3, 'confirmed', 3]] as [$offset, $nights, $status, $typeIdx]) {
            $type = $roomTypes[$typeIdx];
            $in = $today->addDays($offset);
            while ($service->freeRooms($type, $in, $in->addDays($nights))->isEmpty()) {
                $in = $in->addDay();
            }
            $b = $service->create($type, [
                'check_in' => $in->toDateString(), 'check_out' => $in->addDays($nights)->toDateString(),
                'adults' => 2, 'children' => 0, 'first_name' => 'Guest', 'last_name' => 'Demo', 'email' => $guest->email,
                'phone' => $guest->phone, 'country' => $guest->country, 'user_id' => $guest->id, 'payment_method' => 'card',
            ], [$extras[0]->id => 1]);
            $b->forceFill(['status' => $status, 'payment_status' => 'paid', 'amount_paid' => $b->total, 'confirmed_at' => $in->subDays(30), 'created_at' => $in->subDays(30),
                'checked_out_at' => $status === 'checked_out' ? $in->addDays($nights) : null])->save();
            $b->payments()->create(['amount' => $b->total, 'method' => 'card', 'status' => 'succeeded', 'transaction_id' => 'demo_guest'.$b->id, 'card_brand' => 'Visa', 'card_last4' => '4242']);
        }

        // --- Reviews ----------------------------------------------------
        $reviews = [
            ['Sophie L.', 'France', 5, 'Absolutely magical stay', 'The sea view from our balcony was breathtaking and the staff remembered our names from day one. Breakfast was the best we had in years.', 'en'],
            ['Анна К.', 'Россия', 5, 'Лучший отдых за много лет', 'Невероятный сервис, очень чисто, СПА просто сказка. Обязательно вернёмся всей семьёй следующим летом!', 'ru'],
            ['Mark T.', 'United Kingdom', 5, 'Perfect honeymoon', 'They surprised us with champagne and rose petals. The Junior Suite bathtub with sea view is unforgettable.', 'en'],
            ['Дмитрий П.', 'Казахстан', 4, 'Отличный отель для семьи', 'Детский клуб выше всяких похвал, дети не хотели уезжать. Немного шумно у бассейна днём, но это мелочи.', 'ru'],
            ['Giulia R.', 'Italy', 5, 'Exceptional restaurant', 'Chef\'s tasting menu at Azure was a highlight. Friendly team and spotless rooms.', 'en'],
            ['Hans M.', 'Germany', 4, 'Great location', 'Beautiful beach, comfortable beds and very fast check-in. Parking is a bit pricey.', 'en'],
            ['Елена В.', 'Россия', 5, 'Идеально во всём', 'Встретили на трансфере, заселили раньше времени, номер с видом на море. Персонал говорит по-русски.', 'ru'],
            ['Yuki T.', 'Japan', 5, 'Calm and luxurious', 'Quiet, elegant and attentive. The spa ritual for two was worth every penny.', 'en'],
        ];
        foreach ($reviews as $i => $r) {
            Review::create(['name' => $r[0], 'country' => $r[1], 'rating' => $r[2], 'title' => $r[3], 'body' => $r[4], 'locale' => $r[5],
                'room_type_id' => $roomTypes[$i % 4]->id, 'is_approved' => true, 'created_at' => now()->subDays(mt_rand(5, 200))]);
        }
        Review::create(['name' => 'Peter G.', 'country' => 'Netherlands', 'rating' => 3, 'title' => 'Good but pricey', 'body' => 'Nice hotel, but drinks at the pool bar are expensive.', 'is_approved' => false, 'room_type_id' => $roomTypes[0]->id]);
        Review::find(1)->update(['reply' => 'Thank you, Sophie! We look forward to welcoming you back.']);

        // --- Content ----------------------------------------------------
        $facilities = [
            ['spa', 'Aurora Spa & Wellness', 'СПА и велнес Aurora', '2,000 m² of pure relaxation: thermal circuit, hammam, salt room and 12 treatment rooms with signature rituals.', '2 000 м² расслабления: термальный комплекс, хаммам, соляная комната и 12 кабинетов с авторскими ритуалами.', '09:00 – 22:00', 'sparkles', $img('1544161515-4ab6ce6db874')],
            ['restaurant', 'Azure Restaurant', 'Ресторан Azure', 'Mediterranean fine dining with seafood caught daily and an award-winning wine cellar of 600 labels.', 'Средиземноморская высокая кухня с ежедневным уловом и винной картой из 600 позиций.', '07:00 – 23:00', 'cake', $img('1414235077428-338989a2e8c0')],
            ['pools', 'Infinity Pools', 'Инфинити-бассейны', 'Three heated outdoor pools including an adults-only infinity pool overlooking the bay.', 'Три подогреваемых бассейна, включая инфинити-бассейн только для взрослых с видом на бухту.', '08:00 – 20:00', 'sun', $img('1566073771259-6a8506099945')],
            ['beach', 'Private Beach', 'Собственный пляж', '300 metres of golden sand with sun loungers, cabanas and beach butler service.', '300 метров золотого песка с шезлонгами, кабанами и пляжным батлером.', '08:00 – 19:00', 'sun', $img('1507525428034-b723cf961d3e')],
            ['fitness', 'Fitness Center', 'Фитнес-центр', 'Technogym equipment, personal trainers, yoga and pilates classes every morning.', 'Тренажёры Technogym, персональные тренеры, йога и пилатес каждое утро.', '24/7', 'bolt', $img('1534438327276-14e5300c3a48')],
            ['kids-club', 'Aurora Kids Club', 'Детский клуб Aurora', 'Supervised activities for ages 4–12: art studio, mini-disco, treasure hunts and cooking classes.', 'Занятия под присмотром для детей 4–12 лет: арт-студия, мини-диско, квесты и кулинарные мастер-классы.', '10:00 – 18:00', 'face-smile', $img('1596461404969-9ae70f2830c1')],
            ['bar', 'Sunset Lounge Bar', 'Лаунж-бар Sunset', 'Signature cocktails and live jazz every evening on our rooftop terrace.', 'Авторские коктейли и живой джаз каждый вечер на террасе на крыше.', '17:00 – 02:00', 'musical-note', $img('1514933651103-005eec06c04b')],
            ['events', 'Meetings & Events', 'Конференции и события', 'Ballroom for 400 guests, 6 meeting rooms and a dedicated wedding team.', 'Бальный зал на 400 гостей, 6 переговорных и команда по организации свадеб.', 'On request', 'briefcase', $img('1519167758481-83f550bb49b3')],
        ];
        foreach ($facilities as $i => $f) {
            Facility::create(['slug' => $f[0], 'name_en' => $f[1], 'name_ru' => $f[2], 'description_en' => $f[3], 'description_ru' => $f[4], 'hours' => $f[5], 'icon' => $f[6], 'image' => $f[7], 'sort' => $i]);
        }

        $offers = [
            ['Stay 3, save 20%', 'Живите 3 ночи, экономьте 20%', 'Book three nights or more and enjoy 20% off your room rate plus a complimentary welcome drink.', 'Забронируйте от трёх ночей и получите скидку 20% и приветственный напиток.', '-20%', '-20%', 'AURORA20', $img('1571896349842-33c89424de2d')],
            ['Romantic Escape', 'Романтический побег', 'Champagne on arrival, couples massage and candle-lit dinner on the beach.', 'Шампанское при заезде, массаж для двоих и ужин при свечах на пляже.', 'Couples', 'Для пар', null, $img('1519741497674-611481863552')],
            ['Family Summer', 'Семейное лето', 'Kids stay and eat free, plus unlimited access to the Kids Club.', 'Дети живут и питаются бесплатно, плюс безлимитный детский клуб.', 'Kids free', 'Дети бесплатно', null, $img('1602002418082-a4443e081dd1')],
            ['Welcome discount', 'Скидка новым гостям', 'First time with us? Use code WELCOME10 for 10% off any room.', 'Впервые у нас? Используйте код WELCOME10 и получите 10% скидки.', '-10%', '-10%', 'WELCOME10', $img('1542314831-068cd1dbfeeb')],
        ];
        foreach ($offers as $i => $o) {
            Offer::create(['title_en' => $o[0], 'title_ru' => $o[1], 'description_en' => $o[2], 'description_ru' => $o[3], 'badge_en' => $o[4], 'badge_ru' => $o[5], 'promo_code' => $o[6], 'image' => $o[7], 'valid_until' => now()->addMonths(4), 'sort' => $i]);
        }

        $gallery = [
            ['1566073771259-6a8506099945', 'hotel', 'Main pool at sunset', 'Главный бассейн на закате'],
            ['1542314831-068cd1dbfeeb', 'hotel', 'Hotel facade', 'Фасад отеля'],
            ['1564501049412-61c2a3083791', 'hotel', 'Grand lobby', 'Главное лобби'],
            ['1582719478250-c89cae4dc85b', 'rooms', 'Deluxe Sea View', 'Делюкс с видом на море'],
            ['1611892440504-42a792e24d32', 'rooms', 'Classic Room', 'Классический номер'],
            ['1618773928121-c32242e63f39', 'rooms', 'Junior Suite', 'Джуниор-сюит'],
            ['1544161515-4ab6ce6db874', 'spa', 'Signature massage', 'Фирменный массаж'],
            ['1540555700478-4be289fbecef', 'spa', 'Spa relaxation area', 'Зона отдыха СПА'],
            ['1414235077428-338989a2e8c0', 'dining', 'Azure Restaurant', 'Ресторан Azure'],
            ['1517248135467-4c7edcad34c4', 'dining', 'Dinner service', 'Вечерний сервис'],
            ['1507525428034-b723cf961d3e', 'beach', 'Private beach', 'Собственный пляж'],
            ['1520250497591-112f2f40a3f4', 'beach', 'Beach villas', 'Пляжные виллы'],
        ];
        foreach ($gallery as $i => $g) {
            GalleryImage::create(['url' => $img($g[0]), 'category' => $g[1], 'caption_en' => $g[2], 'caption_ru' => $g[3], 'sort' => $i]);
        }

        $faqs = [
            ['What are check-in and check-out times?', 'Какое время заезда и выезда?', 'Check-in is from 14:00 and check-out is until 12:00. Early check-in and late check-out are available on request.', 'Заезд с 14:00, выезд до 12:00. Ранний заезд и поздний выезд — по запросу.'],
            ['What is the cancellation policy?', 'Какие условия отмены?', 'Free cancellation up to 48 hours before arrival. Later cancellations are charged one night.', 'Бесплатная отмена за 48 часов до заезда. При более поздней отмене удерживается стоимость одной ночи.'],
            ['Is breakfast included?', 'Включён ли завтрак?', 'Breakfast can be added during booking or at the reception for $25 per person per night.', 'Завтрак можно добавить при бронировании или на ресепшене — $25 на человека за ночь.'],
            ['Do you offer airport transfers?', 'Есть ли трансфер из аэропорта?', 'Yes, a private Mercedes transfer costs $60 one way. Presidential Suite guests enjoy complimentary limousine service.', 'Да, индивидуальный трансфер на Mercedes — $60 в одну сторону. Для гостей Президентского люкса — бесплатно на лимузине.'],
            ['Are pets allowed?', 'Можно ли с животными?', 'Small pets up to 8 kg are welcome in Classic Rooms and Beach Villas for $30 per night.', 'Небольшие питомцы до 8 кг допускаются в Классических номерах и Пляжных виллах за $30 за ночь.'],
            ['Is there parking?', 'Есть ли парковка?', 'Secure underground parking is available for $15 per night. EV charging stations included.', 'Охраняемая подземная парковка — $15 за ночь, есть зарядки для электромобилей.'],
            ['Which payment methods do you accept?', 'Какие способы оплаты?', 'We accept Visa, Mastercard, American Express, Idram and cash. You can prepay online or pay at the hotel.', 'Принимаем Visa, Mastercard, American Express, Idram и наличные. Можно оплатить онлайн или в отеле.'],
        ];
        foreach ($faqs as $i => $f) {
            Faq::create(['question_en' => $f[0], 'question_ru' => $f[1], 'answer_en' => $f[2], 'answer_ru' => $f[3], 'sort' => $i]);
        }

        $posts = [
            ['new-spa-rituals', 'spa', 'Introducing our new Ocean Spa rituals', 'Представляем новые СПА-ритуалы «Океан»', 'Discover four new treatments inspired by the sea, using marine minerals and local botanicals.', 'Четыре новые процедуры, вдохновлённые морем, с морскими минералами и местными травами.', '1540555700478-4be289fbecef'],
            ['chefs-table', 'dining', "Chef's Table: a seven-course journey", 'Стол шефа: путешествие из семи блюд', 'Every Friday our executive chef hosts an intimate dinner for just ten guests.', 'Каждую пятницу шеф-повар проводит камерный ужин всего для десяти гостей.', '1517248135467-4c7edcad34c4'],
            ['top-10-things-azure-bay', 'travel', '10 things to do in Azure Bay', '10 причин посетить Лазурную бухту', 'From hidden coves to the old lighthouse, here is our concierge team\'s favourite list.', 'От тайных бухт до старого маяка — любимые места нашей команды консьержей.', '1507525428034-b723cf961d3e'],
            ['weddings-2027', 'events', 'Weddings by the sea: 2027 dates open', 'Свадьбы у моря: открыты даты на 2027 год', 'Our wedding team is now accepting bookings for beach ceremonies and ballroom receptions.', 'Свадебная команда принимает заявки на церемонии на пляже и банкеты в бальном зале.', '1519741497674-611481863552'],
            ['sustainability', 'news', 'Our path to a greener hotel', 'Наш путь к экологичному отелю', 'Solar energy, zero single-use plastic and a rooftop vegetable garden: how we reduce our footprint.', 'Солнечная энергия, отказ от одноразового пластика и огород на крыше: как мы заботимся о природе.', '1542314831-068cd1dbfeeb'],
        ];
        foreach ($posts as $i => $p) {
            $bodyEn = "<p>{$p[4]}</p><p>At Aurora Grand Hotel & Spa we believe that every stay should feel personal. Our team has spent months preparing this experience, working with local partners and artisans to make it truly unique.</p><h3>What to expect</h3><ul><li>Personal attention from our concierge team</li><li>Locally sourced ingredients and products</li><li>Special rates for hotel guests</li></ul><p>Contact our reservations team or book online to secure your place.</p>";
            $bodyRu = "<p>{$p[5]}</p><p>В Aurora Grand Hotel & Spa мы верим, что каждое пребывание должно быть особенным. Наша команда месяцами готовила этот опыт вместе с местными партнёрами и мастерами.</p><h3>Что вас ждёт</h3><ul><li>Персональное внимание консьержей</li><li>Местные продукты и ингредиенты</li><li>Специальные цены для гостей отеля</li></ul><p>Свяжитесь с отделом бронирования или забронируйте онлайн.</p>";
            Post::create(['slug' => $p[0], 'category' => $p[1], 'title_en' => $p[2], 'title_ru' => $p[3], 'excerpt_en' => $p[4], 'excerpt_ru' => $p[5],
                'body_en' => $bodyEn, 'body_ru' => $bodyRu, 'image' => $img($p[6]), 'is_published' => true, 'published_at' => now()->subDays(($i + 1) * 9)]);
        }

        ContactMessage::create(['name' => 'Laura Benson', 'email' => 'laura@mail.example', 'phone' => '+44 20 7946 0000', 'subject' => 'Wedding inquiry', 'message' => 'Hello! We are planning a wedding for 120 guests next June. Could you send us your packages?']);
        ContactMessage::create(['name' => 'Сергей Иванов', 'email' => 'sergey@mail.example', 'subject' => 'Трансфер', 'message' => 'Здравствуйте, можно ли заказать трансфер на 6 человек?', 'is_read' => true]);
        ContactMessage::create(['name' => 'Tom Hughes', 'email' => 'tom@mail.example', 'subject' => 'Corporate rates', 'message' => 'Do you offer corporate rates for 20 rooms in November?']);

        foreach (['alice@mail.example', 'bob@mail.example', 'olga@mail.example', 'kenji@mail.example'] as $email) {
            Subscriber::create(['email' => $email]);
        }
    }
}
