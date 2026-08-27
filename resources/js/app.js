import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import Chart from 'chart.js/auto';

Alpine.plugin(collapse);
Alpine.plugin(focus);

window.Alpine = Alpine;
window.Chart = Chart;

/* ---------------------------------------------------------------------------
 | Theme (light / dark / system)
 * ------------------------------------------------------------------------ */
const THEME_KEY = 'aurora-theme';

function applyTheme(mode) {
    const dark = mode === 'dark' ||
        (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
}

window.setTheme = (mode) => {
    localStorage.setItem(THEME_KEY, mode);
    applyTheme(mode);
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { mode } }));
};

applyTheme(localStorage.getItem(THEME_KEY) || 'system');

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if ((localStorage.getItem(THEME_KEY) || 'system') === 'system') applyTheme('system');
});

/* ---------------------------------------------------------------------------
 | Alpine stores & components
 * ------------------------------------------------------------------------ */
Alpine.store('ui', {
    sidebarOpen: false,
    commandOpen: false,
    theme: localStorage.getItem(THEME_KEY) || 'system',

    toggleSidebar() { this.sidebarOpen = !this.sidebarOpen },

    setTheme(mode) {
        this.theme = mode;
        window.setTheme(mode);
    },

    cycleTheme() {
        const order = ['light', 'dark', 'system'];
        this.setTheme(order[(order.indexOf(this.theme) + 1) % order.length]);
    },
});

Alpine.store('toasts', {
    items: [],

    push(message, type = 'success', timeout = 4200) {
        const id = Date.now() + Math.random();
        this.items.push({ id, message, type });
        setTimeout(() => this.remove(id), timeout);
    },

    remove(id) {
        this.items = this.items.filter((t) => t.id !== id);
    },
});

Alpine.data('chart', (config) => ({
    instance: null,

    init() {
        this.render();
        window.addEventListener('theme-changed', () => this.render());
    },

    render() {
        if (this.instance) this.instance.destroy();

        const dark = document.documentElement.classList.contains('dark');
        const grid = dark ? 'rgba(148,163,184,.14)' : 'rgba(15,23,42,.07)';
        const tick = dark ? '#94a3b8' : '#64748b';

        const merged = structuredClone(config);
        merged.options = merged.options || {};
        merged.options.responsive = true;
        merged.options.maintainAspectRatio = false;
        merged.options.plugins = {
            legend: { display: false },
            tooltip: {
                backgroundColor: dark ? '#0f172a' : '#fff',
                titleColor: dark ? '#f1f5f9' : '#0f172a',
                bodyColor: dark ? '#cbd5e1' : '#475569',
                borderColor: dark ? '#1e293b' : '#e2e8f0',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 10,
                displayColors: false,
                titleFont: { family: 'Inter', weight: '600', size: 12 },
                bodyFont: { family: 'Inter', size: 12 },
            },
            ...(merged.options.plugins || {}),
        };

        if (merged.type !== 'doughnut' && merged.type !== 'pie') {
            merged.options.scales = {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: tick, font: { family: 'Inter', size: 11 } } },
                y: { grid: { color: grid }, border: { display: false }, ticks: { color: tick, font: { family: 'Inter', size: 11 } }, beginAtZero: true },
                ...(merged.options.scales || {}),
            };
        }

        const ctx = this.$refs.canvas.getContext('2d');

        // gradient fills for line/area charts
        (merged.data.datasets || []).forEach((ds) => {
            if (ds.__gradient) {
                const g = ctx.createLinearGradient(0, 0, 0, this.$refs.canvas.offsetHeight || 240);
                g.addColorStop(0, ds.__gradient[0]);
                g.addColorStop(1, ds.__gradient[1]);
                ds.backgroundColor = g;
            }
        });

        this.instance = new Chart(ctx, merged);
    },
}));

Alpine.data('copyable', (text) => ({
    copied: false,
    async copy() {
        try {
            await navigator.clipboard.writeText(text);
        } catch (e) {
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            el.remove();
        }
        this.copied = true;
        setTimeout(() => (this.copied = false), 1800);
    },
}));

Alpine.start();

/* Livewire toast bridge */
document.addEventListener('livewire:init', () => {
    if (window.Livewire) {
        window.Livewire.on('toast', (payload) => {
            const data = Array.isArray(payload) ? payload[0] : payload;
            Alpine.store('toasts').push(data.message, data.type || 'success');
        });
    }
});
