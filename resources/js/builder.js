import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import grapesjs from 'grapesjs';
import 'grapesjs/dist/css/grapes.min.css';

/**
 * AuroraBuild visual editor.
 *
 * GrapesJS owns the canvas; Livewire owns persistence. The bridge below is
 * intentionally narrow — only serialised page content crosses the boundary.
 */
function builderEditor(config) {
    return {
        editor: null,
        device: 'desktop',
        saving: false,
        dirty: false,
        panel: 'blocks',
        savedLabel: config.lastSavedAt || 'Not saved yet',

        init() {
            this.editor = grapesjs.init({
                container: this.$refs.canvas,
                height: '100%',
                width: 'auto',
                fromElement: false,
                storageManager: false,
                undoManager: { trackSelectorChanges: true },
                assetManager: { assets: config.assets || [], upload: false, autoAdd: true },
                selectorManager: { componentFirst: true },
                blockManager: { appendTo: this.$refs.blocks, blocks: config.blocks || [] },
                styleManager: { appendTo: this.$refs.styles, sectors: styleSectors() },
                traitManager: { appendTo: this.$refs.traits },
                layerManager: { appendTo: this.$refs.layers },
                panels: { defaults: [] },
                deviceManager: {
                    devices: [
                        { id: 'desktop', name: 'Desktop', width: '' },
                        { id: 'laptop', name: 'Laptop', width: '1024px', widthMedia: '1200px' },
                        { id: 'tablet', name: 'Tablet', width: '768px', widthMedia: '992px' },
                        { id: 'mobile', name: 'Mobile', width: '375px', widthMedia: '575px' },
                    ],
                },
                canvas: {
                    styles: [
                        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
                    ],
                },
            });

            this.loadContent(config.content);
            this.registerCommands();
            this.bindEvents();
            this.bindLivewire();
        },

        /* -------------------------------------------------------------- */

        loadContent(content) {
            if (!content) return;

            if (content.components) {
                this.editor.setComponents(content.components);
                this.editor.setStyle(content.styles || []);
            } else {
                this.editor.setComponents(content.html || '');
                this.editor.setStyle(content.css || '');
            }

            this.editor.UndoManager.clear();
            this.dirty = false;
        },

        registerCommands() {
            const ed = this.editor;

            ed.Commands.add('core:preview-toggle', {
                run: (e) => { e.stopCommand('sw-visibility'); this.panel = 'preview'; },
                stop: (e) => { e.runCommand('sw-visibility'); this.panel = 'blocks'; },
            });

            ed.Commands.add('aurora:clear', {
                run: () => {
                    if (confirm('Remove everything on this page? This can be undone with Ctrl+Z.')) {
                        ed.DomComponents.clear();
                        ed.CssComposer.clear();
                    }
                },
            });
        },

        bindEvents() {
            const ed = this.editor;
            const markDirty = () => {
                if (!this.dirty) {
                    this.dirty = true;
                    this.$wire.dispatch('canvas-dirty');
                }
            };

            ['component:add', 'component:remove', 'component:update', 'style:update', 'canvas:drop']
                .forEach((evt) => ed.on(evt, markDirty));

            // Autosave 4s after the last edit so work is never lost.
            let timer = null;
            ed.on('update', () => {
                clearTimeout(timer);
                timer = setTimeout(() => { if (this.dirty) this.save(); }, 4000);
            });

            document.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 's') { e.preventDefault(); this.save(); }
            });

            window.addEventListener('beforeunload', (e) => {
                if (this.dirty) { e.preventDefault(); e.returnValue = ''; }
            });
        },

        bindLivewire() {
            window.addEventListener('page-switched', (e) => {
                this.loadContent(e.detail.content ?? e.detail[0]?.content);
            });

            window.addEventListener('canvas-saved', () => {
                this.saving = false;
                this.dirty = false;
                this.savedLabel = 'Saved just now';
            });
        },

        /* -------------------------------------------------------------- */

        save() {
            if (this.saving) return;

            this.saving = true;
            const ed = this.editor;

            this.$wire.dispatch('canvas-save', {
                payload: {
                    html: ed.getHtml(),
                    css: ed.getCss(),
                    components: ed.getComponents().toJSON(),
                    styles: ed.getStyle().toJSON ? ed.getStyle().toJSON() : ed.getStyle(),
                    assets: ed.AssetManager.getAll().toJSON(),
                },
            });
        },

        setDevice(device) {
            this.device = device;
            this.editor.setDevice(device.charAt(0).toUpperCase() + device.slice(1));
        },

        setPanel(panel) { this.panel = panel; },
        undo() { this.editor.UndoManager.undo(); },
        redo() { this.editor.UndoManager.redo(); },
        togglePreview() { this.editor.runCommand('core:preview'); },
        toggleBorders() { this.editor.runCommand('sw-visibility'); },
        toggleCode() { this.editor.runCommand('export-template'); },
        clearCanvas() { this.editor.runCommand('aurora:clear'); },
    };
}

