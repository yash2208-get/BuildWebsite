<?php

declare(strict_types=1);

namespace App\Services\Ai;

/**
 * Renders generated content into self-contained, production-ready HTML.
 *
 * Output deliberately uses plain semantic HTML plus a scoped stylesheet so the
 * exported site has zero runtime dependencies and stays editable inside
 * GrapesJS (every block is annotated with data-gjs metadata).
 */
final class SectionRenderer
{
    public function __construct(private readonly array $palette) {}

    private function e(?string $v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }

    /* ------------------------------------------------------------------ */

    public function navbar(string $brand, array $links): string
    {
        $items = implode('', array_map(
            fn ($l) => '<a class="ab-nav__link" href="#'.$this->e(strtolower($l)).'">'.$this->e($l).'</a>',
            $links
        ));

        return <<<HTML
        <header class="ab-nav" data-gjs-name="Navbar" data-gjs-type="navbar">
          <div class="ab-container ab-nav__inner">
            <a class="ab-nav__brand" href="#home">
              <span class="ab-nav__mark"></span>{$this->e($brand)}
            </a>
            <nav class="ab-nav__links">{$items}</nav>
            <div class="ab-nav__actions">
              <a class="ab-btn ab-btn--ghost" href="#contact">Sign in</a>
              <a class="ab-btn ab-btn--primary" href="#contact">Get started</a>
            </div>
          </div>
        </header>
        HTML;
    }

    public function hero(string $headline, string $sub, string $imageHint): string
    {
        return <<<HTML
        <section class="ab-hero" id="home" data-gjs-name="Hero">
          <div class="ab-container ab-hero__inner">
            <div class="ab-hero__copy">
              <span class="ab-pill">New — AI powered</span>
              <h1 class="ab-hero__title">{$this->e($headline)}</h1>
              <p class="ab-hero__sub">{$this->e($sub)}</p>
              <div class="ab-hero__cta">
                <a class="ab-btn ab-btn--primary ab-btn--lg" href="#pricing">Start free</a>
                <a class="ab-btn ab-btn--ghost ab-btn--lg" href="#features">See how it works</a>
              </div>
              <p class="ab-hero__note">No credit card required · Cancel anytime</p>
            </div>
            <div class="ab-hero__media" data-image-hint="{$this->e($imageHint)}">
              <div class="ab-hero__glow"></div>
              <div class="ab-hero__card">
                <div class="ab-hero__bar"><i></i><i></i><i></i></div>
                <div class="ab-hero__skeleton">
                  <span style="width:72%"></span><span style="width:54%"></span>
                  <span style="width:88%"></span><span style="width:40%"></span>
                </div>
              </div>
            </div>
          </div>
        </section>
        HTML;
    }

    public function logos(): string
    {
        $names = ['Northwind', 'Lumen', 'Arcadia', 'Fieldhouse', 'Studio Kite', 'Vertex'];
        $items = implode('', array_map(fn ($n) => '<span class="ab-logos__item">'.$this->e($n).'</span>', $names));

        return <<<HTML
        <section class="ab-logos" data-gjs-name="Logo Cloud">
          <div class="ab-container">
            <p class="ab-logos__label">Trusted by fast-moving teams worldwide</p>
            <div class="ab-logos__row">{$items}</div>
          </div>
        </section>
        HTML;
    }

    public function features(array $features): string
    {
        $cards = '';

        foreach ($features as $f) {
            $cards .= <<<HTML
            <article class="ab-feature">
              <span class="ab-feature__icon" data-icon="{$this->e($f['icon'] ?? 'star')}"></span>
              <h3 class="ab-feature__title">{$this->e($f['title'])}</h3>
              <p class="ab-feature__body">{$this->e($f['body'])}</p>
            </article>
            HTML;
        }

        return <<<HTML
        <section class="ab-section" id="features" data-gjs-name="Features">
          <div class="ab-container">
            <div class="ab-section__head">
              <span class="ab-eyebrow">Features</span>
              <h2 class="ab-section__title">Everything you need, nothing you don't</h2>
              <p class="ab-section__sub">A complete toolkit designed to help you launch faster and grow further.</p>
            </div>
            <div class="ab-grid ab-grid--3">{$cards}</div>
          </div>
        </section>
        HTML;
    }

