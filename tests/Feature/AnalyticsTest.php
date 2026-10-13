<?php

namespace Tests\Feature;

use App\Models\Click;
use App\Models\PageView;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) Mobile/15E148';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function hit(array $data, string $host = 'b-integration.ru', string $agent = self::BROWSER): void
    {
        $this->withHeader('User-Agent', $agent)->postJson("https://{$host}/a/hit", $data)->assertNoContent();
    }

    public function test_page_shows_counter_script_only_on_main_domain_for_guests(): void
    {
        $this->get('https://b-integration.ru/')->assertSee("sendBeacon('/a/hit'", false);
        $this->get('https://t.b-integration.ru/')->assertDontSee("sendBeacon('/a/hit'", false);
    }

    public function test_view_is_recorded_with_source_and_utm(): void
    {
        $this->hit(['p' => '/podderzhka', 'q' => '?utm_source=vk&utm_medium=post&utm_campaign=osen', 'r' => 'https://vk.com/wall-1']);

        $view = PageView::sole();
        $this->assertSame('/podderzhka/', $view->path);
        $this->assertSame('social', $view->source);
        $this->assertSame('osen', $view->utm_campaign);
        $this->assertSame('mobile', $view->device);
        $this->assertTrue($view->is_entry);
    }

    public function test_bots_admin_and_test_domain_are_not_counted(): void
    {
        $this->hit(['p' => '/'], agent: 'Mozilla/5.0 (compatible; YandexBot/3.0)');
        $this->hit(['p' => '/'], host: 't.b-integration.ru');
        $this->hit(['p' => 'https://evil.example/']);
        $this->actingAs(User::factory()->create());
        $this->hit(['p' => '/']);

        $this->assertSame(0, PageView::count());
    }

    public function test_clicks_are_counted(): void
    {
        $this->withHeader('User-Agent', self::BROWSER)->postJson('https://b-integration.ru/a/click', ['k' => 'cta', 'l' => 'Поддержка: Обсудить поддержку', 'p' => '/'])->assertNoContent();
        $this->withHeader('User-Agent', self::BROWSER)->postJson('https://b-integration.ru/a/click', ['k' => 'hack', 'p' => '/'])->assertNoContent();

        $this->assertSame(1, Click::count());
        $this->assertSame('cta', Click::sole()->kind);
    }

    public function test_admin_report_shows_sources_pages_clicks_and_leads(): void
    {
        $this->hit(['p' => '/', 'r' => 'https://yandex.ru/search/?text=битрикс24']);
        $this->hit(['p' => '/podderzhka/', 'r' => 'https://b-integration.ru/']);
        $this->hit(['p' => '/podderzhka/', 'q' => '?utm_source=telegram&utm_campaign=start']);
        $this->withHeader('User-Agent', self::BROWSER)->postJson('https://b-integration.ru/a/click', ['k' => 'email', 'l' => 'info@b-integration.ru', 'p' => '/']);
        $this->post('/zayavka', ['name' => 'Иван', 'contact' => 'ivan@example.com', 'consent' => '1', 'r' => 'https://yandex.ru/search/']);

        $this->actingAs(User::factory()->create());
        $this->get('/admin/analitika')->assertOk()
            ->assertSee('Яндекс')
            ->assertSee('start')
            ->assertSee('Поддержка')
            ->assertSee('info@b-integration.ru')
            ->assertSee('Заявки по источникам');
    }

    public function test_counter_endpoints_are_excluded_from_csrf_check(): void
    {
        $excluded = (new \ReflectionProperty(ValidateCsrfToken::class, 'neverVerify'))->getValue();

        $this->assertContains('a/hit', $excluded);
        $this->assertContains('a/click', $excluded);
    }
}