/** Style Manager sectors — grouped to mirror how designers actually work. */
function styleSectors() {
    return [
        {
            name: 'Layout', open: true,
            properties: [
                { property: 'display', type: 'select', default: 'block',
                  options: [{ id: 'block' }, { id: 'flex' }, { id: 'grid' }, { id: 'inline-block' }, { id: 'none' }] },
                { property: 'flex-direction', type: 'select', options: [{ id: 'row' }, { id: 'column' }] },
                { property: 'justify-content', type: 'select',
                  options: [{ id: 'flex-start' }, { id: 'center' }, { id: 'flex-end' }, { id: 'space-between' }, { id: 'space-around' }] },
                { property: 'align-items', type: 'select',
                  options: [{ id: 'flex-start' }, { id: 'center' }, { id: 'flex-end' }, { id: 'stretch' }] },
                { property: 'gap' }, { property: 'grid-template-columns' },
            ],
        },
        {
            name: 'Spacing', open: true,
            properties: [
                { property: 'padding', properties: [
                    { name: 'Top', property: 'padding-top' }, { name: 'Right', property: 'padding-right' },
                    { name: 'Bottom', property: 'padding-bottom' }, { name: 'Left', property: 'padding-left' }] },
                { property: 'margin', properties: [
                    { name: 'Top', property: 'margin-top' }, { name: 'Right', property: 'margin-right' },
                    { name: 'Bottom', property: 'margin-bottom' }, { name: 'Left', property: 'margin-left' }] },
            ],
        },
        {
            name: 'Size', open: false,
            properties: ['width', 'height', 'max-width', 'min-height'],
        },
        {
            name: 'Typography', open: false,
            properties: [
                { property: 'font-family', type: 'select', options: [
                    { id: 'Inter, sans-serif', label: 'Inter' }, { id: 'Manrope, sans-serif', label: 'Manrope' },
                    { id: 'Poppins, sans-serif', label: 'Poppins' }, { id: '"DM Sans", sans-serif', label: 'DM Sans' },
                    { id: '"Space Grotesk", sans-serif', label: 'Space Grotesk' },
                    { id: '"Playfair Display", serif', label: 'Playfair Display' },
                    { id: 'Georgia, serif', label: 'Georgia' }, { id: 'monospace', label: 'Monospace' }] },
                'font-size', 'font-weight', 'line-height', 'letter-spacing', 'color',
                { property: 'text-align', type: 'radio',
                  options: [{ id: 'left' }, { id: 'center' }, { id: 'right' }, { id: 'justify' }] },
                { property: 'text-transform', type: 'select',
                  options: [{ id: 'none' }, { id: 'uppercase' }, { id: 'capitalize' }, { id: 'lowercase' }] },
            ],
        },
        {
            name: 'Backgrounds', open: false,
            properties: ['background-color', 'background-image', 'background-size', 'background-position', 'background-repeat'],
        },
        {
            name: 'Borders & Corners', open: false,
            properties: [
                { property: 'border-radius', properties: [
                    { name: 'TL', property: 'border-top-left-radius' }, { name: 'TR', property: 'border-top-right-radius' },
                    { name: 'BR', property: 'border-bottom-right-radius' }, { name: 'BL', property: 'border-bottom-left-radius' }] },
                'border', 'box-shadow',
            ],
        },
        {
            name: 'Effects', open: false,
            properties: ['opacity', 'transform', 'transition', 'filter', 'cursor', 'overflow'],
        },
        {
            name: 'Position', open: false,
            properties: [
                { property: 'position', type: 'select',
                  options: [{ id: 'static' }, { id: 'relative' }, { id: 'absolute' }, { id: 'fixed' }, { id: 'sticky' }] },
                'top', 'right', 'bottom', 'left', 'z-index',
            ],
        },
    ];
}

window.builderEditor = builderEditor;
Alpine.data('builderEditor', builderEditor);

Alpine.plugin(collapse);
Alpine.plugin(focus);

window.Alpine = Alpine;
Alpine.start();