    public function stats(array $stats): string
    {
        $items = '';

        foreach ($stats as $s) {
            $items .= '<div class="ab-stat"><strong>'.$this->e($s['value']).'</strong><span>'.$this->e($s['label']).'</span></div>';
        }

        return <<<HTML
        <section class="ab-stats" data-gjs-name="Stats">
          <div class="ab-container ab-stats__row">{$items}</div>
        </section>
        HTML;
    }

    public function about(string $title, string $body, string $imageHint): string
    {
        return <<<HTML
        <section class="ab-section ab-about" id="about" data-gjs-name="About">
          <div class="ab-container ab-about__inner">
            <div class="ab-about__media" data-image-hint="{$this->e($imageHint)}"><div class="ab-about__frame"></div></div>
            <div class="ab-about__copy">
              <span class="ab-eyebrow">About us</span>
              <h2 class="ab-section__title">{$this->e($title)}</h2>
              <p class="ab-section__sub">{$this->e($body)}</p>
              <ul class="ab-checklist">
                <li>Independent and customer funded</li>
                <li>Trusted with millions of page views</li>
                <li>Built by a distributed team of specialists</li>
              </ul>
            </div>
          </div>
        </section>
        HTML;
    }

    public function testimonials(array $items): string
    {
        $cards = '';

        foreach ($items as $t) {
            $initials = mb_strtoupper(mb_substr($t['name'], 0, 1));
            $cards .= <<<HTML
            <figure class="ab-quote">
              <blockquote>“{$this->e($t['quote'])}”</blockquote>
              <figcaption>
                <span class="ab-quote__avatar">{$this->e($initials)}</span>
                <span><strong>{$this->e($t['name'])}</strong><em>{$this->e($t['role'])}, {$this->e($t['company'])}</em></span>
              </figcaption>
            </figure>
            HTML;
        }

        return <<<HTML
        <section class="ab-section ab-section--muted" id="testimonials" data-gjs-name="Testimonials">
          <div class="ab-container">
            <div class="ab-section__head">
              <span class="ab-eyebrow">Testimonials</span>
              <h2 class="ab-section__title">Loved by teams who ship</h2>
            </div>
            <div class="ab-grid ab-grid--3">{$cards}</div>
          </div>
        </section>
        HTML;
    }

    public function pricing(array $tiers): string
    {
        $cards = '';

        foreach ($tiers as $t) {
            $featured = ! empty($t['featured']) ? ' ab-price--featured' : '';
            $badge = ! empty($t['featured']) ? '<span class="ab-price__badge">Most popular</span>' : '';
            $list = implode('', array_map(fn ($f) => '<li>'.$this->e($f).'</li>', $t['features']));

            $cards .= <<<HTML
            <article class="ab-price{$featured}">
              {$badge}
              <h3 class="ab-price__name">{$this->e($t['name'])}</h3>
              <p class="ab-price__amount"><span>$</span>{$this->e($t['price'])}<em>/{$this->e($t['period'])}</em></p>
              <ul class="ab-price__list">{$list}</ul>
              <a class="ab-btn ab-btn--primary ab-btn--block" href="#contact">Choose {$this->e($t['name'])}</a>
            </article>
            HTML;
        }

        return <<<HTML
        <section class="ab-section" id="pricing" data-gjs-name="Pricing">
          <div class="ab-container">
            <div class="ab-section__head">
              <span class="ab-eyebrow">Pricing</span>
              <h2 class="ab-section__title">Simple, transparent pricing</h2>
              <p class="ab-section__sub">Start free and upgrade when you are ready. No hidden fees, ever.</p>
            </div>
            <div class="ab-grid ab-grid--3 ab-grid--pricing">{$cards}</div>
          </div>
        </section>
        HTML;
    }

