#!/usr/bin/env python3
"""Generate the Blade views for admin/super resource tables.

Each screen is described as a stat strip, a filter bar and a set of columns.
Rendering is delegated to shared <x-ui.*> components so the visual language
stays identical across every panel.
"""
import pathlib

ROOT = pathlib.Path(__file__).resolve().parent.parent
VIEWS = ROOT / "resources" / "views" / "livewire"

HEADER = """<div class="space-y-6">
    <x-ui.page-header title="{title}" description="{desc}">
{header_actions}    </x-ui.page-header>
{stats}
    <x-ui.toolbar placeholder="{placeholder}">
{filters}    </x-ui.toolbar>
{bulk}
    <x-ui.table :rows="$rows" empty="{empty}" emptyIcon="{icon}" emptyDescription="{empty_desc}">
        <x-slot:head>
{head}        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{{{ $row->id }}}}">
{body}                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>
{extra}
</div>
"""


def stat_strip(stats):
    if not stats:
        return ""
    cols = min(len(stats), 4)
    items = "\n".join(
        f'        <x-ui.stat label="{label}" :value="{value}" icon="{icon}" color="{color}" />'
        for label, value, icon, color in stats
    )
    return f'\n    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-{cols}">\n{items}\n    </div>\n'


def filter_selects(filters):
    """Options may be a literal list of (value, label) pairs, or the name of a
    view variable holding a collection of values to iterate at render time."""
    out = ""
    for key, label, options in filters:
        if isinstance(options, str):
            opts = (f'            @foreach(${options} as $opt)\n'
                    f'                <option value="{{{{ $opt }}}}">{{{{ Str::headline($opt) }}}}</option>\n'
                    f'            @endforeach')
        else:
            opts = "\n".join(
                f'                <option value="{v}">{l}</option>' for v, l in options
            )
        out += (
            f'        <select wire:model.live="filters.{key}" class="field lg:w-44 shrink-0">\n'
            f'            <option value="">{label}</option>\n{opts}\n        </select>\n'
        )
    return out


def bulk_bar(actions, label):
    if not actions:
        return ""
    btns = "\n".join(
        f'        <button wire:click="{m}" class="btn {c} btn-sm">{t}</button>'
        for m, t, c in actions
    )
    return f'\n    <x-ui.bulk-bar :count="count($selected)" label="{label}">\n{btns}\n    </x-ui.bulk-bar>\n'


def render(spec):
    head = ""
    for col in spec["columns"]:
        if col.get("select"):
            head += ('            <th class="w-10"><input type="checkbox" wire:model.live="selectAll" '
                     'class="rounded border-subtle text-brand-500 focus:ring-brand-500/30 w-4 h-4"></th>\n')
        elif col.get("sort"):
            align = ' align="right"' if col.get("align") == "right" else ""
            head += (f'            <x-ui.th sort="{col["sort"]}" :sortIcon="$this->sortIcon(\'{col["sort"]}\')"'
                     f'{align}>{col["label"]}</x-ui.th>\n')
        else:
            align = f' align="{col["align"]}"' if col.get("align") else ""
            head += f'            <x-ui.th{align}>{col["label"]}</x-ui.th>\n'

    body = ""
    for col in spec["columns"]:
        if col.get("select"):
            body += ('                    <td><input type="checkbox" wire:model.live="selected" value="{{ $row->id }}" '
                     'class="rounded border-subtle text-brand-500 focus:ring-brand-500/30 w-4 h-4"></td>\n')
        else:
            cls = ' class="text-right"' if col.get("align") == "right" else ""
            body += f'                    <td{cls}>{col["cell"]}</td>\n'

    header_actions = "".join(f"        {a}\n" for a in spec.get("actions", []))

    return HEADER.format(
        title=spec["title"], desc=spec["desc"],
        header_actions=header_actions,
        stats=stat_strip(spec.get("stats")),
        placeholder=spec.get("placeholder", "Search…"),
        filters=filter_selects(spec.get("filters", [])),
        bulk=bulk_bar(spec.get("bulk"), spec.get("bulk_label", "item")),
        empty=spec.get("empty", "No records found"),
        icon=spec.get("icon", "inbox"),
        empty_desc=spec.get("empty_desc", "Try adjusting your search or filters."),
        head=head, body=body,
        extra=spec.get("extra", ""),
    )


# ── shared cell snippets ───────────────────────────────────────────────
USER_CELL = '<x-ui.user-cell :user="$row->user" />'
DATE = '<span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span>'


