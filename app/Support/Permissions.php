<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\RoleType;

/**
 * Single source of truth for the platform permission catalogue.
 * Permissions are grouped so the admin UI can render them logically.
 */
final class Permissions
{
    /** @return array<string, array<string, string>> group => [permission => label] */
    public static function catalogue(): array
    {
        return [
            'Dashboard' => [
                'dashboard.view' => 'View dashboard',
                'dashboard.analytics' => 'View revenue & analytics',
            ],
            'Users' => [
                'users.view' => 'View users',
                'users.create' => 'Create users',
                'users.update' => 'Edit users',
                'users.delete' => 'Delete users',
                'users.impersonate' => 'Impersonate users',
                'users.suspend' => 'Suspend users',
            ],
            'Admins' => [
                'admins.view' => 'View admins',
                'admins.create' => 'Create admins',
                'admins.update' => 'Edit admins',
                'admins.delete' => 'Delete admins',
            ],
            'Roles' => [
                'roles.view' => 'View roles & permissions',
                'roles.create' => 'Create roles',
                'roles.update' => 'Edit roles',
                'roles.delete' => 'Delete roles',
            ],
            'Websites' => [
                'websites.view' => 'View all websites',
                'websites.create' => 'Create websites',
                'websites.update' => 'Edit websites',
                'websites.delete' => 'Delete websites',
                'websites.publish' => 'Publish websites',
                'websites.export' => 'Export websites',
            ],
            'Templates' => [
                'templates.view' => 'View templates',
                'templates.create' => 'Create templates',
                'templates.update' => 'Edit templates',
                'templates.delete' => 'Delete templates',
            ],
            'Components' => [
                'components.view' => 'View components',
                'components.create' => 'Create components',
                'components.update' => 'Edit components',
                'components.delete' => 'Delete components',
            ],
            'Themes' => [
                'themes.view' => 'View themes',
                'themes.manage' => 'Manage themes',
            ],
            'Media' => [
                'media.view' => 'View media library',
                'media.upload' => 'Upload media',
                'media.delete' => 'Delete media',
            ],
            'Content' => [
                'blog.view' => 'View blog posts',
                'blog.manage' => 'Manage blog posts',
                'cms.manage' => 'Manage CMS pages',
                'faq.manage' => 'Manage FAQs',
            ],
            'Billing' => [
                'plans.view' => 'View billing plans',
                'plans.manage' => 'Manage billing plans',
                'subscriptions.view' => 'View subscriptions',
                'subscriptions.manage' => 'Manage subscriptions',
                'coupons.manage' => 'Manage coupons',
                'invoices.view' => 'View invoices',
            ],
            'AI' => [
                'ai.use' => 'Use AI generators',
                'ai.settings' => 'Configure AI providers',
                'ai.logs' => 'View AI generation logs',
            ],
            'Support' => [
                'tickets.view' => 'View support tickets',
                'tickets.manage' => 'Reply & resolve tickets',
                'contacts.view' => 'View contact messages',
            ],
            'System' => [
                'settings.view' => 'View system settings',
                'settings.manage' => 'Manage system settings',
                'apikeys.manage' => 'Manage API keys',
                'logs.view' => 'View activity logs',
                'logs.security' => 'View security logs',
                'system.health' => 'View system health',
                'system.cache' => 'Manage cache',
                'system.queue' => 'Monitor queues',
                'system.backup' => 'Backup & restore',
                'system.database' => 'Database tools',
                'system.maintenance' => 'Toggle maintenance mode',
                'system.branding' => 'Manage platform branding',
            ],
            'Reports' => [
                'reports.view' => 'View reports',
                'reports.export' => 'Export reports',
            ],
        ];
    }

    /** @return string[] */
    public static function all(): array
    {
        return array_merge(...array_map('array_keys', array_values(self::catalogue())));
    }

    /** @return string[] */
    public static function forRole(RoleType $role): array
    {
        return match ($role) {
            RoleType::SuperAdmin => self::all(),
            RoleType::Admin => [
                'dashboard.view',
                'users.view', 'users.create', 'users.update', 'users.suspend',
                'websites.view', 'websites.update', 'websites.publish',
                'templates.view', 'templates.create', 'templates.update', 'templates.delete',
                'components.view', 'components.create', 'components.update', 'components.delete',
                'themes.view', 'themes.manage',
                'media.view', 'media.upload', 'media.delete',
                'blog.view', 'blog.manage', 'cms.manage', 'faq.manage',
                'tickets.view', 'tickets.manage', 'contacts.view',
                'ai.use', 'ai.logs',
                'reports.view', 'logs.view',
                'subscriptions.view', 'invoices.view',
            ],
            RoleType::User => [
                'dashboard.view',
                'websites.create', 'websites.update', 'websites.delete', 'websites.publish', 'websites.export',
                'templates.view', 'components.view', 'themes.view',
                'media.view', 'media.upload', 'media.delete',
                'blog.view', 'blog.manage',
                'ai.use',
                'tickets.view',
            ],
        };
    }

    public static function groupOf(string $permission): string
    {
        foreach (self::catalogue() as $group => $items) {
            if (array_key_exists($permission, $items)) {
                return $group;
            }
        }

        return 'Other';
    }

    public static function labelOf(string $permission): string
    {
        foreach (self::catalogue() as $items) {
            if (isset($items[$permission])) {
                return $items[$permission];
            }
        }

        return str($permission)->replace(['.', '-'], ' ')->title()->toString();
    }
}
