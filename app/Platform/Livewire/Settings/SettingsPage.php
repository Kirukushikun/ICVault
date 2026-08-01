<?php

namespace App\Platform\Livewire\Settings;

use App\Platform\Support\Tool;
use App\Platform\Support\ToolRegistry;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Platform-level settings: the account itself, plus a jumping-off point to each
 * installed tool's own settings. Anything tool-specific belongs to that tool,
 * so nothing here touches a tool's tables.
 */
#[Layout('layouts.app', ['title' => 'ICVault — Settings'])]
class SettingsPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public bool $editing = false;

    public function mount(): void
    {
        $this->fillFromUser();
    }

    public function startEdit(): void
    {
        $this->editing = true;
    }

    public function cancelEdit(): void
    {
        $this->resetErrorBag();
        $this->fillFromUser();
        $this->editing = false;
    }

    public function save(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            // Blank means "leave the current password alone".
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (filled($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        $this->password = '';
        $this->editing = false;

        $this->dispatch('toast', message: 'Account updated.');
    }

    private function fillFromUser(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
    }

    public function render(ToolRegistry $registry)
    {
        return view('platform.settings.settings-page', [
            'toolsWithSettings' => $registry->enabled()->filter(fn (Tool $tool) => $tool->hasSettings()),
        ]);
    }
}