SPECS = {
    # ═════════════════════════ ADMIN ═════════════════════════
    "admin/users": dict(
        title="User Management", desc="View, search and moderate customer accounts.",
        placeholder="Search by name, email or username…", icon="users",
        empty="No users found",
        stats=[("Total users", "$totals['all']", "users", "brand"),
               ("Active", "$totals['active']", "check-circle", "emerald"),
               ("Suspended", "$totals['suspended']", "ban", "rose"),
               ("New (30d)", "$totals['new']", "user-plus", "violet")],
        filters=[("status", "All statuses", [("active", "Active"), ("suspended", "Suspended"), ("pending", "Pending")])],
        bulk=[("bulkActivate", "Activate", "btn-secondary"), ("bulkSuspend", "Suspend", "btn-danger")],
        bulk_label="user",
        columns=[
            dict(select=True),
            dict(label="User", sort="name", cell='<x-ui.user-cell :user="$row" />'),
            dict(label="Plan", cell='<x-ui.badge color="{{ $row->subscription ? \'brand\' : \'slate\' }}">{{ $row->subscription?->plan?->name ?? \'Free\' }}</x-ui.badge>'),
            dict(label="Websites", sort="websites_count", cell='<span class="tabular-nums">{{ $row->websites_count }}</span>'),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->status === \'active\' ? \'emerald\' : \'rose\'" dot>{{ ucfirst($row->status) }}</x-ui.badge>'),
            dict(label="Joined", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <button wire:click="$set('viewing', {{ $row->id }})" class="btn btn-ghost btn-icon" title="View"><x-icon name="eye" class="w-4 h-4" /></button>
                            <button wire:click="toggleStatus({{ $row->id }})" class="btn btn-ghost btn-icon" title="Toggle status">
                                <x-icon name="{{ $row->status === 'active' ? 'ban' : 'check-circle' }}" class="w-4 h-4 {{ $row->status === 'active' ? 'text-rose-500' : 'text-emerald-500' }}" />
                            </button>
                        </div>'''),
        ],
    ),

    "admin/websites": dict(
        title="Website Management", desc="Every website created on the platform.",
        placeholder="Search by name, subdomain or owner…", icon="globe",
        empty="No websites found",
        stats=[("Total", "$totals['all']", "globe", "brand"),
               ("Published", "$totals['published']", "rocket", "emerald"),
               ("Drafts", "$totals['draft']", "edit", "amber"),
               ("Total views", "number_format($totals['views'])", "eye", "sky")],
        filters=[("status", "All statuses", [("draft", "Draft"), ("published", "Published"), ("archived", "Archived")])],
        columns=[
            dict(label="Website", sort="name", cell='''<div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-500/18 to-cyan-400/12 grid place-items-center shrink-0">
                                <x-icon name="globe" class="w-4 h-4 text-brand-500" />
                            </span>
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $row->name }}</p>
                                <p class="text-xs text-tertiary truncate">{{ $row->display_domain }}</p>
                            </div>
                        </div>'''),
            dict(label="Owner", cell=USER_CELL),
            dict(label="Pages", sort="pages_count", cell='<span class="tabular-nums">{{ $row->pages_count }}</span>'),
            dict(label="Views", sort="views_count", cell='<span class="tabular-nums">{{ number_format($row->views_count) }}</span>'),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->status->color()" dot>{{ $row->status->label() }}</x-ui.badge>'),
            dict(label="Created", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <a href="{{ route('site.preview', $row->subdomain) }}" target="_blank" class="btn btn-ghost btn-icon" title="Preview"><x-icon name="external" class="w-4 h-4" /></a>
                            <button wire:click="togglePublish({{ $row->id }})" class="btn btn-ghost btn-icon" title="Toggle publish"><x-icon name="{{ $row->isPublished() ? 'eye-off' : 'rocket' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteWebsite({{ $row->id }})" wire:confirm="Delete this website?" class="btn btn-ghost btn-icon text-rose-500" title="Delete"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "admin/templates": dict(
        title="Templates", desc="Curate the template gallery.",
        placeholder="Search templates…", icon="layout", empty="No templates found",
        stats=[("Total", "$totals['all']", "layout", "brand"),
               ("Active", "$totals['active']", "check-circle", "emerald"),
               ("Premium", "$totals['premium']", "crown", "amber")],
        columns=[
            dict(label="Template", sort="name", cell='''<div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->description, 54) }}</p>
                        </div>'''),
            dict(label="Category", sort="category", cell='<x-ui.badge color="slate">{{ ucfirst($row->category) }}</x-ui.badge>'),
            dict(label="Uses", sort="uses_count", cell='<span class="tabular-nums">{{ number_format($row->uses_count ?? 0) }}</span>'),
            dict(label="Tier", cell='@if($row->is_premium)<x-ui.badge color="amber"><x-icon name="crown" class="w-3 h-3" /> Pro</x-ui.badge>@else<x-ui.badge color="emerald">Free</x-ui.badge>@endif'),
            dict(label="Status", cell='<x-ui.badge :color="$row->is_active ? \'emerald\' : \'slate\'" dot>{{ $row->is_active ? \'Visible\' : \'Hidden\' }}</x-ui.badge>'),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <button wire:click="toggleFeatured({{ $row->id }})" class="btn btn-ghost btn-icon" title="Feature"><x-icon name="star" class="w-4 h-4 {{ $row->is_featured ? 'fill-amber-400 text-amber-400' : '' }}" /></button>
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon" title="Toggle visibility"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "admin/components": dict(
        title="Components", desc="Manage the drag-and-drop block library.",
        placeholder="Search blocks…", icon="grid", empty="No components found",
        stats=[("Total blocks", "$totals['all']", "grid", "brand"),
               ("Active", "$totals['active']", "check-circle", "emerald")],
        columns=[
            dict(label="Component", sort="name", cell='''<div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-lg bg-surface-muted grid place-items-center text-base shrink-0">{{ $row->icon ?: '▦' }}</span>
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $row->name }}</p>
                                <p class="text-xs text-tertiary truncate">{{ Str::limit($row->description, 48) }}</p>
                            </div>
                        </div>'''),
            dict(label="Category", sort="category", cell='<x-ui.badge color="slate">{{ ucfirst($row->category) }}</x-ui.badge>'),
            dict(label="Status", cell='<x-ui.badge :color="$row->is_active ? \'emerald\' : \'slate\'" dot>{{ $row->is_active ? \'Active\' : \'Hidden\' }}</x-ui.badge>'),
            dict(label="", align="right", cell='<button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? \'eye-off\' : \'eye\' }}" class="w-4 h-4" /></button>'),
        ],
    ),

    "admin/media": dict(
        title="Media Library", desc="All files uploaded across the platform.",
        placeholder="Search files…", icon="image", empty="No media found",
        stats=[("Total files", "number_format($totals['all'])", "image", "brand"),
               ("Storage used", "$totals['size'].' MB'", "database", "violet"),
               ("Images", "number_format($totals['images'])", "image", "cyan")],
        filters=[("type", "All types", [("image", "Images"), ("video", "Video"), ("document", "Documents")])],
        columns=[
            dict(label="File", sort="original_name", cell='''<div class="flex items-center gap-3 min-w-0">
                            <span class="w-10 h-10 rounded-lg bg-surface-muted grid place-items-center overflow-hidden shrink-0">
                                @if($row->type === 'image')
                                    <img src="{{ $row->url }}" class="w-full h-full object-cover" alt="" loading="lazy">
                                @else
                                    <x-icon name="file" class="w-4 h-4 text-tertiary" />
                                @endif
                            </span>
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $row->original_name }}</p>
                                <p class="text-xs text-tertiary">{{ round($row->size / 1024) }} KB</p>
                            </div>
                        </div>'''),
            dict(label="Owner", cell=USER_CELL),
            dict(label="Type", sort="type", cell='<x-ui.badge color="slate">{{ ucfirst($row->type) }}</x-ui.badge>'),
            dict(label="Uploaded", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='<button wire:click="deleteMedia({{ $row->id }})" wire:confirm="Delete this file?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>'),
        ],
    ),

    "admin/blog": dict(
        title="Blog Management", desc="Review, publish and moderate blog content.",
        placeholder="Search posts…", icon="book", empty="No posts found",
        stats=[("Total posts", "$totals['all']", "book", "brand"),
               ("Published", "$totals['published']", "check-circle", "emerald"),
               ("Drafts", "$totals['draft']", "edit", "amber"),
               ("AI generated", "$totals['ai']", "sparkles", "violet")],
        filters=[("status", "All statuses", [("published", "Published"), ("draft", "Draft"), ("scheduled", "Scheduled")])],
        columns=[
            dict(label="Post", sort="title", cell='''<div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->title }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->excerpt, 60) }}</p>
                        </div>'''),
            dict(label="Author", cell=USER_CELL),
            dict(label="Views", sort="views_count", cell='<span class="tabular-nums">{{ number_format($row->views_count ?? 0) }}</span>'),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->status === \'published\' ? \'emerald\' : \'amber\'" dot>{{ ucfirst($row->status) }}</x-ui.badge>'),
            dict(label="Date", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            @if($row->status !== 'published')
                                <button wire:click="publishPost({{ $row->id }})" class="btn btn-ghost btn-icon text-emerald-500" title="Publish"><x-icon name="rocket" class="w-4 h-4" /></button>
                            @else
                                <button wire:click="unpublishPost({{ $row->id }})" class="btn btn-ghost btn-icon" title="Unpublish"><x-icon name="eye-off" class="w-4 h-4" /></button>
                            @endif
                            <button wire:click="deletePost({{ $row->id }})" wire:confirm="Delete this post?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "admin/activity-logs": dict(
        title="Activity Logs", desc="A chronological record of everything that happens.",
        placeholder="Search activity…", icon="activity", empty="No activity recorded",
        filters=[("event", "All events", "events")],
        columns=[
            dict(label="Event", sort="event", cell='<x-ui.badge :color="$row->eventColor()">{{ Str::headline($row->event) }}</x-ui.badge>'),
            dict(label="Description", cell='<span class="text-sm">{{ $row->description }}</span>'),
            dict(label="User", cell=USER_CELL),
            dict(label="IP", cell='<span class="font-mono text-xs text-tertiary">{{ $row->ip_address ?? \'—\' }}</span>'),
            dict(label="When", sort="created_at", cell=DATE),
        ],
    ),

    # ═════════════════════════ SUPER ═════════════════════════
    "super/users": dict(
        title="All Users", desc="Every account on the platform, staff included.",
        placeholder="Search users…", icon="users", empty="No users found",
        stats=[("Total", "$totals['all']", "users", "brand"),
               ("Active", "$totals['active']", "check-circle", "emerald"),
               ("Suspended", "$totals['suspended']", "ban", "rose"),
               ("Staff", "$totals['staff']", "shield", "violet")],
        filters=[("status", "All statuses", [("active", "Active"), ("suspended", "Suspended")])],
        columns=[
            dict(label="User", sort="name", cell='<x-ui.user-cell :user="$row" />'),
            dict(label="Role", cell='''@foreach($row->roles as $r)<x-ui.badge :color="$r->name === 'super-admin' ? 'fuchsia' : ($r->name === 'admin' ? 'sky' : 'slate')">{{ Str::headline($r->name) }}</x-ui.badge>@endforeach'''),
            dict(label="Plan", cell='<x-ui.badge color="{{ $row->subscription ? \'brand\' : \'slate\' }}">{{ $row->subscription?->plan?->name ?? \'Free\' }}</x-ui.badge>'),
            dict(label="Sites", sort="websites_count", cell='<span class="tabular-nums">{{ $row->websites_count }}</span>'),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->status === \'active\' ? \'emerald\' : \'rose\'" dot>{{ ucfirst($row->status) }}</x-ui.badge>'),
            dict(label="Joined", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            @unless($row->isSuperAdmin())
                                <button wire:click="impersonate({{ $row->id }})" wire:confirm="Sign in as this user?" class="btn btn-ghost btn-icon" title="Impersonate"><x-icon name="user-check" class="w-4 h-4" /></button>
                            @endunless
                            <button wire:click="toggleStatus({{ $row->id }})" class="btn btn-ghost btn-icon" title="Toggle status"><x-icon name="{{ $row->status === 'active' ? 'ban' : 'check-circle' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteUser({{ $row->id }})" wire:confirm="Permanently delete this user?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/websites": dict(
        title="All Websites", desc="Platform-wide website inventory.",
        placeholder="Search websites…", icon="globe", empty="No websites found",
        stats=[("Total", "$totals['all']", "globe", "brand"),
               ("Published", "$totals['published']", "rocket", "emerald"),
               ("Custom domains", "$totals['domains']", "link", "violet"),
               ("Total views", "number_format($totals['views'])", "eye", "sky")],
        filters=[("status", "All statuses", [("draft", "Draft"), ("published", "Published"), ("archived", "Archived")])],
        columns=[
            dict(label="Website", sort="name", cell='''<div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary truncate">{{ $row->display_domain }}</p>
                        </div>'''),
            dict(label="Owner", cell=USER_CELL),
            dict(label="Pages", sort="pages_count", cell='<span class="tabular-nums">{{ $row->pages_count }}</span>'),
            dict(label="Views", sort="views_count", cell='<span class="tabular-nums">{{ number_format($row->views_count) }}</span>'),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->status->color()" dot>{{ $row->status->label() }}</x-ui.badge>'),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <a href="{{ route('site.preview', $row->subdomain) }}" target="_blank" class="btn btn-ghost btn-icon"><x-icon name="external" class="w-4 h-4" /></a>
                            <button wire:click="togglePublish({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->isPublished() ? 'eye-off' : 'rocket' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteWebsite({{ $row->id }})" wire:confirm="Delete this website?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/templates": dict(
        title="Template Management", desc="Full control over the template gallery.",
        placeholder="Search templates…", icon="layout", empty="No templates found",
        stats=[("Total", "$totals['all']", "layout", "brand"), ("Premium", "$totals['premium']", "crown", "amber")],
        columns=[
            dict(label="Template", sort="name", cell='''<div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->description, 54) }}</p>
                        </div>'''),
            dict(label="Category", sort="category", cell='<x-ui.badge color="slate">{{ ucfirst($row->category) }}</x-ui.badge>'),
            dict(label="Uses", sort="uses_count", cell='<span class="tabular-nums">{{ number_format($row->uses_count ?? 0) }}</span>'),
            dict(label="Status", cell='<x-ui.badge :color="$row->is_active ? \'emerald\' : \'slate\'" dot>{{ $row->is_active ? \'Visible\' : \'Hidden\' }}</x-ui.badge>'),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <button wire:click="toggleFeatured({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="star" class="w-4 h-4 {{ $row->is_featured ? 'fill-amber-400 text-amber-400' : '' }}" /></button>
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteTemplate({{ $row->id }})" wire:confirm="Delete this template?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/components": dict(
        title="Component Management", desc="The global block library.",
        placeholder="Search blocks…", icon="grid", empty="No components found",
        stats=[("Total", "$totals['all']", "grid", "brand"), ("Active", "$totals['active']", "check-circle", "emerald")],
        columns=[
            dict(label="Component", sort="name", cell='''<div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-lg bg-surface-muted grid place-items-center text-base shrink-0">{{ $row->icon ?: '▦' }}</span>
                            <p class="font-medium truncate">{{ $row->name }}</p>
                        </div>'''),
            dict(label="Category", sort="category", cell='<x-ui.badge color="slate">{{ ucfirst($row->category) }}</x-ui.badge>'),
            dict(label="Status", cell='<x-ui.badge :color="$row->is_active ? \'emerald\' : \'slate\'" dot>{{ $row->is_active ? \'Active\' : \'Hidden\' }}</x-ui.badge>'),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteComponent({{ $row->id }})" wire:confirm="Delete this component?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/themes": dict(
        title="Theme Management", desc="System and user-created themes.",
        placeholder="Search themes…", icon="palette", empty="No themes found",
        stats=[("Total", "$totals['all']", "palette", "brand"), ("System", "$totals['system']", "shield", "violet")],
        columns=[
            dict(label="Theme", sort="name", cell='''<div class="flex items-center gap-3 min-w-0">
                            <span class="flex gap-1 shrink-0">
                                @foreach(['primary','secondary','accent'] as $role)
                                    <span class="w-5 h-5 rounded-md ring-1 ring-black/8 dark:ring-white/10" style="background: {{ $row->colors[$role] ?? '#6366f1' }}"></span>
                                @endforeach
                            </span>
                            <p class="font-medium truncate">{{ $row->name }}</p>
                        </div>'''),
            dict(label="Typography", cell='<span class="text-xs text-tertiary">{{ $row->typography[\'heading_font\'] ?? \'Inter\' }}</span>'),
            dict(label="Scope", cell='<x-ui.badge :color="$row->user_id ? \'slate\' : \'violet\'">{{ $row->user_id ? \'User\' : \'System\' }}</x-ui.badge>'),
            dict(label="Status", cell='@if($row->is_default)<x-ui.badge color="emerald">Default</x-ui.badge>@else<x-ui.badge :color="$row->is_active ? \'sky\' : \'slate\'">{{ $row->is_active ? \'Active\' : \'Hidden\' }}</x-ui.badge>@endif'),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <button wire:click="makeDefault({{ $row->id }})" class="btn btn-ghost btn-icon" title="Make default"><x-icon name="star" class="w-4 h-4 {{ $row->is_default ? 'fill-amber-400 text-amber-400' : '' }}" /></button>
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/media": dict(
        title="All Media", desc="Every file stored on the platform.",
        placeholder="Search files…", icon="image", empty="No media found",
        stats=[("Total files", "number_format($totals['all'])", "image", "brand"),
               ("Storage", "$totals['size'].' MB'", "database", "violet")],
        filters=[("type", "All types", [("image", "Images"), ("video", "Video"), ("document", "Documents")])],
        columns=[
            dict(label="File", sort="original_name", cell='''<div class="flex items-center gap-3 min-w-0">
                            <span class="w-10 h-10 rounded-lg bg-surface-muted grid place-items-center overflow-hidden shrink-0">
                                @if($row->type === 'image')<img src="{{ $row->url }}" class="w-full h-full object-cover" alt="" loading="lazy">
                                @else<x-icon name="file" class="w-4 h-4 text-tertiary" />@endif
                            </span>
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $row->original_name }}</p>
                                <p class="text-xs text-tertiary">{{ round($row->size / 1024) }} KB</p>
                            </div>
                        </div>'''),
            dict(label="Owner", cell=USER_CELL),
            dict(label="Type", sort="type", cell='<x-ui.badge color="slate">{{ ucfirst($row->type) }}</x-ui.badge>'),
            dict(label="Uploaded", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='<button wire:click="deleteMedia({{ $row->id }})" wire:confirm="Delete this file?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>'),
        ],
    ),

    "super/subscriptions": dict(
        title="Subscriptions", desc="Active and historical plan subscriptions.",
        placeholder="Search by customer…", icon="credit-card", empty="No subscriptions found",
        stats=[("Active", "$totals['active']", "check-circle", "emerald"),
               ("Cancelled", "$totals['cancelled']", "x-circle", "rose"),
               ("Monthly value", "'$'.number_format($totals['mrr'], 2)", "dollar", "brand")],
        filters=[("status", "All statuses", [("trialing", "Trialing"), ("active", "Active"), ("past_due", "Past due"), ("canceled", "Canceled"), ("expired", "Expired")])],
        columns=[
            dict(label="Customer", cell=USER_CELL),
            dict(label="Plan", cell='<x-ui.badge color="brand">{{ $row->plan?->name }}</x-ui.badge>'),
            dict(label="Cycle", cell='<span class="text-xs capitalize">{{ $row->billing_cycle }}</span>'),
            dict(label="Amount", sort="amount", cell='<span class="font-semibold tabular-nums">${{ number_format((float) $row->amount, 2) }}</span>'),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->status->color()" dot>{{ $row->status->label() }}</x-ui.badge>'),
            dict(label="Renews", cell='<span class="text-xs text-tertiary whitespace-nowrap">{{ $row->current_period_end?->format(\'M j, Y\') ?? \'—\' }}</span>'),
            dict(label="", align="right", cell='@if($row->status->isUsable())<button wire:click="cancelSubscription({{ $row->id }})" wire:confirm="Cancel this subscription?" class="btn btn-ghost btn-sm text-rose-500">Cancel</button>@endif'),
        ],
    ),

    "super/invoices": dict(
        title="Invoices", desc="Every invoice issued by the platform.",
        placeholder="Search invoices…", icon="receipt", empty="No invoices found",
        stats=[("Paid total", "'$'.number_format($totals['paid'], 2)", "dollar", "emerald"),
               ("Pending", "$totals['pending']", "clock", "amber"),
               ("Refunded", "$totals['refunded']", "undo", "slate"),
               ("Invoices", "number_format($totals['count'])", "receipt", "brand")],
        filters=[("status", "All statuses", [("paid", "Paid"), ("pending", "Pending"), ("refunded", "Refunded"), ("failed", "Failed")])],
        columns=[
            dict(label="Invoice", sort="invoice_number", cell='<span class="font-mono text-xs">{{ $row->invoice_number }}</span>'),
            dict(label="Customer", cell=USER_CELL),
            dict(label="Plan", cell='<span class="text-xs text-tertiary">{{ $row->subscription?->plan?->name ?? \'—\' }}</span>'),
            dict(label="Total", sort="total", cell='<span class="font-semibold tabular-nums">${{ number_format((float) $row->total, 2) }}</span>'),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="match($row->status) { \'paid\' => \'emerald\', \'pending\' => \'amber\', \'refunded\' => \'slate\', default => \'rose\' }">{{ ucfirst($row->status) }}</x-ui.badge>'),
            dict(label="Date", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            @if($row->status !== 'paid')<button wire:click="markPaid({{ $row->id }})" class="btn btn-ghost btn-sm text-emerald-500">Mark paid</button>@endif
                            @if($row->status === 'paid')<button wire:click="refund({{ $row->id }})" wire:confirm="Refund this invoice?" class="btn btn-ghost btn-sm">Refund</button>@endif
                        </div>'''),
        ],
    ),

    "super/coupons": dict(
        title="Coupons", desc="Discount codes and promotional offers.",
        placeholder="Search coupons…", icon="tag", empty="No coupons found",
        stats=[("Total", "$totals['all']", "tag", "brand"),
               ("Active", "$totals['active']", "check-circle", "emerald"),
               ("Redemptions", "number_format($totals['redemptions'])", "download", "violet")],
        columns=[
            dict(label="Code", sort="code", cell='<code class="font-mono text-sm font-semibold">{{ $row->code }}</code>'),
            dict(label="Discount", cell='<x-ui.badge color="violet">{{ $row->display_value }}</x-ui.badge>'),
            dict(label="Used", cell='<span class="tabular-nums text-sm">{{ $row->redemptions }}@if($row->max_redemptions) / {{ $row->max_redemptions }}@endif</span>'),
            dict(label="Expires", cell='<span class="text-xs text-tertiary whitespace-nowrap">{{ $row->expires_at?->format(\'M j, Y\') ?? \'Never\' }}</span>'),
            dict(label="Status", cell='<x-ui.badge :color="$row->isRedeemable() ? \'emerald\' : \'slate\'" dot>{{ $row->isRedeemable() ? \'Active\' : \'Inactive\' }}</x-ui.badge>'),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteCoupon({{ $row->id }})" wire:confirm="Delete this coupon?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/blog": dict(
        title="Blog Management", desc="All blog content across the platform.",
        placeholder="Search posts…", icon="book", empty="No posts found",
        stats=[("Total", "$totals['all']", "book", "brand"),
               ("Published", "$totals['published']", "check-circle", "emerald"),
               ("Drafts", "$totals['draft']", "edit", "amber")],
        filters=[("status", "All statuses", [("published", "Published"), ("draft", "Draft")])],
        columns=[
            dict(label="Post", sort="title", cell='''<div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->title }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->excerpt, 60) }}</p>
                        </div>'''),
            dict(label="Author", cell=USER_CELL),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->status === \'published\' ? \'emerald\' : \'amber\'" dot>{{ ucfirst($row->status) }}</x-ui.badge>'),
            dict(label="Date", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            @if($row->status !== 'published')<button wire:click="publishPost({{ $row->id }})" class="btn btn-ghost btn-icon text-emerald-500"><x-icon name="rocket" class="w-4 h-4" /></button>@endif
                            <button wire:click="deletePost({{ $row->id }})" wire:confirm="Delete this post?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/cms-pages": dict(
        title="CMS Pages", desc="Marketing and legal pages on the public site.",
        placeholder="Search pages…", icon="file", empty="No CMS pages found",
        stats=[("Total", "$totals['all']", "file", "brand"), ("Published", "$totals['published']", "check-circle", "emerald")],
        columns=[
            dict(label="Page", sort="title", cell='''<div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->title }}</p>
                            <p class="text-xs text-tertiary font-mono truncate">/p/{{ $row->slug }}</p>
                        </div>'''),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->status === \'published\' ? \'emerald\' : \'amber\'" dot>{{ ucfirst($row->status) }}</x-ui.badge>'),
            dict(label="Updated", sort="updated_at", cell='<span class="text-xs text-tertiary whitespace-nowrap">{{ $row->updated_at->diffForHumans(short: true) }}</span>'),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <a href="{{ route('page.show', $row->slug) }}" target="_blank" class="btn btn-ghost btn-icon"><x-icon name="external" class="w-4 h-4" /></a>
                            <button wire:click="togglePublished({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->status === 'published' ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                            <button wire:click="deletePage({{ $row->id }})" wire:confirm="Delete this page?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/faqs": dict(
        title="FAQ Management", desc="Questions shown on the public help pages.",
        placeholder="Search questions…", icon="help", empty="No FAQs found",
        stats=[("Total", "$totals['all']", "help", "brand"), ("Active", "$totals['active']", "check-circle", "emerald")],
        columns=[
            dict(label="Question", sort="question", cell='''<div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->question }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->answer, 70) }}</p>
                        </div>'''),
            dict(label="Category", sort="category", cell='<x-ui.badge color="slate">{{ ucfirst($row->category ?: \'general\') }}</x-ui.badge>'),
            dict(label="Status", cell='<x-ui.badge :color="$row->is_active ? \'emerald\' : \'slate\'" dot>{{ $row->is_active ? \'Visible\' : \'Hidden\' }}</x-ui.badge>'),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <button wire:click="toggleActive({{ $row->id }})" class="btn btn-ghost btn-icon"><x-icon name="{{ $row->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4" /></button>
                            <button wire:click="deleteFaq({{ $row->id }})" wire:confirm="Delete this FAQ?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/contacts": dict(
        title="Contact Messages", desc="Enquiries submitted through the public contact form.",
        placeholder="Search messages…", icon="mail", empty="No messages found",
        stats=[("Total", "$totals['all']", "mail", "brand"),
               ("Unread", "$totals['unread']", "bell", "amber"),
               ("Replied", "$totals['replied']", "check-circle", "emerald")],
        filters=[("status", "All statuses", [("unread", "Unread"), ("read", "Read"), ("replied", "Replied")])],
        columns=[
            dict(label="From", sort="name", cell='''<div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary truncate">{{ $row->email }}</p>
                        </div>'''),
            dict(label="Subject", cell='''<div class="min-w-0">
                            <p class="text-sm truncate">{{ $row->subject ?: '(no subject)' }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->message, 60) }}</p>
                        </div>'''),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="match($row->status) { \'unread\' => \'amber\', \'replied\' => \'emerald\', default => \'slate\' }" dot>{{ ucfirst($row->status) }}</x-ui.badge>'),
            dict(label="Received", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <button wire:click="markRead({{ $row->id }})" class="btn btn-ghost btn-icon" title="Mark read"><x-icon name="eye" class="w-4 h-4" /></button>
                            <button wire:click="markReplied({{ $row->id }})" class="btn btn-ghost btn-icon text-emerald-500" title="Mark replied"><x-icon name="check" class="w-4 h-4" /></button>
                            <button wire:click="deleteMessage({{ $row->id }})" wire:confirm="Delete this message?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/ai-logs": dict(
        title="AI Generation Logs", desc="Every request sent through the AI engine.",
        placeholder="Search prompts…", icon="sparkles", empty="No generations recorded",
        stats=[("Total", "number_format($totals['all'])", "sparkles", "brand"),
               ("Completed", "number_format($totals['completed'])", "check-circle", "emerald"),
               ("Failed", "$totals['failed']", "x-circle", "rose"),
               ("Credits used", "number_format($totals['credits'])", "zap", "violet")],
        filters=[("status", "All statuses", [("completed", "Completed"), ("failed", "Failed"), ("pending", "Pending")])],
        columns=[
            dict(label="Type", sort="type", cell='<x-ui.badge color="violet">{{ $row->type->label() }}</x-ui.badge>'),
            dict(label="Prompt", cell='<span class="text-sm text-secondary">{{ Str::limit($row->prompt, 60) }}</span>'),
            dict(label="User", cell=USER_CELL),
            dict(label="Credits", sort="credits_used", cell='<span class="tabular-nums">{{ $row->credits_used }}</span>'),
            dict(label="Duration", sort="duration_ms", cell='<span class="tabular-nums text-xs text-tertiary">{{ $row->duration_ms }}ms</span>'),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->status === \'completed\' ? \'emerald\' : ($row->status === \'failed\' ? \'rose\' : \'amber\')" dot>{{ ucfirst($row->status) }}</x-ui.badge>'),
            dict(label="When", sort="created_at", cell=DATE),
        ],
    ),

    "super/security-logs": dict(
        title="Security Logs", desc="Authentication events and security-relevant actions.",
        placeholder="Search events…", icon="shield", empty="No security events recorded",
        stats=[("Total events", "number_format($totals['all'])", "shield", "brand"),
               ("Warnings", "$totals['warnings']", "alert", "amber"),
               ("Today", "$totals['today']", "clock", "sky")],
        filters=[("level", "All levels", [("info", "Info"), ("success", "Success"), ("warning", "Warning"), ("critical", "Critical")])],
        columns=[
            dict(label="Event", sort="event", cell='<x-ui.badge :color="match($row->level) { \'warning\' => \'amber\', \'critical\' => \'rose\', \'success\' => \'emerald\', default => \'slate\' }">{{ Str::headline($row->event) }}</x-ui.badge>'),
            dict(label="User", cell=USER_CELL),
            dict(label="IP address", cell='<span class="font-mono text-xs">{{ $row->ip_address ?? \'—\' }}</span>'),
            dict(label="User agent", cell='<span class="text-xs text-tertiary">{{ Str::limit($row->user_agent ?? \'—\', 40) }}</span>'),
            dict(label="When", sort="created_at", cell=DATE),
        ],
    ),

    "super/activity-logs": dict(
        title="Audit & Activity Logs", desc="A complete, immutable audit trail.",
        placeholder="Search activity…", icon="activity", empty="No activity recorded",
        filters=[("event", "All events", "events")],
        stats=[("Total entries", "number_format($totals['all'])", "activity", "brand"),
               ("Today", "$totals['today']", "clock", "sky")],
        columns=[
            dict(label="Event", sort="event", cell='<x-ui.badge :color="$row->eventColor()">{{ Str::headline($row->event) }}</x-ui.badge>'),
            dict(label="Description", cell='<span class="text-sm">{{ $row->description }}</span>'),
            dict(label="User", cell=USER_CELL),
            dict(label="IP", cell='<span class="font-mono text-xs text-tertiary">{{ $row->ip_address ?? \'—\' }}</span>'),
            dict(label="When", sort="created_at", cell=DATE),
        ],
    ),

    "super/backups": dict(
        title="Backup & Restore", desc="Database and file snapshots.",
        placeholder="Search backups…", icon="database", empty="No backups yet",
        empty_desc="Create your first snapshot to protect your data.",
        actions=['<button wire:click="createBackup" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Create backup</button>'],
        stats=[("Backups", "$totals['all']", "database", "brand"),
               ("Total size", "$totals['size'].' MB'", "archive", "violet")],
        columns=[
            dict(label="Backup", sort="name", cell='''<div class="min-w-0">
                            <p class="font-medium truncate font-mono text-xs">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary">{{ $row->human_size }}</p>
                        </div>'''),
            dict(label="Type", sort="type", cell='<x-ui.badge :color="$row->type === \'manual\' ? \'sky\' : \'slate\'">{{ ucfirst($row->type) }}</x-ui.badge>'),
            dict(label="Status", sort="status", cell='<x-ui.badge :color="$row->statusColor()" dot>{{ ucfirst($row->status) }}</x-ui.badge>'),
            dict(label="Created", sort="created_at", cell=DATE),
            dict(label="", align="right", cell='''<div class="flex items-center justify-end gap-1">
                            <button class="btn btn-ghost btn-icon" title="Download"><x-icon name="download" class="w-4 h-4" /></button>
                            <button wire:click="deleteBackup({{ $row->id }})" wire:confirm="Delete this backup?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div>'''),
        ],
    ),

    "super/api-keys": dict(
        title="API Key Management", desc="Issue and revoke platform API credentials.",
        placeholder="Search keys…", icon="key", empty="No API keys issued",
        actions=['<button wire:click="$set(\'showCreate\', true)" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Issue key</button>'],
        stats=[("Total keys", "$totals['all']", "key", "brand"),
               ("Active", "$totals['active']", "check-circle", "emerald"),
               ("Requests", "number_format($totals['requests'])", "activity", "violet")],
        columns=[
            dict(label="Key", sort="name", cell='''<div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary font-mono">{{ $row->masked }}</p>
                        </div>'''),
            dict(label="Owner", cell=USER_CELL),
            dict(label="Rate limit", cell='<span class="text-xs tabular-nums">{{ $row->rate_limit }}/min</span>'),
            dict(label="Requests", sort="usage_count", cell='<span class="tabular-nums">{{ number_format($row->usage_count) }}</span>'),
            dict(label="Last used", cell='<span class="text-xs text-tertiary whitespace-nowrap">{{ $row->last_used_at?->diffForHumans(short: true) ?? \'Never\' }}</span>'),
            dict(label="", align="right", cell='<button wire:click="revoke({{ $row->id }})" wire:confirm="Revoke this API key?" class="btn btn-ghost btn-sm text-rose-500">Revoke</button>'),
        ],
        extra='''
    @if($plainKey)
        <div class="rounded-2xl bg-emerald-500/10 ring-1 ring-emerald-500/25 p-5">
            <div class="flex items-start gap-3">
                <x-icon name="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm">New API key generated</p>
                    <p class="text-xs text-secondary mt-1">Copy it now — it will never be shown again.</p>
                    <div class="flex items-center gap-2 mt-3" x-data="copyable(@js($plainKey))">
                        <code class="flex-1 px-3 py-2 rounded-lg bg-surface border border-subtle font-mono text-xs truncate">{{ $plainKey }}</code>
                        <button x-on:click="copy()" class="btn btn-secondary btn-sm shrink-0">
                            <x-icon name="copy" class="w-3.5 h-3.5" /> <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                </div>
                <button wire:click="$set('plainKey', null)" class="btn btn-ghost btn-icon shrink-0"><x-icon name="x" class="w-4 h-4" /></button>
            </div>
        </div>
    @endif

    @if($showCreate)
        <div class="fixed inset-0 z-[70] grid place-items-center p-4">
            <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" wire:click="$set('showCreate', false)"></div>
            <div class="relative card shadow-2xl max-w-lg w-full p-6 animate-[scale-in_.24s_cubic-bezier(.16,1,.3,1)_both]">
                <h3 class="text-lg font-semibold">Issue a new API key</h3>
                <form wire:submit="createKey" class="space-y-4 mt-5">
                    <div>
                        <label class="label">Key name</label>
                        <input wire:model="keyName" type="text" class="field @error('keyName') field-error @enderror" placeholder="Production integration">
                        @error('keyName')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Owner</label>
                            <select wire:model="ownerId" class="field">
                                <option value="">Platform (no owner)</option>
                                @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Rate limit (per minute)</label>
                            <input wire:model="rateLimit" type="number" min="10" class="field">
                        </div>
                    </div>
                    <div>
                        <label class="label">Abilities</label>
                        <div class="grid sm:grid-cols-2 gap-2">
                            @foreach($allAbilities as $key => $label)
                                <button type="button" wire:click="toggleAbility('{{ $key }}')"
                                        @class(['px-3 py-2 rounded-lg text-xs font-medium text-left transition-all border',
                                                'bg-brand-500/12 border-brand-500/30 text-brand-600 dark:text-brand-300' => in_array($key, $abilities),
                                                'border-subtle text-tertiary hover:text-secondary' => !in_array($key, $abilities)])>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="label">Expires on <span class="text-tertiary font-normal">(optional)</span></label>
                        <input wire:model="expiresAt" type="date" class="field">
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="$set('showCreate', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate key</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
''',
    ),
}


def main():
    for path, spec in SPECS.items():
        out = VIEWS / f"{path}.blade.php"
        out.parent.mkdir(parents=True, exist_ok=True)
        out.write_text(render(spec))
        print(f"  {out.relative_to(ROOT)}")

    print(f"\n{len(SPECS)} resource views generated.")


if __name__ == "__main__":
    main()
