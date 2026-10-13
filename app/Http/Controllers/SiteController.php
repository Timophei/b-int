<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Lead;
use App\Models\Section;
use App\Models\Setting;
use App\Support\TrafficSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        $home = Section::byKey('home') ?? abort(404);
        $featured = Section::navigation()->firstWhere('key', 'support')?->load(['services', 'plans', 'steps']);

        return view('site.home', [
            'section' => $home,
            'featured' => $featured,
        ]);
    }

    /** Страница раздела по адресу из админки. Черновики видит только вошедший админ. */
    public function page(string $path): View|RedirectResponse
    {
        $section = Section::where('path', trim($path, '/'))->where('key', '!=', 'home')->first();
        abort_unless($section && ($section->is_published || auth()->check()), 404);

        if ($section->isExternal()) {
            return redirect()->away($section->external_url);
        }

        $section->load(['services', 'plans', 'steps']);
        $view = match ($section->key) {
            'contact' => 'site.contact',
            'privacy' => 'site.text',
            default => view()->exists("site.section-{$section->key}") ? "site.section-{$section->key}" : 'site.section',
        };

        return view($view, [
            'section' => $section,
            'topics' => Section::navigation()->reject->isExternal(),
        ]);
    }

    public function lead(Request $request): RedirectResponse
    {
        // Ловушка для роботов: скрытое поле заполняют только они
        if (filled($request->input('website'))) {
            return back()->with('sent', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'contact' => ['required', 'string', 'max:190'],
            'company' => ['nullable', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:5000'],
            'topic' => ['nullable', 'string', 'max:40'],
            'consent' => ['accepted'],
        ], [
            'name.required' => 'Как к вам обращаться?',
            'contact.required' => 'Оставьте телефон, почту или ник в мессенджере.',
            'consent.accepted' => 'Нужно согласие на обработку данных.',
        ]);

        parse_str(ltrim((string) $request->input('q', ''), '?'), $query);
        $source = TrafficSource::detect(Str::limit((string) $request->input('r', ''), 500, '') ?: null, $query, $request->getHost());
        $utm = fn (string $key): ?string => isset($query[$key]) && is_string($query[$key]) ? Str::limit($query[$key], 150, '') : null;

        $lead = Lead::create([
            ...collect($data)->except('consent')->all(),
            'page' => Str::limit((string) $request->input('from', ''), 255, '') ?: null,
            'source' => $source['source'],
            'source_name' => $source['source_name'],
            'utm_source' => $utm('utm_source'),
            'utm_campaign' => $utm('utm_campaign'),
        ]);

        $this->notify($lead);

        return redirect(Section::byKey('contact')?->url() ?? '/')->with('sent', true);
    }

    /** Учёт нажатий на контакты и кнопки (sendBeacon из site.js). */
    public function click(Request $request): Response
    {
        $kind = (string) $request->input('k');
        if (array_key_exists($kind, Click::KINDS) && ! auth()->check() && SeoController::isMainHost($request)
            && ! TrafficSource::isBot((string) $request->userAgent())) {
            Click::create([
                'created_at' => now(),
                'kind' => $kind,
                'label' => Str::limit((string) $request->input('l', ''), 150, '') ?: null,
                'path' => Str::limit((string) $request->input('p', ''), 255, '') ?: null,
            ]);
        }

        return response()->noContent();
    }

    private function notify(Lead $lead): void
    {
        $to = Setting::current()->notify_email;
        if (! $to || in_array(config('mail.default'), ['log', 'array'], true) && ! app()->runningUnitTests()) {
            return;
        }

        try {
            $lines = [
                'Имя: '.$lead->name,
                'Контакт: '.$lead->contact,
                'Компания: '.($lead->company ?: '—'),
                'Тема: '.(Section::KEYS[$lead->topic] ?? ($lead->topic ?: '—')),
                '',
                (string) $lead->message,
                '',
                'Все заявки: '.url('/admin/leads'),
            ];
            Mail::raw(implode("\n", $lines), fn ($m) => $m->to($to)->subject('Заявка с сайта: '.$lead->name));
        } catch (\Throwable $e) {
            // Заявка уже в админке; письмо — только уведомление
            Log::warning('Не отправилось письмо о заявке: '.$e->getMessage());
        }
    }
}
