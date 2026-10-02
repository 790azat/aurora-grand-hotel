<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Extra;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\RoomType;
use App\Models\Setting;

/**
 * Instant keyword-based concierge replies for the live chat.
 *
 * Answers in the conversation's locale while no staff member is active
 * in the thread; anything it cannot match is handed over to a manager.
 */
class ChatBot
{
    /** Minutes of staff silence after which the bot answers again. */
    public const STAFF_QUIET_MINUTES = 10;

    /** Max topics combined in one reply. */
    protected const MAX_TOPICS = 2;

    /**
     * Keyword stems per topic (matched as substrings of the lower-cased message).
     * Order matters: earlier topics win when more than MAX_TOPICS match.
     */
    public const KEYWORDS = [
        'checkin' => [
            'check-in', 'check in', 'checkin', 'check-out', 'check out', 'checkout', 'arrival time', 'early arrival', 'late departure',
            'заезд', 'выезд', 'заселен', 'засел', 'расчетн', 'расчётн', 'во сколько',
            'մուտք', 'ելք', 'տեղավոր', 'դուրս գալ', 'ժամը քանիս',
        ],
        'prices' => [
            'price', 'cost', 'rates', 'how much', 'tariff', 'rooms', 'room type', 'which room', 'what room', 'suite', 'villa', 'per night',
            'цен', 'стоим', 'сколько стоит', 'тариф', 'номера', 'номеров', 'какие номер', 'люкс', 'вилл', 'за ночь',
            'գին', 'գներ', 'արժե', 'ինչքան', 'որքան', 'սենյակ', 'համարներ', 'լյուքս', 'վիլլա', 'գիշեր',
        ],
        'booking' => [
            'book', 'reserv', 'availab', 'free room',
            'бронир', 'бронь', 'забронир', 'свободн', 'наличи',
            'ամրագր', 'ամրագրում', 'ազատ',
        ],
        'extras' => [
            'transfer', 'airport', 'taxi', 'pick up', 'pickup', 'shuttle', 'breakfast', 'parking', 'extra', 'baby cot', 'crib',
            'трансфер', 'аэропорт', 'такси', 'встрет', 'завтрак', 'парковк', 'стоянк', 'детская кроват', 'допол', 'услуг',
            'տրանսֆեր', 'օդանավակայան', 'տաքսի', 'նախաճաշ', 'կայանատեղ', 'ավտոկայան', 'մանկական մահճակալ', 'ծառայություն',
        ],
        'facilities' => [
            'spa', 'pool', 'gym', 'fitness', 'restaurant', 'dinner', 'lunch', 'bar', 'massage', 'beach', 'kids club', 'sauna',
            'спа', 'бассейн', 'фитнес', 'спортзал', 'тренажер', 'ресторан', 'ужин', 'обед', 'бар', 'массаж', 'пляж', 'детский клуб', 'сауна',
            'սպա', 'լողավազան', 'ֆիթնես', 'մարզասրահ', 'ռեստորան', 'ընթրիք', 'ճաշ', 'բար', 'մերսում', 'լողափ', 'սաունա',
        ],
        'payment' => [
            'pay', 'paying', 'payment', 'card', 'idram', 'cash', 'visa', 'mastercard', 'deposit', 'prepay',
            'оплат', 'плат', 'карт', 'идрам', 'наличн', 'депозит', 'предоплат',
            'վճար', 'քարտ', 'իդրամ', 'կանխիկ', 'կանխավճար',
        ],
        'cancel' => [
            'cancel', 'refund', 'change my booking', 'modify',
            'отмен', 'аннул', 'возврат', 'вернуть деньги', 'изменить брон',
            'չեղարկ', 'չեղյալ', 'վերադարձ', 'փոխել ամրագր',
        ],
        'contacts' => [
            'phone', 'call', 'email', 'e-mail', 'contact', 'address', 'where are you', 'location', 'whatsapp',
            'телефон', 'позвон', 'почт', 'контакт', 'адрес', 'где вы', 'где наход', 'как добраться',
            'հեռախոս', 'զանգ', 'էլ. փոստ', 'էլփոստ', 'կոնտակտ', 'հասցե', 'որտեղ', 'ինչպես հասնել',
        ],
    ];

