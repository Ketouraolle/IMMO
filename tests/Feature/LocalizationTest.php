<?php

namespace Tests\Feature;

use App\Livewire\NotificationBell;
use App\Models\Contract;
use App\Models\Lease;
use App\Models\Property;
use App\Models\User;
use App\Models\VisitRequest;
use App\Notifications\VisitRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_translatable_string_has_a_french_translation(): void
    {
        $french = json_decode(file_get_contents(lang_path('fr.json')), true);
        $missing = [];

        $files = array_merge(
            glob(app_path('{,*/,*/*/,*/*/*/}*.php'), GLOB_BRACE),
            glob(resource_path('views/{,*/,*/*/}*.blade.php'), GLOB_BRACE),
        );

        foreach ($files as $file) {
            preg_match_all('/(?:__|trans_choice)\(\s*(\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")/', file_get_contents($file), $matches);

            foreach ($matches[1] as $literal) {
                $key = $literal[0] === "'"
                    ? str_replace(["\\'", '\\\\'], ["'", '\\'], substr($literal, 1, -1))
                    : stripcslashes(substr($literal, 1, -1));

                if (! array_key_exists($key, $french)) {
                    $missing[] = $key.'  ('.basename($file).')';
                }
            }
        }

        $this->assertSame([], array_values(array_unique($missing)), 'Missing French translations');
    }

    public function test_switching_language_updates_session_account_and_pages(): void
    {
        $this->get(route('public.properties.index'))->assertOk()->assertSee('Find your next home');

        $this->from(route('public.properties.index'))
            ->get(route('locale.switch', 'fr'))
            ->assertRedirect(route('public.properties.index'))
            ->assertSessionHas('locale', 'fr');

        $this->get(route('public.properties.index'))
            ->assertOk()
            ->assertSee('Trouvez votre prochain logement')
            ->assertSee('lang="fr"', false);

        $tenant = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($tenant)->get(route('locale.switch', 'fr'));
        $this->assertSame('fr', $tenant->fresh()->locale);

        $this->get(route('locale.switch', 'de'))->assertNotFound();
    }

    public function test_browser_language_is_used_on_first_visit(): void
    {
        $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9,en;q=0.5')
            ->get(route('login'))
            ->assertOk()
            ->assertSee('Se connecter');
    }

    public function test_signed_in_pages_render_in_french_with_theme_toggle(): void
    {
        $admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin', 'locale' => 'fr']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord')
            ->assertSee('Demandes de visite')
            ->assertSee('Basculer entre mode clair et sombre')
            ->assertSee('data-bs-theme', false);
    }

    public function test_contract_is_written_in_the_tenants_language(): void
    {
        $admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin', 'locale' => 'en']);
        $tenant = User::factory()->create(['role' => 'tenant', 'locale' => 'fr']);
        $owner = User::factory()->create(['role' => 'owner']);
        $property = Property::create([
            'owner_id' => $owner->id, 'name' => 'Résidence Test', 'address' => '1 Rue Test', 'type' => 'apartment',
            'monthly_rent' => 250000, 'status' => 'occupied',
        ]);
        $lease = Lease::create([
            'property_id' => $property->id, 'tenant_id' => $tenant->id, 'start_date' => '2026-10-01',
            'rent_amount' => 250000, 'billing_cycle' => 'monthly', 'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('contracts.store', $lease), ['send' => 1]);

        $body = Contract::sole()->body;
        $this->assertStringContainsString("Contrat de bail d'habitation", html_entity_decode($body, ENT_QUOTES));
        $this->assertStringContainsString('1 octobre 2026', $body);

        // The admin keeps working in English
        $this->actingAs($admin)->get(route('dashboard'))->assertSee('Dashboard');
    }

    public function test_notifications_are_shown_in_the_readers_language(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin', 'locale' => 'fr']);
        $property = Property::create([
            'owner_id' => $owner->id, 'name' => 'Villa Test', 'address' => '1 Rue Test', 'type' => 'house',
            'monthly_rent' => 150000, 'visit_fee' => 5000, 'status' => 'vacant',
        ]);
        $visit = VisitRequest::create([
            'property_id' => $property->id, 'name' => 'Jean Ondoa', 'email' => 'jean@example.com', 'phone' => '699000000',
            'status' => 'new', 'visit_date' => '2026-10-02', 'visit_time' => '10:30', 'fee_amount' => 5000,
            'payment_option' => 'pay_now', 'payment_status' => 'paid', 'payment_method' => 'orange_money',
        ]);
        $admin->notify(new VisitRequested($visit));

        app()->setLocale('fr');
        \Illuminate\Support\Carbon::setLocale('fr');

        Livewire::actingAs($admin)
            ->test(NotificationBell::class)
            ->assertSee('Nouvelle demande de visite')
            ->assertSee('Jean Ondoa souhaite visiter Villa Test le ven. 02 oct. à 10:30. Frais payés par Orange Money.');
    }
}
