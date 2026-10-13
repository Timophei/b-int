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

    public function test_home_follows_support_mockup(): void
    {
        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Ваш человек', 'Чтобы всё работало', 'Обновления и обслуживание', '25 000 ₽',
                'Обсудить поддержку', 'Сложное восстановление', 'Сначала стабильность', 'Как работаем',
                'Регулярные регламентные работы', 'Начнём с вашего портала', 'Берём на поддержку'])
            ->assertSee('/img/logo-mark.png')->assertSee('/img/hero.jpg')
            ->assertSee('data-menu-toggle', false)
            ->assertDontSee('row-title')
            ->assertSee('{"@context":"https://schema.org","@type":"Organization"', false);
    }

    public function test_ai_page_follows_mockup(): void
    {
        $this->get('/ii-sotrudniki/')->assertOk()
            ->assertSeeInOrder(['Нейросети выходят', 'Подобрать ИИ сотрудника', 'Четыре сценария', 'Консультант интернет-магазина',
                'Город чая', 'gorodchaya.ru', 'Внутренний консультант', 'Ваши знания', 'Сделаем и для вас', 'Примет обращения',
                'Какую задачу поручим', 'Обсудить задачу', 'От вашей задачи', 'Подключаем к работе', 'Базовая настройка',
                '50 000 ₽', 'Ежемесячное сопровождение', 'от 5 000 ₽', '40 000 ₽', 'Тариф подбираем', 'Начнём с вашей задачи', 'Обсудить запуск'])
            ->assertSee('/img/hero-ai.jpg')
            ->assertSee('#ic-cart', false)
            ->assertSee('/napisat/?tema=ai');
    }

    public function test_ai_page_hides_blocks_left_empty_in_admin(): void
    {
        $ai = Section::byKey('ai');
        $ai->cases()->delete();
        $ai->plans()->delete();
        $ai->update(['final_title' => null]);

        $this->get('/ii-sotrudniki/')->assertOk()
            ->assertDontSee('Четыре сценария')->assertDontSee('class="setup"', false)->assertDontSee('Обсудить запуск')
            ->assertSee('Сделаем и для вас');
    }

    public function test_menu_has_sections_from_mockup(): void
    {
        $this->get('/')->assertSeeInOrder(['Поддержка', 'ИИ сотрудники', 'Битрикс24', 'О нас', 'https://btxcap.ru', 'Курсы'], false);
        $this->get('/bitrix24/')->assertOk();
        $this->get('/o-nas/')->assertOk();
    }

    public function test_offer_pdf_link_appears_only_with_uploaded_file(): void
    {
        $this->get('/')->assertDontSee('Скачать оффер');

        Section::byKey('support')->plans()->first()->update(['offer_pdf' => 'offers/offer.pdf']);

        $this->get('/')->assertSee('Скачать оффер · PDF')->assertSee('/storage/offers/offer.pdf');
    }

    public function test_each_section_has_its_own_page(): void
    {
        $this->get('/podderzhka/')->assertOk()->assertSee('Обновления и обслуживание')->assertSee('Кому подходит');
        $this->get('/ii-sotrudniki/')->assertOk()->assertSee('Нейросети выходят');
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

        foreach (['/admin', '/admin/sections', '/admin/sections/2/edit', '/admin/sections/3/edit', '/admin/sections/create', '/admin/leads', '/admin/nastroiki', '/admin/analitika'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
