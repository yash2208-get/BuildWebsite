<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\Component as BlockComponent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class Components extends BaseComponent
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $category = 'all';

    public ?int $previewing = null;

    public function render()
    {
        $components = BlockComponent::active()
            ->search($this->search)
            ->when($this->category !== 'all', fn ($q) => $q->where('category', $this->category))
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return view('livewire.app.components', [
            'groups' => $components,
            'categories' => ['all' => 'All blocks'] + config('platform.component_categories'),
            'preview' => $this->previewing ? BlockComponent::find($this->previewing) : null,
            'total' => BlockComponent::active()->count(),
        ])->layoutData($this->layoutData('Component Library'));
    }
}