    public function team(array $members): string
    {
        $cards = '';

        foreach ($members as $m) {
            $parts = explode(' ', $m['name']);
            $initials = mb_substr($parts[0], 0, 1).mb_substr($parts[1] ?? '', 0, 1);
            $cards .= <<<HTML
            <article class="ab-member">
              <span class="ab-member__avatar">{$this->e(mb_strtoupper($initials))}</span>
              <h3>{$this->e($m['name'])}</h3>
              <p class="ab-member__role">{$this->e($m['role'])}</p>
              <p class="ab-member__bio">{$this->e($m['bio'])}</p>
            </article>
            HTML;
        }

        return <<<HTML
        <section class="ab-section" id="team" data-gjs-name="Team">
          <div class="ab-container">
            <div class="ab-section__head">
              <span class="ab-eyebrow">Team</span>
              <h2 class="ab-section__title">The people behind the work</h2>
            </div>
            <div class="ab-grid ab-grid--4">{$cards}</div>
          </div>
        </section>
        HTML;
    }

    public function gallery(array $hints): string
    {
        $tiles = '';

        foreach ($hints as $i => $h) {
            $tiles .= '<div class="ab-gallery__tile ab-gallery__tile--'.($i % 4).'" data-image-hint="'.$this->e($h).'"><span>'.$this->e($h).'</span></div>';
        }

        return <<<HTML
        <section class="ab-section ab-section--muted" id="gallery" data-gjs-name="Gallery">
          <div class="ab-container">
            <div class="ab-section__head">
              <span class="ab-eyebrow">Gallery</span>
              <h2 class="ab-section__title">A closer look</h2>
            </div>
            <div class="ab-gallery">{$tiles}</div>
          </div>
        </section>
        HTML;
    }

    public function faq(array $faqs): string
    {
        $items = '';

        foreach ($faqs as $f) {
            $items .= <<<HTML
            <details class="ab-faq__item">
              <summary>{$this->e($f['question'])}</summary>
              <p>{$this->e($f['answer'])}</p>
            </details>
            HTML;
        }

        return <<<HTML
        <section class="ab-section" id="faq" data-gjs-name="FAQ">
          <div class="ab-container ab-container--narrow">
            <div class="ab-section__head">
              <span class="ab-eyebrow">FAQ</span>
              <h2 class="ab-section__title">Questions, answered</h2>
            </div>
            <div class="ab-faq">{$items}</div>
          </div>
        </section>
        HTML;
    }

    public function cta(string $heading): string
    {
        return <<<HTML
        <section class="ab-cta" data-gjs-name="Call To Action">
          <div class="ab-container ab-cta__inner">
            <h2>{$this->e($heading)}</h2>
            <p>Join thousands of teams building beautiful, fast websites without writing code.</p>
            <div class="ab-cta__actions">
              <a class="ab-btn ab-btn--light ab-btn--lg" href="#contact">Get started free</a>
              <a class="ab-btn ab-btn--outline ab-btn--lg" href="#contact">Talk to sales</a>
            </div>
          </div>
        </section>
        HTML;
    }

    public function contact(string $brand): string
    {
        return <<<HTML
        <section class="ab-section" id="contact" data-gjs-name="Contact">
          <div class="ab-container ab-contact">
            <div class="ab-contact__copy">
              <span class="ab-eyebrow">Contact</span>
              <h2 class="ab-section__title">Let's talk</h2>
              <p class="ab-section__sub">Tell us about your project and we will get back to you within one business day.</p>
              <ul class="ab-contact__meta">
                <li><strong>Email</strong><span>hello@{$this->e(strtolower(preg_replace('/[^a-z0-9]/i', '', $brand) ?: 'studio'))}.com</span></li>
                <li><strong>Phone</strong><span>+1 (555) 014 2200</span></li>
                <li><strong>Studio</strong><span>128 Bridge Street, Suite 400</span></li>
              </ul>
            </div>
            <form class="ab-form" data-gjs-name="Contact Form" method="post" action="#">
              <label>Full name<input type="text" name="name" placeholder="Jane Cooper" required></label>
              <label>Email<input type="email" name="email" placeholder="jane@company.com" required></label>
              <label>How can we help?<textarea name="message" rows="4" placeholder="Tell us a little about your project…" required></textarea></label>
              <button class="ab-btn ab-btn--primary ab-btn--block" type="submit">Send message</button>
            </form>
          </div>
        </section>
        HTML;
    }

