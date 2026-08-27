<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Plans extends BaseComponent
{
    public ?int $editing = null;

    public bool $showForm = false;

    public string $name = '';
    public string $description = '';
    public float $monthlyPrice = 0;
    public float $yearlyPrice = 0;
    public int $maxWebsites = 1;
    public int $maxPages = 5;
    public int $maxStorageMb = 100;
    public int $aiCredits = 25;
    public int $trialDays = 0;
    public bool $customDomain = false;
    public bool $removeBranding = false;
    public bool $premiumTemplates = false;
    public bool $prioritySupport = false;
    public bool $isFeatured = false;
    public bool $isActive = true;
    public string $features = '';

    public function editPlan(int $id): void
    {
        $plan = Plan::findOrFail($id);

        $this->editing = $plan->id;
        $this->showForm = true;
        $this->name = $plan->name;
        $this->description = (string) $plan->description;
        $this->monthlyPrice = (float) $plan->price_monthly;
        $this->yearlyPrice = (float) $plan->price_yearly;
        $this->maxWebsites = (int) $plan->max_websites;
        $this->maxPages = (int) $plan->max_pages;
        $this->maxStorageMb = (int) $plan->max_storage_mb;
        $this->aiCredits = (int) $plan->max_ai_credits;
        $this->trialDays = (int) $plan->trial_days;
        $this->customDomain = (bool) $plan->custom_domain;
        $this->removeBranding = (bool) $plan->remove_branding;
        $this->premiumTemplates = (bool) $plan->premium_templates;
        $this->prioritySupport = (bool) $plan->priority_support;
        $this->isFeatured = (bool) $plan->is_featured;
        $this->isActive = (bool) $plan->is_active;
        $this->features = implode("\n", $plan->features ?? []);
    }

    public function newPlan(): void
    {
        $this->reset(array_diff(array_keys(get_object_vars($this)), ['showForm']));
        $this->showForm = true;
        $this->editing = null;
        $this->isActive = true;
    }

    public function save(): void
    {
        $this->authorize('manage-settings');

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'monthlyPrice' => ['required', 'numeric', 'min:0', 'max:100000'],
            'yearlyPrice' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'maxWebsites' => ['required', 'integer', 'min:-1'],
            'maxPages' => ['required', 'integer', 'min:-1'],
            'maxStorageMb' => ['required', 'integer', 'min:-1'],
            'aiCredits' => ['required', 'integer', 'min:-1'],
            'trialDays' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'description' => $data['description'],
            'price_monthly' => $data['monthlyPrice'],
            'price_yearly' => $data['yearlyPrice'],
            'max_websites' => $data['maxWebsites'],
            'max_pages' => $data['maxPages'],
            'max_storage_mb' => $data['maxStorageMb'],
            'max_ai_credits' => $data['aiCredits'],
            'trial_days' => $data['trialDays'],
            'custom_domain' => $this->customDomain,
            'remove_branding' => $this->removeBranding,
            'premium_templates' => $this->premiumTemplates,
            'priority_support' => $this->prioritySupport,
            'is_featured' => $this->isFeatured,
            'is_active' => $this->isActive,
            'features' => array_values(array_filter(array_map('trim', explode("\n", $this->features)))),
        ];

        if ($this->editing) {
            Plan::findOrFail($this->editing)->update($attributes);
            $this->notifySuccess('Plan updated.');
        } else {
            Plan::create($attributes + [
                'slug' => Str::slug($data['name']),
                'currency' => 'USD',
                'sort_order' => (int) Plan::max('sort_order') + 1,
            ]);
            $this->notifySuccess('Plan created.');
        }

        $this->showForm = false;
        $this->editing = null;
    }

    public function toggleActive(int $id): void
    {
        $plan = Plan::findOrFail($id);
        $plan->update(['is_active' => ! $plan->is_active]);

        $this->notifySuccess('Plan '.($plan->is_active ? 'published' : 'hidden').'.');
    }

    public function deletePlan(int $id): void
    {
        $plan = Plan::withCount('subscriptions')->findOrFail($id);

        if ($plan->subscriptions_count > 0) {
            $this->notifyError('This plan has active subscribers — hide it instead of deleting.');

            return;
        }

        $plan->delete();
        $this->notifySuccess('Plan deleted.');
    }

    public function render()
    {
        return view('livewire.super.plans', [
            'plans' => Plan::withCount('subscriptions')->orderBy('sort_order')->get(),
            'revenue' => Subscription::where('subscriptions.status', 'active')
                ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                ->selectRaw('plans.id, SUM(subscriptions.amount) as total')
                ->groupBy('plans.id')->pluck('total', 'id'),
        ])->layoutData($this->layoutData('Billing Plans'));
    }
}
