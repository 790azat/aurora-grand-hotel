<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\RoomType;
use Illuminate\Http\Response;

class SeoController
{
    /** XML sitemap of all public pages, room types and published posts. */
    public function sitemap(): Response
    {
        $now = now()->toAtomString();
        $urls = [];

        foreach ([
            'home' => ['1.0', 'weekly'],
            'rooms.index' => ['0.9', 'weekly'],
            'offers' => ['0.8', 'weekly'],
            'facilities' => ['0.7', 'monthly'],
            'gallery' => ['0.6', 'monthly'],
            'about' => ['0.6', 'monthly'],
            'reviews' => ['0.6', 'weekly'],
            'blog.index' => ['0.7', 'weekly'],
            'contact' => ['0.6', 'yearly'],
            'faq' => ['0.5', 'monthly'],
            'booking' => ['0.8', 'monthly'],
            'privacy' => ['0.2', 'yearly'],
            'terms' => ['0.2', 'yearly'],
        ] as $route => [$priority, $freq]) {
            $urls[] = ['loc' => route($route), 'lastmod' => $now, 'changefreq' => $freq, 'priority' => $priority];
        }

        foreach (RoomType::active()->get() as $type) {
            $urls[] = [
                'loc' => route('rooms.show', $type),
                'lastmod' => $type->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'image' => $type->cover,
            ];
        }

        foreach (Post::published()->get() as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post),
                'lastmod' => ($post->updated_at ?? $post->published_at)?->toAtomString() ?? $now,
                'changefreq' => 'monthly',
                'priority' => '0.6',
                'image' => $post->image,
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc'])."</loc>\n";
            $xml .= '    <lastmod>'.$url['lastmod']."</lastmod>\n";
            $xml .= '    <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$url['priority']."</priority>\n";
            if (! empty($url['image'])) {
                $xml .= '    <image:image><image:loc>'.e($url['image'])."</image:loc></image:image>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /booking/',
            'Disallow: /manage-booking',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /livewire',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