    protected const GREETINGS = ['hi', 'hello', 'hey', 'good morning', 'good evening', 'привет', 'здравствуй', 'добрый', 'доброе', 'բարև', 'բարի', 'ողջույն'];

    protected const THANKS = ['thank', 'thanks', 'thx', 'спасибо', 'благодар', 'շնորհակալ', 'մերսի', 'merci'];

    /** Reply to a guest message, or null when the bot should stay silent. */
    public function respond(ChatConversation $conversation, string $message): ?ChatMessage
    {
        if ($this->staffIsActive($conversation)) {
            return null;
        }

        $previous = app()->getLocale();
        app()->setLocale($conversation->locale ?: config('app.locale'));

        try {
            $text = $this->answer($message);

            // Never repeat the previous bot answer word for word; hand over instead.
            $lastBot = $conversation->messages()->where('sender', 'bot')->reorder()->latest('id')->value('body');
            if ($text !== null && $text === $lastBot) {
                $text = null;
            }

            if ($text === null) {
                // Don't repeat the hand-over note on every message.
                $recentFallback = $conversation->messages()
                    ->where('sender', 'bot')->where('body', __('chat.bot.fallback'))
                    ->where('created_at', '>=', now()->subMinutes(self::STAFF_QUIET_MINUTES))
                    ->exists();
                if ($recentFallback) {
                    return null;
                }
                $text = __('chat.bot.fallback');
            }
        } finally {
            app()->setLocale($previous);
        }

        return $conversation->messages()->create(['sender' => 'bot', 'body' => $text]);
    }

    public function staffIsActive(ChatConversation $conversation): bool
    {
        return $conversation->messages()
            ->where('sender', 'staff')
            ->where('created_at', '>=', now()->subMinutes(self::STAFF_QUIET_MINUTES))
            ->exists();
    }

