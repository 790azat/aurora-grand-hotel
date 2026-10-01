<?php

namespace App\Livewire\Rooms;

use App\Models\Review;
use App\Models\RoomType;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    public RoomType $roomType;

    public function mount(RoomType $roomType): void
    {
        abort_unless($roomType->is_active, 404);
        $this->roomType = $roomType->load('amenities');
    }

    public function render()
    {
        $type = $this->roomType;
        $reviews = Review::approved()->where('room_type_id', $type->id);

        return view('livewire.rooms.show', [
            'reviews' => (clone $reviews)->take(4)->get(),
            'reviewCount' => (clone $reviews)->count(),
            'rating' => round((float) (clone $reviews)->avg('rating'), 1),
            'others' => RoomType::active()->whereKeyNot($type->id)->with('amenities')->get(),
        ])->title($type->name)
            ->layoutData([
                'description' => Str::limit(strip_tags($type->short.' '.$type->description), 155),
                'ogImage' => $type->cover,
            ]);
    }
}
