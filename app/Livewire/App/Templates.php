<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\Template;
use App\Services\Website\WebsiteService;
use App\Support\Exceptions\QuotaExceededException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Templates extends BaseComponent
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $category = 'all';

    #[Url(except: false)]
    public bool $freeOnly = false;

    public ?int $previewing = null;

    public function updatedSearch(): void { $this->resetPage(); }

    public function updatedCategory(): void { $this->resetPage(); }

    public function useTemplate(int $id): void
    {
        $template = Template::active()->findOrFail($id);

        if ($template->is_premium && ! $this->user()->activePlan()?->allowsPremiumTemplates()) {
            $this->notifyError('This is a premium template — upgrade your plan to use it.');

            return;
        }

        try {
            $website = app(WebsiteService::class)->createFromTemplate($this->user(), $template);
        } catch (QuotaExceededException $e) {
            $this->notifyError($e->getMessage());

            return;
        }

        $this->notifySuccess("\"{$template->name}\" applied — opening the builder.");
        $this->redirectRoute('builder.edit', ['website' => $website->id], navigate: true);
    }

    public function render()
    {
        $templates = Template::active()
            ->search($this->search)
            ->category($this->category)
            ->when($this->freeOnly, fn ($q) => $q->where('is_premium', false))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12);

        return view('livewire.app.templates', [
            'templates' => $templates,
            'categories' => ['all' => 'All templates'] + config('platform.template_categories'),
            'preview' => $this->previewing ? Template::find($this->previewing) : null,
        ])->layoutData($this->layoutData('Templates'));
    }
}
