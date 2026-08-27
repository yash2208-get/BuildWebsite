<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Enums\AiGenerationType;
use App\Livewire\BaseComponent;
use App\Models\AiGeneration;
use App\Models\BlogPost;
use App\Models\Website;
use App\Services\Ai\AiManager;
use App\Services\Website\AiWebsiteBuilder;
use App\Support\Exceptions\QuotaExceededException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Throwable;

#[Layout('layouts.app')]
class AiStudio extends BaseComponent
{
    #[Url(except: 'website')]
    public string $tool = 'website';

    public string $prompt = '';

    public string $industry = 'saas';

    public string $tone = 'professional';

    public string $businessName = '';

    public string $colorScheme = '';

    public string $contentType = 'paragraph';

    public ?int $targetWebsite = null;

    public ?array $result = null;

    public bool $generating = false;

    protected function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:8', 'max:'.config('ai.limits.prompt_max_chars', 2000)],
            'industry' => ['required', 'string'],
            'tone' => ['required', 'string'],
            'businessName' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function setTool(string $tool): void
    {
        $this->tool = $tool;
        $this->result = null;
        $this->resetValidation();
    }

    public function generate(): void
    {
        $this->validate();
        $this->generating = true;
        $this->result = null;

        $user = $this->user();

        $options = [
            'industry' => $this->industry,
            'tone' => $this->tone,
            'business_name' => $this->businessName,
            'color_scheme' => $this->colorScheme ?: null,
            'content_type' => $this->contentType,
        ];

        try {
            match ($this->tool) {
                'website' => $this->generateWebsite($options),
                'landing' => $this->generateLanding($options),
                'blog' => $this->generateBlog($options),
                default => $this->generateSimple($options),
            };
        } catch (QuotaExceededException $e) {
            $this->notifyError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->notifyError('Generation failed: '.$e->getMessage());
        } finally {
            $this->generating = false;
        }
    }

    private function generateWebsite(array $options): void
    {
        $website = app(AiWebsiteBuilder::class)->buildWebsite($this->user(), $this->prompt, $options);

        $this->notifySuccess("\"{$website->name}\" generated with {$website->pages_count} pages!");
        $this->redirectRoute('builder.edit', ['website' => $website->id], navigate: true);
    }

    private function generateLanding(array $options): void
    {
        $website = $this->targetWebsite
            ? Website::where('user_id', $this->user()->id)->findOrFail($this->targetWebsite)
            : $this->user()->websites()->latest()->first();

        if (! $website) {
            $this->notifyError('Create a website first, then generate landing pages inside it.');

            return;
        }

        $page = app(AiWebsiteBuilder::class)->buildLandingPage($this->user(), $website, $this->prompt, $options);

        $this->notifySuccess('Landing page generated.');
        $this->redirectRoute('builder.page', ['website' => $website->id, 'page' => $page->id], navigate: true);
    }

    private function generateBlog(array $options): void
    {
        $generation = app(AiWebsiteBuilder::class)->buildBlogPost(
            $this->user(),
            $this->targetWebsite ? Website::find($this->targetWebsite) : null,
            $this->prompt,
            $options,
        );

        $this->result = $generation->decodedResult() + ['__generation_id' => $generation->id];
        $this->notifySuccess('Article drafted — review it below.');
    }

    private function generateSimple(array $options): void
    {
        $type = match ($this->tool) {
            'content' => AiGenerationType::Content,
            'palette' => AiGenerationType::Palette,
            'images' => AiGenerationType::ImageSuggestion,
            'design' => AiGenerationType::DesignSuggestion,
            default => AiGenerationType::Content,
        };

        $generation = app(AiManager::class)->generateFor($this->user(), $type, $this->prompt, $options);

        $this->result = $generation->decodedResult();
        $this->notifySuccess('Done! Results are ready below.');
    }

    public function saveBlogPost(): void
    {
        if (! $this->result) {
            return;
        }

        $website = $this->targetWebsite
            ? Website::where('user_id', $this->user()->id)->find($this->targetWebsite)
            : $this->user()->websites()->latest()->first();

        BlogPost::create([
            'website_id' => $website?->id,
            'user_id' => $this->user()->id,
            'title' => $this->result['title'] ?? 'Untitled article',
            'excerpt' => $this->result['excerpt'] ?? null,
            'content' => $this->result['content'] ?? '',
            'tags' => $this->result['tags'] ?? [],
            'seo' => $this->result['seo'] ?? [],
            'status' => 'draft',
            'ai_generated' => true,
        ]);

        $this->notifySuccess('Article saved to your blog as a draft.');
        $this->result = null;
    }

    public function render()
    {
        return view('livewire.app.ai-studio', [
            'tools' => [
                'website' => ['label' => 'Website Generator', 'icon' => 'sparkles', 'desc' => 'A complete multi-page site from one brief', 'credits' => 5],
                'landing' => ['label' => 'Landing Page', 'icon' => 'rocket', 'desc' => 'A focused, conversion-ready page', 'credits' => 3],
                'blog' => ['label' => 'Blog Writer', 'icon' => 'book', 'desc' => 'Full-length, structured articles', 'credits' => 2],
                'content' => ['label' => 'Content Writer', 'icon' => 'pencil', 'desc' => 'Headlines, taglines, CTAs and copy', 'credits' => 1],
                'palette' => ['label' => 'Colour Palette', 'icon' => 'palette', 'desc' => 'Accessible, on-brand colour systems', 'credits' => 1],
                'images' => ['label' => 'Image Suggestions', 'icon' => 'image', 'desc' => 'Art direction and photo prompts', 'credits' => 1],
                'design' => ['label' => 'Design Review', 'icon' => 'wand', 'desc' => 'Layout, spacing and typography advice', 'credits' => 1],
            ],
            'industries' => config('ai.industries'),
            'tones' => config('ai.tones'),
            'websites' => $this->user()->websites()->orderBy('name')->get(['id', 'name']),
            'history' => AiGeneration::where('user_id', $this->user()->id)->latest()->limit(8)->get(),
            'creditsLeft' => $this->user()->aiCreditsRemaining(),
        ])->layoutData($this->layoutData('AI Studio'));
    }
}
