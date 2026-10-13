<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * robots.txt и sitemap.xml. Индексируется только основной домен, тестовые закрыты целиком.
 */
class SeoController extends Controller
{
    public function robots(Request $request): Response
    {
        $root = config('app.canonical_root');

        $lines = self::isMainHost($request)
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /livewire', 'Disallow: /a/', '', "Sitemap: {$root}/sitemap.xml"]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $root = config('app.canonical_root');
        $urls = Section::where('is_published', true)->whereNull('external_url')->orderBy('sort')->get()
            ->map(fn (Section $s) => ['loc' => $root.$s->url(), 'lastmod' => $s->updated_at?->toAtomString()]);

        return response()->view('seo.sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** Запрос пришёл на основной домен (b-integration.ru), а не на тестовый. */
    public static function isMainHost(Request $request): bool
    {
        return $request->getHost() === parse_url(config('app.canonical_root'), PHP_URL_HOST);
    }
}
