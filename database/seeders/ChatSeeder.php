<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\ChatConversation;
use App\Models\User;
use App\Services\ChatBot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/** Demo live-chat conversations (en/ru/hy), some answered by staff, two with unread guest messages. */
class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $bot = app(ChatBot::class);
        $manager = User::where('role', 'manager')->first() ?? User::where('role', 'admin')->first();
        $reception = User::where('role', 'reception')->first() ?? $manager;
        $booking = Booking::where('status', 'confirmed')->where('check_in', '>=', today())->orderBy('check_in')->first();
        $locale = app()->getLocale();

        $conversations = [
            [
                'name' => $booking?->first_name ?? 'Emily', 'email' => $booking?->email, 'locale' => 'en', 'status' => 'open', 'start' => now()->subHours(40),
                'messages' => [
                    ['guest', 'Hi! What rooms do you have and what are the prices?', 0],
                    ['bot', null, 0],
                    ['guest', "We're celebrating our anniversary — could you arrange flowers and champagne in the room?", 3],
                    ['bot', null, 3],
                    ['staff', "Happy anniversary! Of course — I've added a note to your booking: flowers and a bottle of champagne on arrival, with our compliments.", 9, $manager],
                    ['guest', 'That is wonderful, thank you so much!', 12],
                    ['staff', 'My pleasure. See you soon at Aurora Grand!', 13, $manager],
                ],
            ],
            [
                'name' => 'Алексей', 'email' => null, 'locale' => 'ru', 'status' => 'open', 'start' => now()->subHours(26),
                'messages' => [
                    ['guest', 'Здравствуйте! Есть ли трансфер из аэропорта?', 0],
                    ['bot', null, 0],
                    ['guest', 'Наш рейс прилетает в 02:30 ночи, сможете встретить?', 2],
                    ['bot', null, 2],
                    ['staff', 'Добрый день, Алексей! Да, водитель встретит вас в зоне прилёта с табличкой. Пришлите, пожалуйста, номер рейса.', 7, $reception],
                    ['guest', 'Спасибо! Рейс SU 1124.', 15],
                    ['staff', 'Записали. Хорошего полёта!', 16, $reception],
                ],
            ],
            [
                'name' => 'Ольга Смирнова', 'email' => 'olga.smirnova@example.com', 'locale' => 'ru', 'status' => 'closed', 'start' => now()->subHours(30),
                'messages' => [
                    ['guest', 'Как можно оплатить проживание?', 0],
                    ['bot', null, 0],
                    ['guest', 'Отлично, спасибо!', 1],
                    ['bot', null, 1],
                ],
            ],
            [
                'name' => 'Անի', 'email' => null, 'locale' => 'hy', 'status' => 'open', 'start' => now()->subHours(3),
                'messages' => [
                    ['guest', 'Բարև Ձեզ։ Ժամը քանիսի՞ն է մուտքը և ելքը։', 0],
                    ['bot', null, 0],
                    ['guest', 'Կարո՞ղ ենք ժամանել առավոտյան 9-ին, մենք երեխայի հետ ենք։', 4],
                    ['bot', null, 4],
                ],
                'unread' => true,
            ],
            [
                'name' => null, 'email' => null, 'locale' => 'en', 'status' => 'open', 'start' => now()->subMinutes(25),
                'messages' => [
                    ['guest', 'Hello, is the spa open in the evening?', 0],
                    ['bot', null, 0],
                    ['guest', 'Can I book a couples massage for tomorrow at 7 pm?', 3],
                    ['bot', null, 3],
                ],
                'unread' => true,
            ],
        ];

        foreach ($conversations as $data) {
            app()->setLocale($data['locale']);
            $start = Carbon::parse($data['start']);

            $conversation = ChatConversation::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'locale' => $data['locale'],
                'status' => $data['status'],
                'created_at' => $start,
                'updated_at' => $start,
            ]);

            $lastGuest = null;
            $lastBot = null;
            foreach ($data['messages'] as $msg) {
                [$sender, $body, $minutes] = $msg;
                $at = $start->copy()->addMinutes($minutes)->addSeconds($sender === 'bot' ? 2 : 0);
                if ($sender === 'bot') {
                    $answer = $bot->answer($lastGuest);
                    $body = $answer !== null && $answer !== $lastBot ? $answer : __('chat.bot.fallback');
                    $lastBot = $body;
                }
                if ($sender === 'guest') {
                    $lastGuest = $body;
                }

                $conversation->messages()->create([
                    'sender' => $sender,
                    'user_id' => $sender === 'staff' ? ($msg[3] ?? null)?->id : null,
                    'body' => $body,
                    'read_at' => $at,
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);
            }

            if ($data['unread'] ?? false) {
                // Latest guest message not yet seen by staff.
                $conversation->messages()->where('sender', 'guest')->latest('id')->first()?->update(['read_at' => null]);
            }
        }

        app()->setLocale($locale);
    }
}
