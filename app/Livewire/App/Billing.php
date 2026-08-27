<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Services\Billing\SubscriptionService;
use Livewire\Attributes\Layout;
use Throwable;

#[Layout('layouts.app')]
class Billing extends BaseComponent
{
    public string $cycle = 'monthly';

    public string $couponCode = '';

    public ?array $appliedCoupon = null;

    public ?int $confirmingPlan = null;

    public bool $confirmingCancel = false;

    public function applyCoupon(): void
    {
        $this->validate(['couponCode' => ['required', 'string', 'max:40']]);

        $coupon = Coupon::where('code', strtoupper($this->couponCode))->first();

        if (! $coupon || ! $coupon->isValid()) {
            $this->appliedCoupon = null;
            $this->addError('couponCode', 'This coupon code is invalid or has expired.');

            return;
        }

        $this->appliedCoupon = [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'label' => $coupon->discountLabel(),
        ];

        $this->notifySuccess("Coupon {$coupon->code} applied — {$coupon->discountLabel()}.");
    }

    public function removeCoupon(): void
    {
        $this->reset('appliedCoupon', 'couponCode');
    }

    public function subscribe(int $planId): void
    {
        $plan = Plan::active()->findOrFail($planId);

        try {
            app(SubscriptionService::class)->subscribe(
                $this->user(),
                $plan,
                $this->cycle,
                $this->appliedCoupon['code'] ?? null,
            );

            $this->confirmingPlan = null;
            $this->notifySuccess("You are now on the {$plan->name} plan.");
        } catch (Throwable $e) {
            report($e);
            $this->notifyError('We could not process that change: '.$e->getMessage());
        }
    }

    public function cancelSubscription(): void
    {
        $subscription = $this->user()->subscription;

        if (! $subscription) {
            return;
        }

        app(SubscriptionService::class)->cancel($subscription);

        $this->confirmingCancel = false;
        $this->notifySuccess('Subscription cancelled — you keep access until the period ends.');
    }

    public function resumeSubscription(): void
    {
        $subscription = $this->user()->subscriptions()->latest()->first();

        if ($subscription) {
            app(SubscriptionService::class)->resume($subscription);
            $this->notifySuccess('Subscription resumed.');
        }
    }

    public function render()
    {
        $user = $this->user();

        return view('livewire.app.billing', [
            'plans' => Plan::active()->get(),
            'currentPlan' => $user->activePlan(),
            'subscription' => $user->subscription,
            'invoices' => Invoice::where('user_id', $user->id)->latest()->limit(12)->get(),
            'usage' => app(\App\Services\Website\QuotaService::class)->usage($user),
        ])->layoutData($this->layoutData('Billing & Plans'));
    }
}
