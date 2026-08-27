<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\Theme;
use App\Models\Website;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Themes extends BaseComponent
{
    public ?int $applyingTo = null;

    public ?int $selectedTheme = null;

    public bool $showBuilder = false;

    public string $themeName = '';
    public string $primary = '#6366f1';
    public string $secondary = '#8b5cf6';
    public string $accent = '#22d3ee';
    public string $background = '#ffffff';
    public string $text = '#0f172a';
    public string $headingFont = 'Inter';
    public string $bodyFont = 'Inter';
    public string $radius = '12px';

    public function applyTheme(int $themeId, int $websiteId): void
    {
        $website = Website::where('user_id', $this->user()->id)->findOrFail($websiteId);
        $this->authorize('update', $website);

        $website->update(['theme_id' => $themeId]);

        $this->applyingTo = null;
        $this->notifySuccess('Theme applied to '.$website->name.'.');
    }

    public function saveCustomTheme(): void
    {
        $data = $this->validate([
            'themeName' => ['required', 'string', 'min:2', 'max:80'],
            'primary' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'text' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        Theme::create([
            'user_id' => $this->user()->id,
            'name' => $data['themeName'],
            'slug' => Str::slug($data['themeName']).'-'.Str::lower(Str::random(4)),
            'description' => 'Custom theme',
            'colors' => [
                'primary' => $this->primary,
                'secondary' => $this->secondary,
                'accent' => $this->accent,
                'background' => $this->background,
                'text' => $this->text,
            ],
            'typography' => [
                'heading_font' => $this->headingFont,
                'body_font' => $this->bodyFont,
            ],
            'settings' => ['radius' => $this->radius],
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->reset('showBuilder', 'themeName');
        $this->notifySuccess('Custom theme created.');
    }

    public function deleteTheme(int $id): void
    {
        $theme = Theme::where('user_id', $this->user()->id)->findOrFail($id);
        $theme->delete();

        $this->notifySuccess('Theme deleted.');
    }

    public function render()
    {
        return view('livewire.app.themes', [
            'systemThemes' => Theme::active()->whereNull('user_id')->orderBy('name')->get(),
            'myThemes' => Theme::where('user_id', $this->user()->id)->latest()->get(),
            'websites' => $this->user()->websites()->orderBy('name')->get(['id', 'name', 'theme_id']),
            'fonts' => config('platform.fonts'),
        ])->layoutData($this->layoutData('Theme Builder'));
    }
}
