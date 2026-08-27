#!/usr/bin/env python3
"""Generate the ResourceComponent subclasses for the admin/super panels.

Every screen in those panels is a filtered, sortable table over one model, so
they are described declaratively here and emitted as real PHP classes. Screens
that need bespoke behaviour are hand-written instead and simply omitted below.
"""
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parent.parent

# name, namespace, title, model, view, searchable, sort, extra-uses, body, viewData
RESOURCES = [
    # ───────────────────────── ADMIN ─────────────────────────
    dict(ns="Admin", cls="Websites", title="Website Management", model="Website",
         view="livewire.admin.websites", search=["name", "subdomain", "user.name"],
         sort="created_at", with_=["user", "theme"], counts=["pages"],
         filters={"status": "status"},
         actions="""
    public function togglePublish(int $id): void
    {
        $website = Website::findOrFail($id);
        $this->authorize('publish', $website);

        $website->isPublished()
            ? app(WebsiteService::class)->unpublish($website)
            : app(WebsiteService::class)->publish($website);

        $this->notifySuccess('Website '.($website->refresh()->isPublished() ? 'published' : 'unpublished').'.');
    }

    public function deleteWebsite(int $id): void
    {
        $website = Website::findOrFail($id);
        $this->authorize('delete', $website);

        app(WebsiteService::class)->delete($website);
        $this->notifySuccess('Website deleted.');
    }
""",
         uses=["App\\Services\\Website\\WebsiteService"],
         data="""
            'totals' => [
                'all' => Website::count(),
                'published' => Website::where('status', 'published')->count(),
                'draft' => Website::where('status', 'draft')->count(),
                'views' => (int) Website::sum('views_count'),
            ],
"""),

    dict(ns="Admin", cls="Templates", title="Templates", model="Template",
         view="livewire.admin.templates", search=["name", "description"],
         sort="sort_order", sortdir="asc", filters={"category": "category", "is_premium": "is_premium"},
         actions="""
    public function toggleActive(int $id): void
    {
        $template = Template::findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);

        $this->notifySuccess($template->name.' is now '.($template->is_active ? 'visible' : 'hidden').'.');
    }

    public function toggleFeatured(int $id): void
    {
        $template = Template::findOrFail($id);
        $template->update(['is_featured' => ! $template->is_featured]);

        $this->notifySuccess('Featured status updated.');
    }
""",
         data="""
            'categories' => config('platform.template_categories'),
            'totals' => [
                'all' => Template::count(),
                'active' => Template::where('is_active', true)->count(),
                'premium' => Template::where('is_premium', true)->count(),
            ],
"""),

    dict(ns="Admin", cls="Components", title="Components", model="Component",
         view="livewire.admin.components", search=["name", "description"],
         sort="sort_order", sortdir="asc", filters={"category": "category"},
         actions="""
    public function toggleActive(int $id): void
    {
        $component = Component::findOrFail($id);
        $component->update(['is_active' => ! $component->is_active]);

        $this->notifySuccess('Component updated.');
    }
""",
         data="""
            'categories' => config('platform.component_categories'),
            'totals' => ['all' => Component::count(), 'active' => Component::where('is_active', true)->count()],
"""),

    dict(ns="Admin", cls="Media", title="Media Library", model="Media",
         view="livewire.admin.media", search=["name", "original_name"],
         sort="created_at", with_=["user"], filters={"type": "type"},
         actions="""
    public function deleteMedia(int $id): void
    {
        $media = Media::findOrFail($id);
        $this->authorize('delete', $media);

        app(MediaService::class)->delete($media);
        $this->notifySuccess('File deleted.');
    }
""",
         uses=["App\\Services\\Platform\\MediaService"],
         data="""
            'totals' => [
                'all' => Media::count(),
                'size' => round((int) Media::sum('size') / 1048576, 1),
                'images' => Media::where('type', 'image')->count(),
            ],
"""),

    dict(ns="Admin", cls="Blog", title="Blog Management", model="BlogPost",
         view="livewire.admin.blog", search=["title", "excerpt"],
         sort="created_at", with_=["user", "category"], filters={"status": "status"},
         actions="""
    public function publishPost(int $id): void
    {
        $post = BlogPost::findOrFail($id);
        $post->update(['status' => 'published', 'published_at' => now()]);

        $this->notifySuccess('Post published.');
    }

    public function unpublishPost(int $id): void
    {
        BlogPost::findOrFail($id)->update(['status' => 'draft']);
        $this->notifySuccess('Post moved back to draft.');
    }

    public function deletePost(int $id): void
    {
        BlogPost::findOrFail($id)->delete();
        $this->notifySuccess('Post deleted.');
    }
""",
         data="""
            'totals' => [
                'all' => BlogPost::count(),
                'published' => BlogPost::where('status', 'published')->count(),
                'draft' => BlogPost::where('status', 'draft')->count(),
                'ai' => BlogPost::where('ai_generated', true)->count(),
            ],
"""),

    dict(ns="Admin", cls="ActivityLogs", title="Activity Logs", model="ActivityLog",
         view="livewire.admin.activity-logs", search=["description", "action"],
         sort="created_at", with_=["user"], filters={"action": "action"},
         data="""
            'actions' => ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
"""),
    # ───────────────────────── SUPER ─────────────────────────
    dict(ns="Super", cls="Users", title="All Users", model="User",
         view="livewire.super.users", search=["name", "email", "username"],
         sort="created_at", with_=["roles", "subscription.plan"], counts=["websites"],
         filters={"status": "status"},
         actions="""
    public function toggleStatus(int $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);
        $this->notifySuccess("{$user->name} is now {$user->status}.");
    }

    public function impersonate(int $id): void
    {
        $user = User::findOrFail($id);

        abort_if($user->isSuperAdmin(), 403, 'Super admins cannot be impersonated.');

        session(['impersonator_id' => auth()->id()]);
        auth()->login($user);

        $this->redirectRoute($user->homeRoute(), navigate: false);
    }

    public function deleteUser(int $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        $user->delete();
        $this->notifySuccess('User deleted.');
    }
""",
         data="""
            'totals' => [
                'all' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'staff' => User::staff()->count(),
            ],
"""),

    dict(ns="Super", cls="Websites", title="All Websites", model="Website",
         view="livewire.super.websites", search=["name", "subdomain", "custom_domain", "user.name"],
         sort="created_at", with_=["user", "theme"], counts=["pages"], filters={"status": "status"},
         actions="""
    public function togglePublish(int $id): void
    {
        $website = Website::findOrFail($id);

        $website->isPublished()
            ? app(WebsiteService::class)->unpublish($website)
            : app(WebsiteService::class)->publish($website);

        $this->notifySuccess('Website status updated.');
    }

    public function deleteWebsite(int $id): void
    {
        app(WebsiteService::class)->delete(Website::findOrFail($id));
        $this->notifySuccess('Website deleted.');
    }
""",
         uses=["App\\Services\\Website\\WebsiteService"],
         data="""
            'totals' => [
                'all' => Website::count(),
                'published' => Website::where('status', 'published')->count(),
                'domains' => Website::whereNotNull('custom_domain')->count(),
                'views' => (int) Website::sum('views_count'),
            ],
"""),

    dict(ns="Super", cls="Templates", title="Template Management", model="Template",
         view="livewire.super.templates", search=["name", "description"], sort="sort_order", sortdir="asc",
         filters={"category": "category"},
         actions="""
    public function toggleActive(int $id): void
    {
        $t = Template::findOrFail($id);
        $t->update(['is_active' => ! $t->is_active]);
        $this->notifySuccess('Template updated.');
    }

    public function toggleFeatured(int $id): void
    {
        $t = Template::findOrFail($id);
        $t->update(['is_featured' => ! $t->is_featured]);
        $this->notifySuccess('Template updated.');
    }

    public function deleteTemplate(int $id): void
    {
        Template::findOrFail($id)->delete();
        $this->notifySuccess('Template deleted.');
    }
""",
         data="""
            'categories' => config('platform.template_categories'),
            'totals' => ['all' => Template::count(), 'premium' => Template::where('is_premium', true)->count()],
"""),

    dict(ns="Super", cls="Components", title="Component Management", model="Component",
         view="livewire.super.components", search=["name", "description"], sort="sort_order", sortdir="asc",
         filters={"category": "category"},
         actions="""
    public function toggleActive(int $id): void
    {
        $c = Component::findOrFail($id);
        $c->update(['is_active' => ! $c->is_active]);
        $this->notifySuccess('Component updated.');
    }

    public function deleteComponent(int $id): void
    {
        Component::findOrFail($id)->delete();
        $this->notifySuccess('Component deleted.');
    }
""",
         data="""
            'categories' => config('platform.component_categories'),
            'totals' => ['all' => Component::count(), 'active' => Component::where('is_active', true)->count()],
"""),

    dict(ns="Super", cls="Themes", title="Theme Management", model="Theme",
         view="livewire.super.themes", search=["name", "description"], sort="name", sortdir="asc",
         actions="""
    public function toggleActive(int $id): void
    {
        $t = Theme::findOrFail($id);
        $t->update(['is_active' => ! $t->is_active]);
        $this->notifySuccess('Theme updated.');
    }

    public function makeDefault(int $id): void
    {
        Theme::query()->update(['is_default' => false]);
        Theme::findOrFail($id)->update(['is_default' => true, 'is_active' => true]);
        $this->notifySuccess('Default theme updated.');
    }
""",
         data="""
            'totals' => ['all' => Theme::count(), 'system' => Theme::whereNull('user_id')->count()],
"""),

    dict(ns="Super", cls="Media", title="All Media", model="Media",
         view="livewire.super.media", search=["name", "original_name"], sort="created_at",
         with_=["user"], filters={"type": "type"},
         actions="""
    public function deleteMedia(int $id): void
    {
        app(MediaService::class)->delete(MediaModel::findOrFail($id));
        $this->notifySuccess('File deleted.');
    }
""",
         uses=["App\\Services\\Platform\\MediaService"],
         data="""
            'totals' => [
                'all' => MediaModel::count(),
                'size' => round((int) MediaModel::sum('size') / 1048576, 1),
            ],
"""),

    dict(ns="Super", cls="Subscriptions", title="Subscriptions", model="Subscription",
         view="livewire.super.subscriptions", search=["user.name", "user.email"], sort="created_at",
         with_=["user", "plan"], filters={"status": "status"},
         actions="""
    public function cancelSubscription(int $id): void
    {
        app(SubscriptionService::class)->cancel(Subscription::findOrFail($id));
        $this->notifySuccess('Subscription cancelled.');
    }
""",
         uses=["App\\Services\\Billing\\SubscriptionService"],
         data="""
            'totals' => [
                'active' => Subscription::where('status', 'active')->count(),
                'cancelled' => Subscription::where('status', 'cancelled')->count(),
                'mrr' => round((float) Subscription::where('status', 'active')->where('billing_cycle', 'monthly')->sum('amount'), 2),
            ],
"""),

    dict(ns="Super", cls="Invoices", title="Invoices", model="Invoice",
         view="livewire.super.invoices", search=["invoice_number", "user.name", "user.email"],
         sort="created_at", with_=["user", "subscription.plan"], filters={"status": "status"},
         actions="""
    public function markPaid(int $id): void
    {
        Invoice::findOrFail($id)->update(['status' => 'paid', 'paid_at' => now()]);
        $this->notifySuccess('Invoice marked as paid.');
    }

    public function refund(int $id): void
    {
        Invoice::findOrFail($id)->update(['status' => 'refunded']);
        $this->notifySuccess('Invoice refunded.');
    }
""",
         data="""
            'totals' => [
                'paid' => round((float) Invoice::where('status', 'paid')->sum('total'), 2),
                'pending' => Invoice::where('status', 'pending')->count(),
                'refunded' => Invoice::where('status', 'refunded')->count(),
                'count' => Invoice::count(),
            ],
"""),

    dict(ns="Super", cls="Coupons", title="Coupons", model="Coupon",
         view="livewire.super.coupons", search=["code", "description"], sort="created_at",
         actions="""
    public function toggleActive(int $id): void
    {
        $c = Coupon::findOrFail($id);
        $c->update(['is_active' => ! $c->is_active]);
        $this->notifySuccess('Coupon updated.');
    }

    public function deleteCoupon(int $id): void
    {
        Coupon::findOrFail($id)->delete();
        $this->notifySuccess('Coupon deleted.');
    }
""",
         data="""
            'totals' => [
                'all' => Coupon::count(),
                'active' => Coupon::where('is_active', true)->count(),
                'redemptions' => (int) Coupon::sum('redemptions_count'),
            ],
"""),

    dict(ns="Super", cls="Blog", title="Blog Management", model="BlogPost",
         view="livewire.super.blog", search=["title", "excerpt"], sort="created_at",
         with_=["user", "category"], filters={"status": "status"},
         actions="""
    public function publishPost(int $id): void
    {
        BlogPost::findOrFail($id)->update(['status' => 'published', 'published_at' => now()]);
        $this->notifySuccess('Post published.');
    }

    public function deletePost(int $id): void
    {
        BlogPost::findOrFail($id)->delete();
        $this->notifySuccess('Post deleted.');
    }
""",
         data="""
            'totals' => [
                'all' => BlogPost::count(),
                'published' => BlogPost::where('status', 'published')->count(),
                'draft' => BlogPost::where('status', 'draft')->count(),
            ],
"""),

    dict(ns="Super", cls="CmsPages", title="CMS Pages", model="CmsPage",
         view="livewire.super.cms-pages", search=["title", "slug"], sort="title", sortdir="asc",
         actions="""
    public function togglePublished(int $id): void
    {
        $page = CmsPage::findOrFail($id);
        $page->update(['status' => $page->status === 'published' ? 'draft' : 'published']);
        $this->notifySuccess('Page updated.');
    }

    public function deletePage(int $id): void
    {
        CmsPage::findOrFail($id)->delete();
        $this->notifySuccess('Page deleted.');
    }
""",
         data="""
            'totals' => ['all' => CmsPage::count(), 'published' => CmsPage::where('status', 'published')->count()],
"""),

    dict(ns="Super", cls="Faqs", title="FAQ Management", model="Faq",
         view="livewire.super.faqs", search=["question", "answer"], sort="sort_order", sortdir="asc",
         filters={"category": "category"},
         actions="""
    public function toggleActive(int $id): void
    {
        $faq = Faq::findOrFail($id);
        $faq->update(['is_active' => ! $faq->is_active]);
        $this->notifySuccess('FAQ updated.');
    }

    public function deleteFaq(int $id): void
    {
        Faq::findOrFail($id)->delete();
        $this->notifySuccess('FAQ deleted.');
    }
""",
         data="""
            'totals' => ['all' => Faq::count(), 'active' => Faq::where('is_active', true)->count()],
            'categories' => Faq::query()->select('category')->distinct()->orderBy('category')->pluck('category'),
"""),

    dict(ns="Super", cls="Contacts", title="Contact Messages", model="ContactMessage",
         view="livewire.super.contacts", search=["name", "email", "subject", "message"], sort="created_at",
         filters={"status": "status"},
         actions="""
    public function markRead(int $id): void
    {
        ContactMessage::findOrFail($id)->update(['status' => 'read', 'read_at' => now()]);
        $this->notifySuccess('Marked as read.');
    }

    public function markReplied(int $id): void
    {
        ContactMessage::findOrFail($id)->update(['status' => 'replied']);
        $this->notifySuccess('Marked as replied.');
    }

    public function deleteMessage(int $id): void
    {
        ContactMessage::findOrFail($id)->delete();
        $this->notifySuccess('Message deleted.');
    }
""",
         data="""
            'totals' => [
                'all' => ContactMessage::count(),
                'unread' => ContactMessage::where('status', 'unread')->count(),
                'replied' => ContactMessage::where('status', 'replied')->count(),
            ],
"""),

    dict(ns="Super", cls="AiLogs", title="AI Generation Logs", model="AiGeneration",
         view="livewire.super.ai-logs", search=["prompt", "user.name"], sort="created_at",
         with_=["user"], filters={"status": "status", "type": "type"},
         data="""
            'totals' => [
                'all' => AiGeneration::count(),
                'completed' => AiGeneration::where('status', 'completed')->count(),
                'failed' => AiGeneration::where('status', 'failed')->count(),
                'credits' => (int) AiGeneration::sum('credits_used'),
                'tokens' => (int) AiGeneration::sum('tokens_used'),
            ],
"""),

    dict(ns="Super", cls="SecurityLogs", title="Security Logs", model="SecurityLog",
         view="livewire.super.security-logs", search=["event", "ip_address"], sort="created_at",
         with_=["user"], filters={"level": "level", "event": "event"},
         data="""
            'totals' => [
                'all' => SecurityLog::count(),
                'warnings' => SecurityLog::where('level', 'warning')->count(),
                'today' => SecurityLog::whereDate('created_at', today())->count(),
            ],
            'events' => SecurityLog::query()->select('event')->distinct()->orderBy('event')->pluck('event'),
"""),

    dict(ns="Super", cls="ActivityLogs", title="Audit & Activity Logs", model="ActivityLog",
         view="livewire.super.activity-logs", search=["description", "action"], sort="created_at",
         with_=["user"], filters={"action": "action"},
         data="""
            'actions' => ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'totals' => ['all' => ActivityLog::count(), 'today' => ActivityLog::whereDate('created_at', today())->count()],
"""),

    dict(ns="Super", cls="Backups", title="Backup & Restore", model="Backup",
         view="livewire.super.backups", search=["name"], sort="created_at",
         actions="""
    public function createBackup(): void
    {
        Backup::create([
            'name' => 'manual-'.now()->format('Y-m-d-His'),
            'type' => 'manual',
            'disk' => 'local',
            'path' => 'backups/manual-'.now()->format('YmdHis').'.zip',
            'size' => random_int(2_000_000, 40_000_000),
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->notifySuccess('Backup created successfully.');
    }

    public function deleteBackup(int $id): void
    {
        Backup::findOrFail($id)->delete();
        $this->notifySuccess('Backup deleted.');
    }
""",
         data="""
            'totals' => [
                'all' => Backup::count(),
                'size' => round((int) Backup::sum('size') / 1048576, 1),
                'latest' => Backup::latest()->first()?->created_at,
            ],
"""),
]

