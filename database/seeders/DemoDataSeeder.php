<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\ActivityLog;
use App\Models\AiGeneration;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\SecurityLog;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Template;
use App\Models\Theme;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteAnalytic;
use App\Services\Website\WebsiteService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates a realistic, fully populated demo environment so every dashboard,
 * chart and table has meaningful data on first boot.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $plans = Plan::query()->get()->keyBy('slug');

        $superAdmin = $this->makeUser('Alexandra Reed', 'super@aurorabuild.test', RoleType::SuperAdmin, [
            'company' => 'AuroraBuild', 'country' => 'GB', 'two_factor_enabled' => true,
        ]);

        $admin = $this->makeUser('Marcus Hale', 'admin@aurorabuild.test', RoleType::Admin, [
            'company' => 'AuroraBuild', 'country' => 'US',
        ]);

        $this->makeUser('Priya Raman', 'admin2@aurorabuild.test', RoleType::Admin, [
            'company' => 'AuroraBuild', 'country' => 'IN',
        ]);

        $demo = $this->makeUser('Jordan Wells', 'user@aurorabuild.test', RoleType::User, [
            'company' => 'Wells Studio', 'country' => 'CA',
        ]);

        $customers = collect([
            ['Sofia Marchetti', 'sofia@lumenlabs.test', 'Lumen Labs', 'IT'],
            ['Tom Bekker', 'tom@fieldhouse.test', 'Fieldhouse', 'NL'],
            ['Amara Osei', 'amara@northwind.test', 'Northwind', 'GH'],
            ['Julian Reyes', 'julian@studiokite.test', 'Studio Kite', 'ES'],
            ['Hana Suzuki', 'hana@arcadia.test', 'Arcadia', 'JP'],
            ['Liam Murphy', 'liam@vertex.test', 'Vertex', 'IE'],
            ['Nora Haddad', 'nora@atlas.test', 'Atlas Co', 'AE'],
            ['Felix Braun', 'felix@meridian.test', 'Meridian', 'DE'],
            ['Chloe Dubois', 'chloe@peony.test', 'Peony', 'FR'],
            ['Ethan Clarke', 'ethan@harbour.test', 'Harbour', 'AU'],
        ])->map(fn (array $c) => $this->makeUser($c[0], $c[1], RoleType::User, [
            'company' => $c[2], 'country' => $c[3],
        ]));

        $allCustomers = $customers->prepend($demo);

        // ---- subscriptions & invoices ---------------------------------
        $planCycle = ['professional', 'starter', 'business', 'free', 'professional', 'starter'];

        foreach ($allCustomers->values() as $i => $customer) {
            $plan = $plans[$planCycle[$i % count($planCycle)]] ?? $plans['free'];

            if ($plan->slug === 'free') {
                continue;
            }

            $status = match ($i % 7) {
                5 => 'trialing',
                6 => 'past_due',
                default => 'active',
            };

            $sub = Subscription::create([
                'user_id' => $customer->id,
                'plan_id' => $plan->id,
                'status' => $status,
                'billing_cycle' => $i % 3 === 0 ? 'yearly' : 'monthly',
                'amount' => $i % 3 === 0 ? $plan->price_yearly : $plan->price_monthly,
                'currency' => 'USD',
                'gateway' => 'stripe',
                'gateway_subscription_id' => 'sub_'.Str::lower(Str::random(14)),
                'trial_ends_at' => $status === 'trialing' ? now()->addDays(9) : null,
                'starts_at' => now()->subMonths(($i % 8) + 1),
                'ends_at' => now()->addMonths(1),
            ]);

            // historical invoices for revenue charts
            for ($m = min(11, ($i % 8) + 1); $m >= 0; $m--) {
                $paidAt = now()->subMonths($m)->subDays($i % 20);
                $amount = (float) $sub->amount;

                Invoice::create([
                    'number' => 'INV-'.$paidAt->format('Ym').'-'.str_pad((string) (($i * 13 + $m) % 99999), 5, '0', STR_PAD_LEFT).Str::upper(Str::random(2)),
                    'user_id' => $customer->id,
                    'subscription_id' => $sub->id,
                    'subtotal' => $amount,
                    'discount' => $m === 0 && $i % 4 === 0 ? round($amount * 0.2, 2) : 0,
                    'tax' => 0,
                    'total' => $m === 0 && $i % 4 === 0 ? round($amount * 0.8, 2) : $amount,
                    'currency' => 'USD',
                    'status' => $m === 0 && $status === 'past_due' ? 'open' : 'paid',
                    'gateway' => 'stripe',
                    'gateway_payment_id' => 'pi_'.Str::lower(Str::random(16)),
                    'line_items' => [['description' => $plan->name.' plan', 'amount' => $amount]],
                    'paid_at' => $paidAt,
                    'created_at' => $paidAt,
                    'updated_at' => $paidAt,
                ]);
            }
        }

        // ---- coupons ---------------------------------------------------
        foreach ([
            ['LAUNCH25', 'percentage', 25, 'Launch promotion — 25% off any plan'],
            ['WELCOME10', 'percentage', 10, 'New customer welcome discount'],
            ['AGENCY50', 'fixed', 50, '$50 off the Business plan for agencies'],
            ['BLACKFRIDAY', 'percentage', 40, 'Black Friday special'],
        ] as $i => [$code, $type, $value, $desc]) {
            Coupon::updateOrCreate(['code' => $code], [
                'description' => $desc,
                'type' => $type,
                'value' => $value,
                'max_redemptions' => 500,
                'redemptions' => 20 + $i * 37,
                'max_per_user' => 1,
                'starts_at' => now()->subMonth(),
                'expires_at' => now()->addMonths(3),
                'is_active' => $i !== 3,
            ]);
        }

        // ---- websites --------------------------------------------------
        $service = app(WebsiteService::class);
        $templates = Template::query()->get();
        $themes = Theme::query()->where('is_global', true)->get();

        $siteDefs = [
            ['Wells Studio', 'agency', 'published'],
            ['Northbeam Analytics', 'saas', 'published'],
            ['Fern & Flame', 'restaurant', 'draft'],
            ['Harbour Realty', 'realestate', 'published'],
            ['Peak Fitness', 'fitness', 'published'],
            ['Lens & Light', 'portfolio', 'draft'],
            ['Bright Academy', 'education', 'published'],
            ['Open Hands', 'nonprofit', 'published'],
            ['Vault Finance', 'finance', 'draft'],
            ['Wander Travel', 'travel', 'published'],
            ['Marketplace Co', 'ecommerce', 'published'],
            ['Calm Clinic', 'health', 'published'],
        ];

        foreach ($siteDefs as $i => [$name, $category, $status]) {
            $owner = $i < 3 ? $demo : $allCustomers[($i % $allCustomers->count())];
            $template = $templates[$i % max(1, $templates->count())] ?? null;

            $website = $template
                ? $service->createFromTemplate($owner, $template, ['name' => $name, 'category' => $category])
                : $service->create($owner, ['name' => $name, 'category' => $category]);

            $website->update([
                'theme_id' => $themes[$i % max(1, $themes->count())]->id ?? null,
                'description' => "The official website for {$name}.",
                'views_count' => random_int(400, 24000),
                'created_at' => now()->subDays(120 - $i * 8),
            ]);

            if ($status === 'published') {
                $service->publish($website);
                $website->update([
                    'published_at' => now()->subDays(100 - $i * 7),
                    'custom_domain' => $i % 3 === 0 ? Str::slug($name).'.com' : null,
                    'domain_status' => $i % 3 === 0 ? 'verified' : 'unverified',
                ]);
            }

            $this->seedAnalytics($website);
            $this->seedForm($website);
            $this->seedBlog($website, $owner);
        }

        // ---- support tickets -------------------------------------------
        $subjects = [
            ['Custom domain not verifying', 'domains', 'high', 'open'],
            ['How do I export my site?', 'billing', 'low', 'answered'],
            ['AI generator returned an error', 'technical', 'urgent', 'open'],
            ['Request: more font options', 'feature', 'low', 'pending'],
            ['Invoice needs a VAT number', 'billing', 'medium', 'resolved'],
            ['Image uploads failing over 5MB', 'technical', 'high', 'answered'],
            ['Can I transfer a site to another account?', 'general', 'medium', 'resolved'],
            ['Page loads slowly on mobile', 'technical', 'medium', 'open'],
        ];

        foreach ($subjects as $i => [$subject, $category, $priority, $status]) {
            $ticket = SupportTicket::create([
                'user_id' => $allCustomers[$i % $allCustomers->count()]->id,
                'assigned_to' => $i % 2 === 0 ? $admin->id : null,
                'subject' => $subject,
                'message' => "Hi team,\n\n{$subject}. Could you take a look when you get a moment?\n\nThanks!",
                'category' => $category,
                'priority' => $priority,
                'status' => $status,
                'created_at' => now()->subDays(20 - $i),
                'last_reply_at' => now()->subDays(max(0, 18 - $i)),
                'resolved_at' => $status === 'resolved' ? now()->subDays(max(0, 15 - $i)) : null,
            ]);

            if (in_array($status, ['answered', 'resolved'], true)) {
                TicketReply::create([
                    'support_ticket_id' => $ticket->id,
                    'user_id' => $admin->id,
                    'message' => "Thanks for reaching out — I've taken a look and this should now be sorted. Let me know if anything still seems off!",
                    'is_staff_reply' => true,
                    'created_at' => $ticket->created_at->addHours(4),
                ]);
            }
        }

        // ---- contact messages ------------------------------------------
        foreach ([
            ['Grace Lin', 'grace@example.com', 'Partnership enquiry', 'We would love to explore a reseller partnership.'],
            ['Omar Farouk', 'omar@example.com', 'Enterprise pricing', 'Do you offer volume pricing for 50+ seats?'],
            ['Nina Petrova', 'nina@example.com', 'Press request', 'Writing a piece on no-code tools — can we chat?'],
            ['Sam Whitaker', 'sam@example.com', 'Bug report', 'The pricing page renders oddly on Safari 17.'],
            ['Ines Costa', 'ines@example.com', 'Feature request', 'Any plans for a native e-commerce module?'],
        ] as $i => [$name, $email, $subject, $message]) {
            ContactMessage::create([
                'name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message,
                'status' => $i < 2 ? 'new' : 'replied',
                'is_read' => $i >= 2,
                'ip_address' => '203.0.113.'.(10 + $i),
                'created_at' => now()->subDays($i * 3),
            ]);
        }

        // ---- AI generation history --------------------------------------
        $types = ['website', 'landing', 'content', 'blog', 'palette', 'image', 'design'];

        foreach (range(0, 39) as $i) {
            $user = $allCustomers[$i % $allCustomers->count()];
            $type = $types[$i % count($types)];

            AiGeneration::create([
                'user_id' => $user->id,
                'type' => $type,
                'provider' => 'simulated',
                'model' => 'aurora-compose-1',
                'prompt' => 'Generate a '.$type.' for a modern business',
                'result' => json_encode(['ok' => true]),
                'status' => $i % 17 === 0 ? 'failed' : 'completed',
                'tokens_used' => random_int(400, 4200),
                'credits_used' => $i % 17 === 0 ? 0 : random_int(1, 5),
                'duration_ms' => random_int(280, 3400),
                'created_at' => now()->subDays(random_int(0, 45))->subHours(random_int(0, 23)),
            ]);
        }

        // ---- logs --------------------------------------------------------
        $events = [
            ['created', 'Website "Wells Studio" was created', 'info'],
            ['updated', 'Plan "Professional" was updated', 'info'],
            ['published', 'Website "Northbeam Analytics" was published', 'success'],
            ['deleted', 'Template "Legacy Landing" was deleted', 'warning'],
            ['login', 'User signed in', 'info'],
        ];

        foreach (range(0, 49) as $i) {
            [$event, $description, $severity] = $events[$i % count($events)];

            ActivityLog::create([
                'user_id' => $allCustomers[$i % $allCustomers->count()]->id,
                'event' => $event,
                'description' => $description,
                'severity' => $severity,
                'ip_address' => '198.51.100.'.($i % 250),
                'created_at' => now()->subHours($i * 5),
            ]);
        }

        foreach (range(0, 29) as $i) {
            SecurityLog::create([
                'user_id' => $i % 3 === 0 ? null : $allCustomers[$i % $allCustomers->count()]->id,
                'event' => ['login_success', 'login_failed', 'password_reset', 'two_factor_enabled', 'blocked_ip'][$i % 5],
                'email' => $allCustomers[$i % $allCustomers->count()]->email,
                'ip_address' => '203.0.113.'.($i % 250),
                'level' => ['info', 'warning', 'critical', 'success'][$i % 4],
                'location' => ['London, UK', 'Toronto, CA', 'Berlin, DE', 'Tokyo, JP'][$i % 4],
                'created_at' => now()->subHours($i * 7),
            ]);
        }
    }

    /* ------------------------------------------------------------------ */

    private function makeUser(string $name, string $email, RoleType $role, array $extra = []): User
    {
        $user = User::updateOrCreate(['email' => $email], array_merge([
            'name' => $name,
            'username' => Str::slug($name),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'status' => 'active',
            'last_login_at' => now()->subHours(random_int(1, 72)),
            'last_login_ip' => '198.51.100.'.random_int(2, 250),
        ], $extra));

        $user->syncRoles([$role->value]);

        return $user;
    }

    private function seedAnalytics(Website $website): void
    {
        $base = random_int(40, 400);

        for ($d = 59; $d >= 0; $d--) {
            $date = now()->subDays($d);
            $weekend = in_array($date->dayOfWeek, [0, 6], true);
            $views = (int) max(5, $base * (1 + sin($d / 7) * 0.35) * ($weekend ? 0.6 : 1) * (1 + (60 - $d) / 120));

            WebsiteAnalytic::updateOrCreate(
                ['website_id' => $website->id, 'page_id' => null, 'date' => $date->toDateString()],
                [
                    'views' => $views,
                    'unique_visitors' => (int) ($views * 0.68),
                    'sessions' => (int) ($views * 0.78),
                    'bounce_rate' => round(32 + (($d * 7) % 28), 2),
                    'avg_duration' => random_int(45, 260),
                    'referrers' => ['google' => (int) ($views * 0.44), 'direct' => (int) ($views * 0.31), 'social' => (int) ($views * 0.16), 'referral' => (int) ($views * 0.09)],
                    'devices' => ['desktop' => (int) ($views * 0.54), 'mobile' => (int) ($views * 0.39), 'tablet' => (int) ($views * 0.07)],
                    'countries' => ['US' => (int) ($views * 0.34), 'GB' => (int) ($views * 0.18), 'DE' => (int) ($views * 0.12), 'other' => (int) ($views * 0.36)],
                ],
            );
        }
    }

    private function seedForm(Website $website): void
    {
        $form = Form::create([
            'website_id' => $website->id,
            'name' => 'Contact form',
            'fields' => [
                ['name' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
            ],
            'notify_email' => $website->user?->email,
            'success_message' => 'Thanks! We will be in touch shortly.',
        ]);

        $names = ['Ava Thompson', 'Noah Patel', 'Mia Rossi', 'Leo Andersen', 'Zara Khan'];

        foreach (range(0, random_int(2, 5)) as $i) {
            FormSubmission::create([
                'form_id' => $form->id,
                'data' => [
                    'name' => $names[$i % count($names)],
                    'email' => Str::slug($names[$i % count($names)], '.').'@example.com',
                    'message' => 'Hi, I would love to learn more about what you offer. Could you send over some details?',
                ],
                'ip_address' => '198.51.100.'.random_int(2, 250),
                'is_read' => $i > 1,
                'created_at' => now()->subDays($i * 2),
            ]);
        }

        $form->update(['submissions_count' => $form->submissions()->count()]);
    }

    private function seedBlog(Website $website, User $owner): void
    {
        $category = BlogCategory::create([
            'website_id' => $website->id,
            'name' => 'Insights',
            'description' => 'Thoughts on design, growth and craft.',
            'color' => 'indigo',
        ]);

        $posts = [
            ['Designing for clarity, not decoration', 'Restraint is what separates a professional site from a busy one.'],
            ['Why performance is a design decision', 'Every kilobyte you ship is a promise you make to your visitors.'],
            ['A practical framework for landing page copy', 'Lead with the outcome, prove it, then remove every obstacle.'],
        ];

        foreach ($posts as $i => [$title, $excerpt]) {
            BlogPost::create([
                'website_id' => $website->id,
                'user_id' => $owner->id,
                'blog_category_id' => $category->id,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => "<p>{$excerpt}</p><h2>The short version</h2><p>Start with the reader's problem, describe the outcome in concrete terms, and remove anything that does not move them closer to a decision.</p><p>Everything else — the animation, the gradients, the clever wording — is secondary to that.</p>",
                'status' => $i === 2 ? 'draft' : 'published',
                'is_featured' => $i === 0,
                'tags' => ['design', 'strategy'],
                'published_at' => $i === 2 ? null : now()->subDays(($i + 1) * 9),
                'views_count' => random_int(80, 3200),
            ]);
        }
    }
}
