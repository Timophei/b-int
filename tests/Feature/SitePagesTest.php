<?php

namespace Tests\Feature;

use App\Models\Section;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_shows_hero_support_offer_and_section_rows(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Ваш человек')
            ->assertSee('Чтобы всё работало')
            ->assertSee('25 000 ₽')
            ->assertSee('Начнём с вашего портала')
            ->assertSee('/ii-sotrudniki/')
            ->assertSee('https://btxcap.ru');
    }

    public function test_each_section_has_its_own_page(): void
    {
        $this->get('/podderzhka/')->assertOk()->assertSee('Обновления и обслуживание')->assertSee('Кому подходит');
        $this->get('/ii-sotrudniki/')->assertOk()->assertSee('Консультант для клиентов')->assertSee('Обсудим задачу');
        $this->get('/napisat/')->assertOk()->assertSee('name="consent"', false);
        $this->get('/politika/')->assertOk();
    }

    public function test_address_with_or_without_slash_opens_and_external_section_goes_away(): void
    {
        $this->get('/podderzhka')->assertOk()->assertSee('<link rel="canonical" href="https://b-integration.ru/podderzhka/">', false);
        $this->get('/kursy/')->assertRedirect('https://btxcap.ru');
        $this->get('/net-takoi/')->assertNotFound();
    }

    public function test_draft_is_hidden_from_guests_but_visible_to_admin(): void
    {
        Section::byKey('ai')->update(['is_published' => false]);

        $this->get('/ii-sotrudniki/')->assertNotFound();
        $this->get('/')->assertDontSee('/ii-sotrudniki/');

        $this->actingAs(User::factory()->create());
        $this->get('/ii-sotrudniki/')->assertOk()->assertSee('Черновик');
    }

    public function test_new_page_from_admin_gets_its_address(): void
    {
        Section::create(['key' => 'page-cases', 'path' => 'keisy', 'nav_label' => 'Кейсы', 'title' => 'Что сделали.', 'sort' => 5]);

        $this->get('/keisy/')->assertOk()->assertSee('Что сделали');
        $this->get('/')->assertSee('/keisy/');
    }

    public function test_robots_and_sitemap(): void
    {
        $this->get('https://b-integration.ru/robots.txt')->assertSee('Sitemap: https://b-integration.ru/sitemap.xml');
        $this->get('https://t.b-integration.ru/robots.txt')->assertSee('Disallow: /');
        $this->get('/sitemap.xml')->assertOk()->assertSee('https://b-integration.ru/podderzhka/')->assertDontSee('btxcap');
    }

    public function test_www_redirects_to_main_domain(): void
    {
        $this->get('https://www.b-integration.ru/podderzhka/')->assertRedirect('https://b-integration.ru/podderzhka');
    }

    public function test_admin_pages_open(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['/admin', '/admin/sections', '/admin/sections/2/edit', '/admin/sections/create', '/admin/leads', '/admin/nastroiki', '/admin/analitika'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
