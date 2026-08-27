<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ContactMessage;
use App\Models\SupportTicket;
use App\Models\User;

/**
 * Builds the sidebar navigation tree for each role.
 * Items are filtered by permission at render time.
 */
final class Navigation
{
    public static function for(User $user): array
    {
        return match (true) {
            $user->isSuperAdmin() => self::superAdmin(),
            $user->isAdmin() => self::admin(),
            default => self::user(),
        };
    }

    private static function user(): array
    {
        return [
            'Workspace' => [
                ['label' => 'Dashboard', 'route' => 'app.dashboard', 'icon' => 'dashboard'],
                ['label' => 'My Websites', 'route' => 'app.websites', 'icon' => 'globe'],
                ['label' => 'Media Library', 'route' => 'app.media', 'icon' => 'image'],
                ['label' => 'Analytics', 'route' => 'app.analytics', 'icon' => 'chart'],
            ],
            'Create' => [
                ['label' => 'AI Studio', 'route' => 'app.ai', 'icon' => 'sparkles'],
                ['label' => 'Templates', 'route' => 'app.templates', 'icon' => 'layout'],
                ['label' => 'Components', 'route' => 'app.components', 'icon' => 'grid'],
                ['label' => 'Themes', 'route' => 'app.themes', 'icon' => 'palette'],
            ],
            'Account' => [
                ['label' => 'Billing & Plan', 'route' => 'app.billing', 'icon' => 'credit-card'],
                ['label' => 'Support', 'route' => 'support.index', 'icon' => 'ticket'],
                ['label' => 'Settings', 'route' => 'app.settings', 'icon' => 'settings'],
            ],
        ];
    }

    private static function admin(): array
    {
        return [
            'Overview' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Analytics', 'route' => 'admin.analytics', 'icon' => 'chart', 'can' => 'reports.view'],
                ['label' => 'Reports', 'route' => 'admin.reports', 'icon' => 'file', 'can' => 'reports.view'],
            ],
            'Management' => [
                ['label' => 'Users', 'route' => 'admin.users', 'icon' => 'users', 'can' => 'users.view'],
                ['label' => 'Websites', 'route' => 'admin.websites', 'icon' => 'globe', 'can' => 'websites.view'],
                ['label' => 'Templates', 'route' => 'admin.templates', 'icon' => 'layout', 'can' => 'templates.view'],
                ['label' => 'Components', 'route' => 'admin.components', 'icon' => 'grid', 'can' => 'components.view'],
                ['label' => 'Media', 'route' => 'admin.media', 'icon' => 'image', 'can' => 'media.view'],
            ],
            'Content' => [
                ['label' => 'Blog', 'route' => 'admin.blog', 'icon' => 'book', 'can' => 'blog.view'],
                ['label' => 'Support Tickets', 'route' => 'admin.tickets', 'icon' => 'ticket', 'can' => 'tickets.view',
                    'badge' => self::openTickets()],
            ],
            'System' => [
                ['label' => 'Activity Logs', 'route' => 'admin.activity', 'icon' => 'activity', 'can' => 'logs.view'],
            ],
        ];
    }

    private static function superAdmin(): array
    {
        return [
            'Overview' => [
                ['label' => 'Dashboard', 'route' => 'super.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Revenue', 'route' => 'super.revenue', 'icon' => 'credit-card'],
                ['label' => 'Reports', 'route' => 'super.reports', 'icon' => 'file'],
            ],
            'People' => [
                ['label' => 'Users', 'route' => 'super.users', 'icon' => 'users'],
                ['label' => 'Admins', 'route' => 'super.admins', 'icon' => 'shield'],
                ['label' => 'Roles & Permissions', 'route' => 'super.roles', 'icon' => 'key'],
            ],
            'Platform' => [
                ['label' => 'Websites', 'route' => 'super.websites', 'icon' => 'globe'],
                ['label' => 'Templates', 'route' => 'super.templates', 'icon' => 'layout'],
                ['label' => 'Components', 'route' => 'super.components', 'icon' => 'grid'],
                ['label' => 'Themes', 'route' => 'super.themes', 'icon' => 'palette'],
                ['label' => 'Media', 'route' => 'super.media', 'icon' => 'image'],
            ],
            'Commerce' => [
                ['label' => 'Plans', 'route' => 'super.plans', 'icon' => 'tag'],
                ['label' => 'Subscriptions', 'route' => 'super.subscriptions', 'icon' => 'refresh'],
                ['label' => 'Coupons', 'route' => 'super.coupons', 'icon' => 'badge'],
                ['label' => 'Invoices', 'route' => 'super.invoices', 'icon' => 'file'],
            ],
            'Content' => [
                ['label' => 'Blog', 'route' => 'super.blog', 'icon' => 'book'],
                ['label' => 'CMS Pages', 'route' => 'super.cms', 'icon' => 'file'],
                ['label' => 'FAQ', 'route' => 'super.faq', 'icon' => 'help'],
                ['label' => 'Support Tickets', 'route' => 'super.tickets', 'icon' => 'ticket', 'badge' => self::openTickets()],
                ['label' => 'Contact Messages', 'route' => 'super.contacts', 'icon' => 'mail', 'badge' => self::newContacts()],
            ],
            'Intelligence' => [
                ['label' => 'AI Settings', 'route' => 'super.ai', 'icon' => 'sparkles'],
                ['label' => 'AI Logs', 'route' => 'super.ai-logs', 'icon' => 'wand'],
                ['label' => 'API Keys', 'route' => 'super.api-keys', 'icon' => 'key'],
            ],
            'System' => [
                ['label' => 'Settings', 'route' => 'super.settings', 'icon' => 'settings'],
                ['label' => 'System Health', 'route' => 'super.health', 'icon' => 'server'],
                ['label' => 'Queue Monitor', 'route' => 'super.queue', 'icon' => 'refresh'],
                ['label' => 'Database', 'route' => 'super.database', 'icon' => 'database'],
                ['label' => 'Backups', 'route' => 'super.backups', 'icon' => 'download'],
                ['label' => 'Activity Logs', 'route' => 'super.activity', 'icon' => 'activity'],
                ['label' => 'Security Logs', 'route' => 'super.security', 'icon' => 'shield'],
            ],
        ];
    }

    private static function openTickets(): ?int
    {
        try {
            $count = SupportTicket::open()->count();

            return $count > 0 ? $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function newContacts(): ?int
    {
        try {
            $count = ContactMessage::where('is_read', false)->count();

            return $count > 0 ? $count : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
