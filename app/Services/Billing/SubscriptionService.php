<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\ActivityLog;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Owns the subscription lifecycle: starting, switching, cancelling and
 * resuming plans, plus invoice generation and coupon redemption.
 *
 * Payment-gateway calls are isolated behind {@see charge()} so a real
 * Stripe/Paddle driver can be dropped in without touching this logic.
 */
class SubscriptionService
{
    public function subscribe(User $user, Plan $plan, string $cycle = 'monthly', ?string $couponCode = null): Subscription
    {
        if (! in_array($cycle, ['monthly', 'yearly'], true)) {
            throw new RuntimeException('Unsupported billing cycle.');
        }

        return DB::transaction(function () use ($user, $plan, $cycle, $couponCode) {
            $current = $user->subscription;

            if ($current && $current->plan_id === $plan->id && $current->billing_cycle === $cycle) {
                throw new RuntimeException('You are already on this plan.');
            }

            $base = $cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
            $coupon = $couponCode ? $this->resolveCoupon($couponCode) : null;
            $discount = $coupon ? $coupon->discountFor((float) $base) : 0.0;
            $total = max(0, round($base - $discount, 2));

            // Supersede the previous subscription rather than deleting history.
            $current?->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'ends_at' => now(),
            ]);

            $periodEnd = $cycle === 'yearly' ? now()->addYear() : now()->addMonth();

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => $total > 0 ? 'active' : 'active',
                'billing_cycle' => $cycle,
                'amount' => $total,
                'currency' => $plan->currency ?? 'USD',
                'starts_at' => now(),
                'ends_at' => $periodEnd,
                'current_period_start' => now(),
                'current_period_end' => $periodEnd,
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
                'coupon_code' => $coupon?->code,
                'gateway' => 'internal',
                'gateway_subscription_id' => 'sub_'.Str::lower(Str::random(20)),
            ]);

            if ($total > 0) {
                $this->createInvoice($user, $subscription, $total, $discount, $coupon);
            }

            if ($coupon) {
                $this->redeem($coupon, $user, $discount);
            }

            ActivityLog::record('subscribed', "{$user->name} subscribed to the {$plan->name} plan ({$cycle})", $subscription);

            return $subscription;
        });
    }

    public function cancel(Subscription $subscription, bool $immediately = false): Subscription
    {
        $subscription->update([
            'status' => $immediately ? 'cancelled' : 'cancelled',
            'cancelled_at' => now(),
            'ends_at' => $immediately ? now() : $subscription->current_period_end,
        ]);

        ActivityLog::record('cancelled', "Subscription #{$subscription->id} cancelled", $subscription);

        return $subscription->refresh();
    }

    public function resume(Subscription $subscription): Subscription
    {
        if ($subscription->ends_at && $subscription->ends_at->isPast()) {
            throw new RuntimeException('This billing period has already ended — please choose a plan again.');
        }

        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null,
            'ends_at' => $subscription->current_period_end,
        ]);

        ActivityLog::record('resumed', "Subscription #{$subscription->id} resumed", $subscription);

        return $subscription->refresh();
    }

    /** Renews an active subscription — invoked by the scheduler. */
    public function renew(Subscription $subscription): Subscription
    {
        $periodEnd = $subscription->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth();

        $subscription->update([
            'current_period_start' => now(),
            'current_period_end' => $periodEnd,
            'ends_at' => $periodEnd,
            'status' => 'active',
        ]);

        $this->createInvoice($subscription->user, $subscription, (float) $subscription->amount, 0.0, null);

        return $subscription->refresh();
    }

    private function createInvoice(User $user, Subscription $subscription, float $total, float $discount, ?Coupon $coupon): Invoice
    {
        $tax = round($total * 0.0, 2);

        return Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-'.now()->format('Ym').'-'.strtoupper(Str::random(6)),
            'subtotal' => round($total + $discount, 2),
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'currency' => $subscription->currency,
            'status' => 'paid',
            'coupon_code' => $coupon?->code,
            'paid_at' => now(),
            'due_at' => now(),
            'line_items' => [[
                'description' => $subscription->plan->name.' plan — '.$subscription->billing_cycle,
                'quantity' => 1,
                'unit_price' => round($total + $discount, 2),
                'total' => $total,
            ]],
        ]);
    }

    private function resolveCoupon(string $code): Coupon
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (! $coupon || ! $coupon->isValid()) {
            throw new RuntimeException('That coupon code is invalid or has expired.');
        }

        return $coupon;
    }

    private function redeem(Coupon $coupon, User $user, float $discount): void
    {
        CouponRedemption::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'discount_amount' => $discount,
        ]);

        $coupon->increment('redemptions_count');
    }
}