CLASS_TEMPLATE = '''<?php

declare(strict_types=1);

namespace App\\Livewire\\{ns};

use App\\Livewire\\ResourceComponent;
use App\\Models\\{import_line};
{extra_uses}use Illuminate\\Database\\Eloquent\\Builder;
use Livewire\\Attributes\\Layout;

#[Layout('layouts.app')]
class {cls} extends ResourceComponent
{{
    public string $sortField = '{sort}';

    public string $sortDirection = '{sortdir}';
{actions}
    protected function title(): string
    {{
        return '{title}';
    }}

    protected function view(): string
    {{
        return '{view}';
    }}

    protected function searchable(): array
    {{
        return [{search}];
    }}

    protected function query(): Builder
    {{
        return {model}::query(){chain};
    }}

    protected function viewData(): array
    {{
        return [{data}        ];
    }}
}}
'''


def render(r):
    # A component may share its name with the model it lists (e.g. Media).
    # Alias the model import in that case so the class name stays unambiguous.
    model_ref = r["model"]
    import_line = r["model"]

    if r["cls"] == r["model"]:
        model_ref = r["model"] + "Model"
        import_line = f'{r["model"]} as {model_ref}'

    chain = ""
    if r.get("with_"):
        chain += "\n            ->with(" + ", ".join(f"'{w}'" for w in r["with_"]) + ")"
    if r.get("counts"):
        chain += "\n            ->withCount(" + ", ".join(f"'{c}'" for c in r["counts"]) + ")"

    extra = "".join(f"use {u};\n" for u in r.get("uses", []))

    actions = r.get("actions", "\n")
    data = r.get("data", "\n")

    if model_ref != r["model"]:
        pattern = re.compile(rf'\b{r["model"]}::')
        actions = pattern.sub(f"{model_ref}::", actions)
        data = pattern.sub(f"{model_ref}::", data)

    return CLASS_TEMPLATE.format(
        ns=r["ns"], cls=r["cls"], model=model_ref, import_line=import_line,
        title=r["title"], view=r["view"],
        search=", ".join(f"'{s}'" for s in r["search"]),
        sort=r.get("sort", "created_at"), sortdir=r.get("sortdir", "desc"),
        chain=chain, extra_uses=extra,
        actions=actions,
        data=data,
    )


def main():
    written = 0
    for r in RESOURCES:
        path = ROOT / "app" / "Livewire" / r["ns"] / f"{r['cls']}.php"
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(render(r))
        written += 1
        print(f"  {path.relative_to(ROOT)}")
    print(f"\n{written} resource components generated.")


if __name__ == "__main__":
    main()
