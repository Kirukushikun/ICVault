<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Platform\Livewire\Settings\SettingsPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Iverson Craig',
            'email' => 'ic@example.test',
        ]);

        $this->actingAs($this->user);
    }

    /** Brand assets are referenced by every layout; a missing file is silent. */
    public function test_the_brand_logo_and_favicon_exist_in_public(): void
    {
        $this->assertFileExists(public_path('images/ic-logo.png'));
        $this->assertFileExists(public_path('images/ic-icon.ico'));
    }

    public function test_the_shell_renders_the_brand_logo_and_favicon(): void
    {
        $this->get(route('settings'))
            ->assertOk()
            ->assertSee('images/ic-logo.png', false)
            ->assertSee('images/ic-icon.ico', false);
    }

    public function test_it_shows_the_signed_in_users_details(): void
    {
        Livewire::test(SettingsPage::class)
            ->assertSet('name', 'Iverson Craig')
            ->assertSet('email', 'ic@example.test')
            ->assertSet('password', '');
    }

    public function test_it_links_to_each_tool_that_owns_a_settings_page(): void
    {
        Livewire::test(SettingsPage::class)
            ->assertSee('Tool Settings')
            ->assertSee('Quiz Vault');
    }

    /** The platform page must not reach into a tool's data. */
    public function test_it_does_not_render_quiz_owned_controls(): void
    {
        Livewire::test(SettingsPage::class)
            ->assertDontSee('Danger Zone')
            ->assertDontSee('Delete All Questions')
            ->assertDontSee('Backup & Restore');
    }

    public function test_it_updates_the_account_name_and_email(): void
    {
        Livewire::test(SettingsPage::class)
            ->call('startEdit')
            ->set('name', 'IC')
            ->set('email', 'new@example.test')
            ->call('save')
            ->assertSet('editing', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'IC',
            'email' => 'new@example.test',
        ]);
    }

    public function test_a_blank_password_leaves_the_existing_one_untouched(): void
    {
        $original = $this->user->password;

        Livewire::test(SettingsPage::class)
            ->call('startEdit')
            ->set('name', 'IC')
            ->set('password', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($original, $this->user->fresh()->password);
    }

    public function test_a_supplied_password_is_hashed_before_saving(): void
    {
        Livewire::test(SettingsPage::class)
            ->call('startEdit')
            ->set('password', 'a-brand-new-secret')
            ->call('save')
            ->assertSet('password', '')
            ->assertHasNoErrors();

        $stored = $this->user->fresh()->password;

        $this->assertNotSame('a-brand-new-secret', $stored);
        $this->assertTrue(Hash::check('a-brand-new-secret', $stored));
    }

    public function test_it_rejects_a_password_that_is_too_short(): void
    {
        Livewire::test(SettingsPage::class)
            ->call('startEdit')
            ->set('password', 'short')
            ->call('save')
            ->assertHasErrors(['password']);
    }

    public function test_it_rejects_an_email_already_taken_by_another_account(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        Livewire::test(SettingsPage::class)
            ->call('startEdit')
            ->set('email', 'taken@example.test')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_keeping_your_own_email_is_not_a_uniqueness_conflict(): void
    {
        Livewire::test(SettingsPage::class)
            ->call('startEdit')
            ->set('name', 'IC')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_cancelling_discards_unsaved_edits(): void
    {
        Livewire::test(SettingsPage::class)
            ->call('startEdit')
            ->set('name', 'Discarded')
            ->call('cancelEdit')
            ->assertSet('name', 'Iverson Craig')
            ->assertSet('editing', false);

        $this->assertSame('Iverson Craig', $this->user->fresh()->name);
    }
}
