<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Armenian (`_hy`) translations next to the existing `_en` / `_ru` columns. */
    private const COLUMNS = [
        'amenities' => ['name' => 'string'],
        'room_types' => ['name' => 'string', 'short' => 'string', 'description' => 'text', 'beds' => 'string', 'view' => 'string'],
        'extras' => ['name' => 'string', 'description' => 'string'],
        'posts' => ['title' => 'string', 'excerpt' => 'text', 'body' => 'longText'],
        'offers' => ['title' => 'string', 'description' => 'text', 'badge' => 'string'],
        'facilities' => ['name' => 'string', 'description' => 'text'],
        'gallery_images' => ['caption' => 'string'],
        'faqs' => ['question' => 'string', 'answer' => 'text'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            Schema::table($table, function (Blueprint $t) use ($columns) {
                foreach ($columns as $name => $type) {
                    $t->{$type}("{$name}_hy")->nullable()->after("{$name}_ru");
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn(array_map(fn ($c) => "{$c}_hy", array_keys($columns))));
        }
    }
};
