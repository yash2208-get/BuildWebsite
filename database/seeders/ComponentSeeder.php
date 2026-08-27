<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Component;
use App\Services\Ai\Copywriter;
use App\Services\Ai\PaletteFactory;
use App\Services\Ai\SectionRenderer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the drag-and-drop block library used by the builder.
 * Blocks are rendered with the same SectionRenderer the AI engine uses, so
 * everything on the canvas shares one visual language.
 */
class ComponentSeeder extends Seeder
{
    public function run(): void
    {
        $palette = (new PaletteFactory('library'))->make('indigo');
        $r = new SectionRenderer($palette);
        $w = new Copywriter('component-library');
        $nav = $w->navigation();

        $blocks = [
            // Structure -----------------------------------------------------
            ['Navbar — Classic', 'navbar', 'menu', $r->navbar('Brand', $nav)],
            ['Header — Hero + Nav', 'header', 'layout', $r->navbar('Brand', $nav)."\n".$r->hero('A headline that sells', 'Supporting copy that explains the value in one clear sentence.', 'hero')],
            ['Footer — Complete', 'footer', 'layout', $r->footer('Brand', $nav)],

            // Hero ----------------------------------------------------------
            ['Hero — Split with visual', 'hero', 'sparkles', $r->hero('Build something people love', 'Launch a beautiful, fast website in minutes — no code required.', 'product shot')],
            ['Hero — Centered', 'hero', 'sparkles', $this->centeredHero()],

            // Marketing -----------------------------------------------------
            ['Features — 3 column grid', 'features', 'grid', $r->features($w->features('saas', 6))],
            ['Features — 4 column', 'features', 'grid', $r->features($w->features('agency', 4))],
            ['Stats — Metric bar', 'stats', 'chart', $r->stats($w->stats())],
            ['Logo Cloud', 'logos', 'badge', $r->logos()],
            ['Pricing — 3 tiers', 'pricing', 'tag', $r->pricing($w->pricing())],
            ['Testimonials — 3 quotes', 'testimonials', 'quote', $r->testimonials($w->testimonials('Brand', 3))],
            ['FAQ — Accordion', 'faq', 'help', $r->faq($w->faqs('Brand', 5))],
            ['Team — Member grid', 'team', 'users', $r->team($w->team(4))],
            ['About — Split content', 'content', 'text', $r->about('Built with intent', $w->about('Brand', 'saas'), 'office')],
            ['Call To Action', 'cta', 'megaphone', $r->cta('Ready to get started?')],
            ['Gallery — Masonry tiles', 'gallery', 'image', $r->gallery($w->imageKeywords('agency'))],
            ['Contact — Form + details', 'contact', 'mail', $r->contact('Brand')],

            // Elements ------------------------------------------------------
            ['Buttons — Group', 'content', 'cursor', $this->buttons()],
            ['Cards — 3 up', 'content', 'card', $this->cards()],
            ['Tabs', 'content', 'tabs', $this->tabs()],
            ['Accordion', 'content', 'list', $this->accordion()],
            ['Countdown timer', 'content', 'clock', $this->countdown()],
            ['Video embed', 'media', 'play', $this->video()],
            ['Map embed', 'contact', 'map', $this->map()],
            ['Slider / Carousel', 'media', 'slides', $this->carousel()],
            ['Newsletter signup', 'forms', 'mail', $this->newsletter()],
            ['Contact form — Simple', 'forms', 'form', $this->simpleForm()],
            ['Blog post grid', 'blog', 'book', $this->blogGrid()],
            ['Popup / Modal', 'content', 'window', $this->popup()],
            ['Divider', 'content', 'minus', '<div style="height:1px;background:rgba(15,23,42,.1);margin:48px auto;width:min(1160px,92vw)"></div>'],
            ['Spacer', 'content', 'space', '<div style="height:72px"></div>'],
        ];

        $css = $r->stylesheet();

        foreach ($blocks as $i => [$name, $category, $icon, $html]) {
            Component::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'category' => $category,
                    'icon' => $icon,
                    'description' => $name.' block',
                    'html' => $html,
                    'css' => $css,
                    'is_global' => true,
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );
        }
    }

    /* ------------------------------------------------------------------ */

    private function centeredHero(): string
    {
        return <<<'HTML'
        <section class="ab-hero" data-gjs-name="Hero Centered">
          <div class="ab-container" style="text-align:center;max-width:820px">
            <span class="ab-pill">Introducing</span>
            <h1 class="ab-hero__title">The fastest way to launch your next idea</h1>
            <p class="ab-hero__sub" style="margin-inline:auto">Everything you need to design, publish and grow — in a single, beautifully simple platform.</p>
            <div class="ab-hero__cta" style="justify-content:center">
              <a class="ab-btn ab-btn--primary ab-btn--lg" href="#">Start for free</a>
              <a class="ab-btn ab-btn--ghost ab-btn--lg" href="#">Book a demo</a>
            </div>
          </div>
        </section>
        HTML;
    }

    private function buttons(): string
    {
        return <<<'HTML'
        <div class="ab-container" style="padding:48px 0;display:flex;gap:12px;flex-wrap:wrap" data-gjs-name="Buttons">
          <a class="ab-btn ab-btn--primary" href="#">Primary action</a>
          <a class="ab-btn ab-btn--ghost" href="#">Secondary</a>
          <a class="ab-btn ab-btn--primary ab-btn--lg" href="#">Large primary</a>
        </div>
        HTML;
    }

    private function cards(): string
    {
        return <<<'HTML'
        <section class="ab-section" data-gjs-name="Cards">
          <div class="ab-container ab-grid ab-grid--3">
            <article class="ab-feature"><span class="ab-feature__icon"></span><h3 class="ab-feature__title">Card one</h3><p class="ab-feature__body">Short supporting copy that describes this card.</p><a class="ab-btn ab-btn--ghost" href="#">Learn more</a></article>
            <article class="ab-feature"><span class="ab-feature__icon"></span><h3 class="ab-feature__title">Card two</h3><p class="ab-feature__body">Short supporting copy that describes this card.</p><a class="ab-btn ab-btn--ghost" href="#">Learn more</a></article>
            <article class="ab-feature"><span class="ab-feature__icon"></span><h3 class="ab-feature__title">Card three</h3><p class="ab-feature__body">Short supporting copy that describes this card.</p><a class="ab-btn ab-btn--ghost" href="#">Learn more</a></article>
          </div>
        </section>
        HTML;
    }

    private function tabs(): string
    {
        return <<<'HTML'
        <section class="ab-section" data-gjs-name="Tabs">
          <div class="ab-container ab-container--narrow">
            <div class="ab-tabs" style="display:flex;gap:8px;border-bottom:1px solid rgba(15,23,42,.1);margin-bottom:24px">
              <button style="padding:10px 16px;border:0;background:none;font:600 15px Inter,sans-serif;border-bottom:2px solid var(--ab-primary);color:var(--ab-primary);cursor:pointer">Overview</button>
              <button style="padding:10px 16px;border:0;background:none;font:600 15px Inter,sans-serif;border-bottom:2px solid transparent;color:var(--ab-muted);cursor:pointer">Details</button>
              <button style="padding:10px 16px;border:0;background:none;font:600 15px Inter,sans-serif;border-bottom:2px solid transparent;color:var(--ab-muted);cursor:pointer">Pricing</button>
            </div>
            <p style="color:var(--ab-muted)">Tab panel content goes here. Click a tab in the editor to change which panel is shown.</p>
          </div>
        </section>
        HTML;
    }

    private function accordion(): string
    {
        return <<<'HTML'
        <section class="ab-section" data-gjs-name="Accordion">
          <div class="ab-container ab-container--narrow ab-faq">
            <details class="ab-faq__item" open><summary>First item</summary><p>Content revealed when this row is expanded.</p></details>
            <details class="ab-faq__item"><summary>Second item</summary><p>Content revealed when this row is expanded.</p></details>
            <details class="ab-faq__item"><summary>Third item</summary><p>Content revealed when this row is expanded.</p></details>
          </div>
        </section>
        HTML;
    }

    private function countdown(): string
    {
        return <<<'HTML'
        <section class="ab-section" data-gjs-name="Countdown">
          <div class="ab-container" style="text-align:center">
            <span class="ab-eyebrow">Launching soon</span>
            <h2 class="ab-section__title">Doors open in</h2>
            <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-top:24px" data-countdown="2026-12-31T00:00:00Z">
              <div style="min-width:96px;padding:20px;border-radius:16px;background:var(--ab-surface);border:1px solid rgba(15,23,42,.08)"><strong style="display:block;font-size:34px;letter-spacing:-.03em" data-unit="days">14</strong><span style="font-size:12px;text-transform:uppercase;letter-spacing:.1em;color:var(--ab-muted)">Days</span></div>
              <div style="min-width:96px;padding:20px;border-radius:16px;background:var(--ab-surface);border:1px solid rgba(15,23,42,.08)"><strong style="display:block;font-size:34px;letter-spacing:-.03em" data-unit="hours">06</strong><span style="font-size:12px;text-transform:uppercase;letter-spacing:.1em;color:var(--ab-muted)">Hours</span></div>
              <div style="min-width:96px;padding:20px;border-radius:16px;background:var(--ab-surface);border:1px solid rgba(15,23,42,.08)"><strong style="display:block;font-size:34px;letter-spacing:-.03em" data-unit="minutes">42</strong><span style="font-size:12px;text-transform:uppercase;letter-spacing:.1em;color:var(--ab-muted)">Minutes</span></div>
              <div style="min-width:96px;padding:20px;border-radius:16px;background:var(--ab-surface);border:1px solid rgba(15,23,42,.08)"><strong style="display:block;font-size:34px;letter-spacing:-.03em" data-unit="seconds">09</strong><span style="font-size:12px;text-transform:uppercase;letter-spacing:.1em;color:var(--ab-muted)">Seconds</span></div>
            </div>
          </div>
        </section>
        HTML;
    }

    private function video(): string
    {
        return <<<'HTML'
        <section class="ab-section" data-gjs-name="Video">
          <div class="ab-container" style="max-width:900px">
            <div style="position:relative;aspect-ratio:16/9;border-radius:18px;overflow:hidden;box-shadow:var(--ab-shadow);background:#0f172a">
              <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Video" style="position:absolute;inset:0;width:100%;height:100%;border:0" allowfullscreen loading="lazy"></iframe>
            </div>
          </div>
        </section>
        HTML;
    }

    private function map(): string
    {
        return <<<'HTML'
        <section class="ab-section" data-gjs-name="Map">
          <div class="ab-container">
            <div style="aspect-ratio:21/9;border-radius:18px;overflow:hidden;box-shadow:var(--ab-shadow)">
              <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=-0.16%2C51.49%2C-0.10%2C51.52&layer=mapnik" title="Map" style="width:100%;height:100%;border:0" loading="lazy"></iframe>
            </div>
          </div>
        </section>
        HTML;
    }

    private function carousel(): string
    {
        return <<<'HTML'
        <section class="ab-section" data-gjs-name="Carousel">
          <div class="ab-container">
            <div style="display:flex;gap:16px;overflow-x:auto;scroll-snap-type:x mandatory;padding-bottom:12px">
              <div style="flex:0 0 340px;aspect-ratio:4/3;border-radius:18px;scroll-snap-align:start;background:linear-gradient(135deg,var(--ab-primary),var(--ab-accent))"></div>
              <div style="flex:0 0 340px;aspect-ratio:4/3;border-radius:18px;scroll-snap-align:start;background:linear-gradient(135deg,var(--ab-secondary),var(--ab-primary))"></div>
              <div style="flex:0 0 340px;aspect-ratio:4/3;border-radius:18px;scroll-snap-align:start;background:linear-gradient(135deg,var(--ab-accent),var(--ab-secondary))"></div>
            </div>
          </div>
        </section>
        HTML;
    }

    private function newsletter(): string
    {
        return <<<'HTML'
        <section class="ab-section ab-section--muted" data-gjs-name="Newsletter">
          <div class="ab-container" style="text-align:center;max-width:620px">
            <h2 class="ab-section__title">Stay in the loop</h2>
            <p class="ab-section__sub">Product updates and occasional insights. No spam, unsubscribe anytime.</p>
            <form style="display:flex;gap:10px;margin-top:24px;flex-wrap:wrap;justify-content:center" data-form="newsletter">
              <input type="email" name="email" placeholder="you@company.com" required style="flex:1;min-width:240px;padding:13px 16px;border-radius:12px;border:1px solid rgba(15,23,42,.14);font:inherit">
              <button class="ab-btn ab-btn--primary" type="submit">Subscribe</button>
            </form>
          </div>
        </section>
        HTML;
    }

    private function simpleForm(): string
    {
        return <<<'HTML'
        <section class="ab-section" data-gjs-name="Simple Form">
          <div class="ab-container ab-container--narrow">
            <form class="ab-form" data-form="contact">
              <label>Name<input type="text" name="name" required></label>
              <label>Email<input type="email" name="email" required></label>
              <label>Message<textarea name="message" rows="4" required></textarea></label>
              <button class="ab-btn ab-btn--primary ab-btn--block" type="submit">Send</button>
            </form>
          </div>
        </section>
        HTML;
    }

    private function blogGrid(): string
    {
        return <<<'HTML'
        <section class="ab-section" data-gjs-name="Blog Grid">
          <div class="ab-container">
            <div class="ab-section__head"><span class="ab-eyebrow">Journal</span><h2 class="ab-section__title">Latest writing</h2></div>
            <div class="ab-grid ab-grid--3">
              <article class="ab-feature"><div style="aspect-ratio:16/10;border-radius:12px;margin-bottom:16px;background:linear-gradient(135deg,var(--ab-primary),var(--ab-accent))"></div><h3 class="ab-feature__title">Designing for clarity</h3><p class="ab-feature__body">How restraint makes interfaces easier to use.</p></article>
              <article class="ab-feature"><div style="aspect-ratio:16/10;border-radius:12px;margin-bottom:16px;background:linear-gradient(135deg,var(--ab-secondary),var(--ab-primary))"></div><h3 class="ab-feature__title">Performance as a feature</h3><p class="ab-feature__body">Why speed is the first thing visitors notice.</p></article>
              <article class="ab-feature"><div style="aspect-ratio:16/10;border-radius:12px;margin-bottom:16px;background:linear-gradient(135deg,var(--ab-accent),var(--ab-secondary))"></div><h3 class="ab-feature__title">Writing that converts</h3><p class="ab-feature__body">A practical framework for landing page copy.</p></article>
            </div>
          </div>
        </section>
        HTML;
    }

    private function popup(): string
    {
        return <<<'HTML'
        <div data-gjs-name="Popup" data-popup="true" style="position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);z-index:9998">
          <div style="background:var(--ab-surface);max-width:440px;width:92vw;padding:32px;border-radius:20px;box-shadow:0 30px 80px -30px rgba(0,0,0,.5);text-align:center">
            <h3 style="margin:0 0 10px;font-size:22px">Get 20% off your first month</h3>
            <p style="margin:0 0 22px;color:var(--ab-muted)">Join the newsletter and we will send your code instantly.</p>
            <form style="display:grid;gap:10px">
              <input type="email" placeholder="you@company.com" style="padding:12px 14px;border-radius:11px;border:1px solid rgba(15,23,42,.14);font:inherit">
              <button class="ab-btn ab-btn--primary ab-btn--block" type="submit">Claim discount</button>
            </form>
          </div>
        </div>
        HTML;
    }
}