    /** Answer text in the current app locale, or null when nothing matched. */
    public function answer(string $message): ?string
    {
        $text = $this->normalize($message);
        if ($text === '') {
            return null;
        }

        $topics = $this->topics($text);

        if ($topics) {
            return collect($topics)
                ->take(self::MAX_TOPICS)
                ->map(fn ($topic) => $this->{'reply'.ucfirst($topic)}())
                ->implode("\n\n");
        }

        if ($faq = $this->matchFaq($text)) {
            return $faq;
        }

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) <= 6 && $this->contains($text, self::THANKS)) {
            return __('chat.bot.thanks');
        }
        if (count($words) <= 5 && $this->contains($text, self::GREETINGS, wordStart: true)) {
            return __('chat.bot.greeting');
        }

        return null;
    }

    /** @return list<string> matched topic keys in priority order */
    public function topics(string $normalized): array
    {
        $found = [];
        foreach (self::KEYWORDS as $topic => $keywords) {
            if ($this->contains($normalized, $keywords, wordStart: true)) {
                $found[] = $topic;
            }
        }

        // "Cancel my booking" is not a booking request.
        if (in_array('cancel', $found, true)) {
            $found = array_values(array_diff($found, ['booking']));
        }

        // "Book a room": lead with the booking link, prices second.
        if (in_array('booking', $found, true) && in_array('prices', $found, true)) {
            $found = array_values(array_unique(array_merge(['booking', 'prices'], $found)));
        }

        return $found;
    }

    protected function normalize(string $message): string
    {
        $text = mb_strtolower($message);
        $text = str_replace(['ё', '՞', '՜', '՛'], ['е', '', '', ''], $text);
        $text = preg_replace('/[^\p{L}\p{N}\s\-\.]+/u', ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** True when any keyword occurs at the start of a word. */
    protected function contains(string $text, array $keywords, bool $wordStart = false): bool
    {
        foreach ($keywords as $kw) {
            // Short keywords ("spa", "bar", "pay") must be whole words: not "spaсибо", "բարև".
            $whole = mb_strlen($kw) <= 3 ? '(?![\p{L}\p{N}])' : '';
            $pattern = '/'.($wordStart ? '(?<![\p{L}\p{N}])' : '').preg_quote(mb_strtolower($kw), '/').$whole.'/u';
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    protected function replyPrices(): string
    {
        $lines = RoomType::active()->get()
            ->map(fn (RoomType $t) => '• '.$t->name.' — '.__('chat.bot.from_price', ['price' => money($t->base_price)]))
            ->implode("\n");

        return __('chat.bot.prices_intro')."\n".$lines."\n".__('chat.bot.prices_outro', ['url' => $this->path('rooms.index')]);
    }

    protected function replyCheckin(): string
    {
        return __('chat.bot.checkin', [
            'in' => setting('check_in_time'),
            'out' => setting('check_out_time'),
        ]);
    }

    protected function replyExtras(): string
    {
        $lines = Extra::query()->where('is_active', true)->orderBy('sort')->get()
            ->map(fn (Extra $e) => '• '.$e->name.' — '.((float) $e->price > 0
                ? money($e->price).' '.__('chat.bot.pricing.'.$e->pricing)
                : __('chat.bot.free')))
            ->implode("\n");

        return __('chat.bot.extras_intro')."\n".$lines."\n".__('chat.bot.extras_outro');
    }

    protected function replyFacilities(): string
    {
        $lines = Facility::query()->where('is_active', true)->orderBy('sort')->get()
            ->map(fn (Facility $f) => '• '.$f->name.($f->hours ? ' · '.$f->hours : ''))
            ->implode("\n");

        return __('chat.bot.facilities_intro')."\n".$lines."\n".__('chat.bot.facilities_outro', ['url' => $this->path('facilities')]);
    }

    protected function replyPayment(): string
    {
        return __('chat.bot.payment');
    }

    protected function replyCancel(): string
    {
        return __('chat.bot.cancel', [
            'hours' => setting('free_cancellation_hours', 48),
            'url' => $this->path('booking.lookup'),
        ]);
    }

    protected function replyBooking(): string
    {
        return __('chat.bot.booking', ['url' => $this->path('booking')]);
    }

    protected function replyContacts(): string
    {
        return __('chat.bot.contacts', [
            'phone' => setting('hotel_phone'),
            'email' => setting('hotel_email'),
            'address' => Setting::localized('hotel_address'),
        ]);
    }

    /** Best FAQ answer by word overlap with the question, or null. */
    protected function matchFaq(string $text): ?string
    {
        $words = $this->significantWords($text);
        if (! $words) {
            return null;
        }

        $best = null;
        $bestScore = 0.0;
        foreach (Faq::query()->where('is_active', true)->get() as $faq) {
            $q = $this->significantWords($this->normalize((string) $faq->question));
            if (! $q) {
                continue;
            }
            $hits = 0;
            foreach ($words as $w) {
                foreach ($q as $qw) {
                    // Compare 5-letter stems so "pets"/"pet", "животные"/"животными" match.
                    if (mb_substr($w, 0, 5) === mb_substr($qw, 0, 5)) {
                        $hits++;
                        break;
                    }
                }
            }
            $score = $hits / max(1, min(count($words), count($q)));
            if ($hits > 0 && $score > $bestScore) {
                $best = $faq;
                $bestScore = $score;
            }
        }

        return $best && $bestScore >= 0.5 ? $best->answer : null;
    }

    /** Words of 4+ letters (stop-word-ish short words dropped). */
    protected function significantWords(string $text): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $stop = ['what', 'which', 'when', 'where', 'there', 'your', 'have', 'does', 'with', 'this', 'that', 'allowed', 'offer',
            'какие', 'какой', 'какое', 'можно', 'есть', 'ваши', 'ваша', 'если', 'ли',
            'ինչ', 'կա', 'արդյոք', 'ձեր', 'կարելի', 'որոնք'];

        return array_values(array_filter($words, fn ($w) => mb_strlen($w) >= 4 && ! in_array($w, $stop, true)));
    }

    /** Site-relative URL (stored in messages so they survive a host change). */
    protected function path(string $route): string
    {
        return route($route, [], false);
    }
}