    public function footer(string $brand, array $links): string
    {
        $nav = implode('', array_map(
            fn ($l) => '<a href="#'.$this->e(strtolower($l)).'">'.$this->e($l).'</a>',
            $links
        ));
        $year = date('Y');

        return <<<HTML
        <footer class="ab-footer" data-gjs-name="Footer">
          <div class="ab-container ab-footer__inner">
            <div class="ab-footer__brand">
              <span class="ab-nav__mark"></span>
              <strong>{$this->e($brand)}</strong>
              <p>Beautiful websites, built in minutes.</p>
            </div>
            <nav class="ab-footer__nav">{$nav}</nav>
          </div>
          <div class="ab-container ab-footer__bottom">
            <span>© {$year} {$this->e($brand)}. All rights reserved.</span>
            <span class="ab-footer__legal"><a href="#">Privacy</a><a href="#">Terms</a></span>
          </div>
        </footer>
        HTML;
    }

    /* ------------------------------------------------------------------ */

    /** Scoped stylesheet driven by the generated palette. */
    public function stylesheet(): string
    {
        $p = $this->palette;
        $primary = $p['primary'] ?? '#6366f1';
        $secondary = $p['secondary'] ?? '#8b5cf6';
        $accent = $p['accent'] ?? '#06b6d4';
        $ink = $p['text'] ?? '#0f172a';
        $muted = $p['muted'] ?? '#64748b';
        $surface = $p['surface'] ?? '#ffffff';
        $bg = $p['background'] ?? '#f8fafc';

        return <<<CSS
        :root{
          --ab-primary:{$primary};--ab-secondary:{$secondary};--ab-accent:{$accent};
          --ab-ink:{$ink};--ab-muted:{$muted};--ab-surface:{$surface};--ab-bg:{$bg};
          --ab-radius:18px;--ab-shadow:0 24px 60px -28px rgba(15,23,42,.35);
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:'Inter',system-ui,-apple-system,'Segoe UI',sans-serif;color:var(--ab-ink);background:var(--ab-bg);line-height:1.6;-webkit-font-smoothing:antialiased}
        img{max-width:100%;display:block}
        a{color:inherit;text-decoration:none}
        .ab-container{width:min(1160px,92vw);margin-inline:auto}
        .ab-container--narrow{width:min(780px,92vw)}
        .ab-section{padding:96px 0}
        .ab-section--muted{background:color-mix(in srgb,var(--ab-primary) 4%,var(--ab-bg))}
        .ab-section__head{text-align:center;max-width:640px;margin:0 auto 56px}
        .ab-section__title{font-size:clamp(28px,4vw,42px);line-height:1.15;letter-spacing:-.02em;margin:12px 0 14px;font-weight:700}
        .ab-section__sub{color:var(--ab-muted);font-size:17px;margin:0}
        .ab-eyebrow{display:inline-block;font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--ab-primary)}
        .ab-grid{display:grid;gap:24px}
        .ab-grid--3{grid-template-columns:repeat(3,1fr)}
        .ab-grid--4{grid-template-columns:repeat(4,1fr)}
        .ab-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 20px;border-radius:12px;font-weight:600;font-size:15px;border:1px solid transparent;cursor:pointer;transition:transform .18s ease,box-shadow .18s ease,background .18s ease}
        .ab-btn:hover{transform:translateY(-1px)}
        .ab-btn--lg{padding:14px 26px;font-size:16px}
        .ab-btn--block{width:100%}
        .ab-btn--primary{background:linear-gradient(135deg,var(--ab-primary),var(--ab-secondary));color:#fff;box-shadow:0 12px 30px -12px var(--ab-primary)}
        .ab-btn--ghost{background:transparent;border-color:color-mix(in srgb,var(--ab-ink) 14%,transparent);color:var(--ab-ink)}
        .ab-btn--light{background:#fff;color:var(--ab-primary)}
        .ab-btn--outline{border-color:rgba(255,255,255,.45);color:#fff}
        /* nav */
        .ab-nav{position:sticky;top:0;z-index:50;backdrop-filter:blur(14px);background:color-mix(in srgb,var(--ab-surface) 82%,transparent);border-bottom:1px solid color-mix(in srgb,var(--ab-ink) 8%,transparent)}
        .ab-nav__inner{display:flex;align-items:center;justify-content:space-between;height:72px;gap:24px}
        .ab-nav__brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:18px;letter-spacing:-.01em}
        .ab-nav__mark{width:26px;height:26px;border-radius:9px;background:linear-gradient(135deg,var(--ab-primary),var(--ab-accent));display:inline-block}
        .ab-nav__links{display:flex;gap:28px}
        .ab-nav__link{font-size:15px;color:var(--ab-muted);font-weight:500}
        .ab-nav__link:hover{color:var(--ab-ink)}
        .ab-nav__actions{display:flex;gap:10px;align-items:center}
        /* hero */
        .ab-hero{position:relative;padding:110px 0 96px;overflow:hidden;background:radial-gradient(1000px 460px at 78% -10%,color-mix(in srgb,var(--ab-secondary) 22%,transparent),transparent 60%),var(--ab-bg)}
        .ab-hero__inner{display:grid;grid-template-columns:1.05fr .95fr;gap:56px;align-items:center}
        .ab-pill{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:999px;font-size:13px;font-weight:600;color:var(--ab-primary);background:color-mix(in srgb,var(--ab-primary) 12%,transparent);margin-bottom:20px}
        .ab-hero__title{font-size:clamp(36px,5.4vw,60px);line-height:1.05;letter-spacing:-.035em;margin:0 0 18px;font-weight:800}
        .ab-hero__sub{font-size:19px;color:var(--ab-muted);margin:0 0 30px;max-width:34em}
        .ab-hero__cta{display:flex;gap:12px;flex-wrap:wrap}
        .ab-hero__note{margin-top:18px;font-size:13px;color:var(--ab-muted)}
        .ab-hero__media{position:relative}
        .ab-hero__glow{position:absolute;inset:-8% -12% 12% 6%;background:linear-gradient(135deg,var(--ab-primary),var(--ab-accent));filter:blur(64px);opacity:.28;border-radius:50%}
        .ab-hero__card{position:relative;background:var(--ab-surface);border-radius:20px;box-shadow:var(--ab-shadow);padding:18px;border:1px solid color-mix(in srgb,var(--ab-ink) 8%,transparent)}
        .ab-hero__bar{display:flex;gap:7px;margin-bottom:16px}
        .ab-hero__bar i{width:11px;height:11px;border-radius:50%;background:color-mix(in srgb,var(--ab-ink) 14%,transparent)}
        .ab-hero__skeleton{display:grid;gap:12px}
        .ab-hero__skeleton span{height:13px;border-radius:7px;background:linear-gradient(90deg,color-mix(in srgb,var(--ab-primary) 22%,transparent),color-mix(in srgb,var(--ab-accent) 16%,transparent));display:block}
        /* logos */
        .ab-logos{padding:44px 0;border-block:1px solid color-mix(in srgb,var(--ab-ink) 7%,transparent)}
        .ab-logos__label{text-align:center;color:var(--ab-muted);font-size:13px;letter-spacing:.08em;text-transform:uppercase;margin:0 0 20px}
        .ab-logos__row{display:flex;justify-content:space-between;flex-wrap:wrap;gap:22px}
        .ab-logos__item{font-weight:700;font-size:18px;color:color-mix(in srgb,var(--ab-ink) 42%,transparent);letter-spacing:-.01em}
        /* feature */
        .ab-feature{background:var(--ab-surface);border-radius:var(--ab-radius);padding:28px;border:1px solid color-mix(in srgb,var(--ab-ink) 8%,transparent);transition:transform .2s ease,box-shadow .2s ease}
        .ab-feature:hover{transform:translateY(-4px);box-shadow:var(--ab-shadow)}
        .ab-feature__icon{width:44px;height:44px;border-radius:13px;display:block;margin-bottom:16px;background:linear-gradient(135deg,color-mix(in srgb,var(--ab-primary) 90%,#fff),var(--ab-accent))}
        .ab-feature__title{margin:0 0 8px;font-size:18px;font-weight:700}
        .ab-feature__body{margin:0;color:var(--ab-muted);font-size:15px}
        /* stats */
        .ab-stats{padding:56px 0;background:linear-gradient(135deg,var(--ab-primary),var(--ab-secondary));color:#fff}
        .ab-stats__row{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;text-align:center}
        .ab-stat strong{display:block;font-size:36px;font-weight:800;letter-spacing:-.03em}
        .ab-stat span{opacity:.82;font-size:14px}
        /* about */
        .ab-about__inner{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center}
        .ab-about__frame{aspect-ratio:4/3;border-radius:var(--ab-radius);background:linear-gradient(135deg,color-mix(in srgb,var(--ab-primary) 24%,transparent),color-mix(in srgb,var(--ab-accent) 24%,transparent));box-shadow:var(--ab-shadow)}
        .ab-checklist{list-style:none;padding:0;margin:22px 0 0;display:grid;gap:11px}
        .ab-checklist li{position:relative;padding-left:28px;color:var(--ab-muted)}
        .ab-checklist li::before{content:"";position:absolute;left:0;top:7px;width:16px;height:16px;border-radius:50%;background:var(--ab-primary);opacity:.85}
        /* quotes */
        .ab-quote{margin:0;background:var(--ab-surface);border-radius:var(--ab-radius);padding:28px;border:1px solid color-mix(in srgb,var(--ab-ink) 8%,transparent)}
        .ab-quote blockquote{margin:0 0 20px;font-size:16px;line-height:1.7}
        .ab-quote figcaption{display:flex;align-items:center;gap:12px}
        .ab-quote__avatar{width:40px;height:40px;border-radius:50%;display:grid;place-items:center;font-weight:700;color:#fff;background:linear-gradient(135deg,var(--ab-primary),var(--ab-accent))}
        .ab-quote figcaption strong{display:block;font-size:14px}
        .ab-quote figcaption em{font-style:normal;font-size:13px;color:var(--ab-muted)}
        /* pricing */
        .ab-price{position:relative;background:var(--ab-surface);border-radius:var(--ab-radius);padding:32px 28px;border:1px solid color-mix(in srgb,var(--ab-ink) 9%,transparent);display:flex;flex-direction:column}
        .ab-price--featured{border-color:var(--ab-primary);box-shadow:var(--ab-shadow);transform:translateY(-8px)}
        .ab-price__badge{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--ab-primary),var(--ab-secondary));color:#fff;font-size:12px;font-weight:700;padding:5px 14px;border-radius:999px}
        .ab-price__name{margin:0 0 10px;font-size:17px;font-weight:700}
        .ab-price__amount{font-size:44px;font-weight:800;letter-spacing:-.03em;margin:0 0 22px}
        .ab-price__amount span{font-size:22px;vertical-align:super;margin-right:2px}
        .ab-price__amount em{font-style:normal;font-size:14px;font-weight:500;color:var(--ab-muted);margin-left:4px}
        .ab-price__list{list-style:none;padding:0;margin:0 0 26px;display:grid;gap:11px;flex:1}
        .ab-price__list li{position:relative;padding-left:26px;color:var(--ab-muted);font-size:15px}
        .ab-price__list li::before{content:"✓";position:absolute;left:0;color:var(--ab-primary);font-weight:700}
        /* team */
        .ab-member{background:var(--ab-surface);border-radius:var(--ab-radius);padding:26px;text-align:center;border:1px solid color-mix(in srgb,var(--ab-ink) 8%,transparent)}
        .ab-member__avatar{width:64px;height:64px;border-radius:50%;margin:0 auto 16px;display:grid;place-items:center;font-weight:700;font-size:20px;color:#fff;background:linear-gradient(135deg,var(--ab-primary),var(--ab-secondary))}
        .ab-member h3{margin:0 0 4px;font-size:16px}
        .ab-member__role{margin:0 0 10px;font-size:13px;color:var(--ab-primary);font-weight:600}
        .ab-member__bio{margin:0;font-size:14px;color:var(--ab-muted)}
        /* gallery */
        .ab-gallery{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
        .ab-gallery__tile{aspect-ratio:1;border-radius:var(--ab-radius);display:grid;place-items:end;padding:14px;font-size:12px;color:#fff;text-shadow:0 1px 6px rgba(0,0,0,.4);background:linear-gradient(135deg,var(--ab-primary),var(--ab-accent))}
        .ab-gallery__tile--1{background:linear-gradient(135deg,var(--ab-secondary),var(--ab-primary))}
        .ab-gallery__tile--2{background:linear-gradient(135deg,var(--ab-accent),var(--ab-secondary))}
        .ab-gallery__tile--3{background:linear-gradient(135deg,var(--ab-primary),var(--ab-secondary))}
        /* faq */
        .ab-faq{display:grid;gap:12px}
        .ab-faq__item{background:var(--ab-surface);border:1px solid color-mix(in srgb,var(--ab-ink) 9%,transparent);border-radius:14px;padding:18px 22px}
        .ab-faq__item summary{cursor:pointer;font-weight:600;list-style:none}
        .ab-faq__item summary::-webkit-details-marker{display:none}
        .ab-faq__item p{margin:12px 0 0;color:var(--ab-muted)}
        /* cta */
        .ab-cta{padding:88px 0;background:linear-gradient(135deg,var(--ab-primary),var(--ab-secondary));color:#fff;text-align:center}
        .ab-cta h2{font-size:clamp(28px,4vw,40px);margin:0 0 12px;letter-spacing:-.02em}
        .ab-cta p{opacity:.9;margin:0 0 28px}
        .ab-cta__actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
        /* contact */
        .ab-contact{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:start}
        .ab-contact__meta{list-style:none;padding:0;margin:26px 0 0;display:grid;gap:14px}
        .ab-contact__meta strong{display:block;font-size:12px;text-transform:uppercase;letter-spacing:.1em;color:var(--ab-muted)}
        .ab-form{background:var(--ab-surface);padding:28px;border-radius:var(--ab-radius);border:1px solid color-mix(in srgb,var(--ab-ink) 9%,transparent);display:grid;gap:16px;box-shadow:var(--ab-shadow)}
        .ab-form label{display:grid;gap:7px;font-size:14px;font-weight:600}
        .ab-form input,.ab-form textarea{font:inherit;padding:11px 14px;border-radius:11px;border:1px solid color-mix(in srgb,var(--ab-ink) 14%,transparent);background:var(--ab-bg);font-weight:400}
        .ab-form input:focus,.ab-form textarea:focus{outline:2px solid color-mix(in srgb,var(--ab-primary) 45%,transparent);outline-offset:1px}
        /* footer */
        .ab-footer{background:var(--ab-ink);color:#fff;padding:56px 0 26px}
        .ab-footer__inner{display:flex;justify-content:space-between;gap:32px;flex-wrap:wrap;padding-bottom:32px;border-bottom:1px solid rgba(255,255,255,.12)}
        .ab-footer__brand{display:grid;gap:8px;max-width:280px}
        .ab-footer__brand strong{font-size:17px}
        .ab-footer__brand p{margin:0;opacity:.62;font-size:14px}
        .ab-footer__nav{display:flex;gap:26px;flex-wrap:wrap;align-items:flex-start}
        .ab-footer__nav a{opacity:.72;font-size:14px}
        .ab-footer__nav a:hover{opacity:1}
        .ab-footer__bottom{display:flex;justify-content:space-between;padding-top:22px;font-size:13px;opacity:.6;flex-wrap:wrap;gap:12px}
        .ab-footer__legal{display:flex;gap:18px}
        @media(max-width:960px){
          .ab-hero__inner,.ab-about__inner,.ab-contact{grid-template-columns:1fr}
          .ab-grid--3,.ab-grid--4,.ab-stats__row,.ab-gallery{grid-template-columns:repeat(2,1fr)}
          .ab-nav__links{display:none}
          .ab-section{padding:68px 0}
        }
        @media(max-width:600px){
          .ab-grid--3,.ab-grid--4,.ab-gallery{grid-template-columns:1fr}
          .ab-stats__row{grid-template-columns:repeat(2,1fr)}
          .ab-price--featured{transform:none}
          .ab-nav__actions .ab-btn--ghost{display:none}
        }
        CSS;
    }
}
