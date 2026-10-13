<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LeadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_lead_is_saved_with_source_and_admin_is_notified(): void
    {
        Mail::fake();

        $this->post('/zayavka', [
            'name' => 'Иван', 'contact' => '+7 900 000-00-00', 'company' => 'ООО Ромашка', 'message' => 'Портал не обновляется',
            'topic' => 'support', 'consent' => '1',
            'from' => '/podderzhka/', 'q' => '?utm_source=vk&utm_campaign=osen', 'r' => 'https://vk.com/feed',
        ])->assertRedirect('/napisat')->assertSessionHas('sent');

        $lead = Lead::sole();
        $this->assertSame('support', $lead->topic);
        $this->assertSame('new', $lead->status);
        $this->assertSame('ВКонтакте', $lead->source_name);
        $this->assertSame('osen', $lead->utm_campaign);
        $this->assertSame('/podderzhka/', $lead->page);

        $this->get('/napisat/')->assertSee('заявка у нас');
    }

    public function test_lead_needs_name_contact_and_consent(): void
    {
        $this->post('/zayavka', ['name' => '', 'contact' => ''])->assertSessionHasErrors(['name', 'contact', 'consent']);
        $this->assertSame(0, Lead::count());
    }

    public function test_honeypot_drops_bots_silently(): void
    {
        $this->post('/zayavka', ['name' => 'bot', 'contact' => 'x', 'consent' => '1', 'website' => 'http://spam'])->assertRedirect();
        $this->assertSame(0, Lead::count());
    }

    public function test_admin_sees_leads_and_new_count(): void
    {
        Lead::create(['name' => 'Пётр', 'contact' => 'petr@example.com', 'topic' => 'ai']);
        $this->actingAs(User::factory()->create());

        $this->get('/admin/leads')->assertOk()->assertSee('Пётр')->assertSee('ИИ сотрудники');
        $this->get('/admin/leads/1')->assertOk()->assertSee('petr@example.com');
    }
}
