@extends('layouts.app')

@section('title', config('redcherry.store_name').' — Physical Books & eBooks, Delivered Across India')

@section('content')

    {{-- Hero --}}
    @if($banners->isNotEmpty())
        <h1 class="visually-hidden">{{ config('redcherry.store_name') }} — print books and eBooks, delivered across India</h1>
        <section class="hero" data-hero aria-roledescription="carousel" aria-label="Featured">
            @foreach($banners as $banner)
                <div class="hero-slide @if($loop->first) is-active @endif" data-hero-slide aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $banners->count() }}" @unless($loop->first) aria-hidden="true" @endunless>
                    <img class="hero-img" src="{{ $banner->url() }}" alt="" @unless($loop->first) loading="lazy" @endunless>
                    <div class="hero-copy container">
                        <span class="hero-index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($banners->count(), 2, '0', STR_PAD_LEFT) }}</span>
                        @if($banner->title)<h2 class="hero-title">{{ $banner->title }}</h2>@endif
                        @if($banner->subtitle)<p class="hero-sub">{{ $banner->subtitle }}</p>@endif
                        @if($banner->link_url)
                            <a href="{{ $banner->link_url }}" class="hero-cta" @unless($loop->first) tabindex="-1" @endunless>{{ $banner->cta_label ?: 'Shop now' }} <i class="bi bi-arrow-right"></i></a>
                        @endif
                    </div>
                </div>
            @endforeach
            @if($banners->count() > 1)
                <div class="hero-controls container">
                    <div class="hero-progress">
                        @foreach($banners as $banner)
                            <button type="button" class="hero-tick" data-hero-go="{{ $loop->index }}" aria-label="Go to slide {{ $loop->iteration }}"><span></span></button>
                        @endforeach
                    </div>
                    <div class="hero-arrows">
                        <button type="button" data-hero-prev aria-label="Previous slide"><i class="bi bi-arrow-left"></i></button>
                        <button type="button" data-hero-next aria-label="Next slide"><i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>
            @endif
        </section>
    @else
        <div class="bg-brand-soft py-5 mb-4">
            <div class="container text-center py-4">
                <div class="section-eyebrow">The Publishing Company Since 2014</div>
                <h1 class="section-title display-5 mt-3">Books worth shelving — in print or on your screen</h1>
                <p class="text-muted col-lg-6 mx-auto mt-3">Browse our curated catalog of paperbacks, hardcovers and instant-download eBooks from RedCherry Books.</p>
                <a href="{{ route('catalog.top100') }}" class="btn btn-brand btn-lg mt-2">Explore Top 100</a>
            </div>
        </div>
    @endif

    {{-- New Releases --}}
    <section class="home-section">
        <div class="container">
            <div class="section-head" data-reveal>
                <div>
                    <span class="section-eyebrow">Just arrived</span>
                    <h2 class="section-title">New on the shelf</h2>
                </div>
                <a href="{{ route('catalog.new-releases') }}" class="link-arrow">All new releases <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="book-grid" data-stagger>
                @foreach($newReleases as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Categories --}}
    @if($categories->isNotEmpty())
    <section class="home-section">
        <div class="container">
            <div class="section-head" data-reveal>
                <div>
                    <span class="section-eyebrow">Browse by genre</span>
                    <h2 class="section-title">Pick a shelf</h2>
                </div>
            </div>
            <div class="category-grid" data-stagger>
                @foreach($categories as $category)
                    <a href="{{ route('catalog.category', $category) }}" class="category-tile">
                        <span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="name">{{ $category->name }}</span>
                        <span class="count">{{ $category->books_count }} {{ \Illuminate\Support\Str::plural('title', $category->books_count) }} <i class="bi bi-arrow-right"></i></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Best Sellers --}}
    @if($bestsellers->isNotEmpty())
    <section class="home-section">
        <div class="container">
            <div class="section-head" data-reveal>
                <div>
                    <span class="section-eyebrow">Most loved</span>
                    <h2 class="section-title">What readers are buying</h2>
                </div>
                <a href="{{ route('catalog.bestsellers') }}" class="link-arrow">All best sellers <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="book-grid" data-stagger>
                @foreach($bestsellers as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Feature band --}}
    <section class="home-section">
        <div class="container">
            <div class="feature-band" data-reveal>
                <div class="media">
                    <img src="{{ asset('storage/banners/hero-gift.jpg') }}" alt="An open book beside a cup of tea" loading="lazy" data-parallax>
                </div>
                <div class="copy">
                    <span class="section-eyebrow">Gift cards</span>
                    <h2>Not sure what they’re reading next?</h2>
                    <p>Send a RedCherry gift card by email. They pick the book, it lands in their wallet, and it never expires on a shelf.</p>
                    <a href="{{ route('gift-cards.index') }}" class="btn btn-light">Choose a gift card</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Top 100 --}}
    @if($top100->isNotEmpty())
    <section class="home-section">
        <div class="container">
            <div class="section-head" data-reveal>
                <div>
                    <span class="section-eyebrow">Curated list</span>
                    <h2 class="section-title">The RedCherry Top 100</h2>
                </div>
                <a href="{{ route('catalog.top100') }}" class="link-arrow">See the full list <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="book-grid" data-stagger>
                @foreach($top100 as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Authors --}}
    @if($authors->isNotEmpty())
    <section class="home-section">
        <div class="container">
            <div class="section-head" data-reveal>
                <div>
                    <span class="section-eyebrow">Browse by author</span>
                    <h2 class="section-title">Writers on our list</h2>
                </div>
            </div>
            <div class="author-cloud" data-stagger>
                @foreach($authors as $author)
                    <a href="{{ route('catalog.author', $author) }}" class="author-chip">{{ $author->name }}<small>{{ $author->books_count }}</small></a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="stats-strip">
        <div class="container stats-grid" data-stagger>
            <div><div class="stat-num" data-count="50" data-suffix="K+">50K+</div><div class="stat-label">Titles in print and digital</div></div>
            <div><div class="stat-num" data-count="2" data-suffix="M+">2M+</div><div class="stat-label">Readers served since 2014</div></div>
            <div><div class="stat-num" data-count="1200" data-suffix="+">1200+</div><div class="stat-label">Authors on our list</div></div>
            <div><div class="stat-num">4.8<span class="text-brand">★</span></div><div class="stat-label">Average review rating</div></div>
        </div>
    </section>

@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script>
(function () {
    var hero = document.querySelector('[data-hero]');
    if (!hero || typeof gsap === 'undefined') return;
    var slides = gsap.utils.toArray('[data-hero-slide]', hero);
    var ticks = gsap.utils.toArray('.hero-tick span', hero);
    if (slides.length < 2) return;

    var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    var DURATION = 6.5;
    var wrap = gsap.utils.wrap(0, slides.length);
    var current = 0, busy = false, progress;

    function copyOf(s) { return s.querySelectorAll('.hero-index, .hero-title, .hero-sub, .hero-cta'); }

    function setActive(i) {
        slides.forEach(function (s, n) {
            var on = n === i;
            s.classList.toggle('is-active', on);
            s.setAttribute('aria-hidden', on ? 'false' : 'true');
            s.querySelectorAll('a').forEach(function (a) { a.tabIndex = on ? 0 : -1; });
        });
        ticks.forEach(function (t, n) { t.style.setProperty('--p', n < i ? 1 : 0); });
    }

    function runProgress() {
        if (progress) progress.kill();
        if (reduce) return;
        var tick = ticks[current];
        progress = gsap.fromTo(tick, { '--p': 0 }, { '--p': 1, duration: DURATION, ease: 'none', onComplete: function () { go(current + 1); } });
    }

    function go(target) {
        var next = wrap(target);
        if (busy || next === current) return;
        busy = true;
        var from = slides[current], to = slides[next];
        current = next;
        setActive(next);
        gsap.set(to, { zIndex: 2, autoAlpha: 1 });
        gsap.set(from, { zIndex: 1 });
        var tl = gsap.timeline({ onComplete: function () { gsap.set(from, { autoAlpha: 0, zIndex: 0 }); busy = false; runProgress(); } });
        if (reduce) { tl.set(from, { autoAlpha: 0 }); return; }
        // Slow Ken Burns zoom runs on its own so it doesn't hold `busy` (and the controls) for the whole slide.
        gsap.fromTo(to.querySelector('.hero-img'), { scale: 1.18 }, { scale: 1.04, duration: DURATION + 1.2, ease: 'none', overwrite: true });
        tl.fromTo(to, { clipPath: 'inset(0 0 0 100%)' }, { clipPath: 'inset(0 0 0 0%)', duration: 1.1, ease: 'power3.inOut' })
          .fromTo(copyOf(to), { y: 34, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .8, stagger: .08, ease: 'power3.out' }, .55)
          .to(from.querySelector('.hero-img'), { scale: 1.12, duration: 1.1, ease: 'power3.inOut', overwrite: true }, 0);
    }

    // first slide entrance
    if (!reduce) {
        gsap.fromTo(slides[0].querySelector('.hero-img'), { scale: 1.12 }, { scale: 1.04, duration: DURATION + 1, ease: 'none' });
        gsap.from(copyOf(slides[0]), { y: 34, autoAlpha: 0, duration: .9, stagger: .09, ease: 'power3.out', delay: .15 });
    }
    setActive(0);
    runProgress();

    hero.querySelector('[data-hero-next]')?.addEventListener('click', function () { go(current + 1); });
    hero.querySelector('[data-hero-prev]')?.addEventListener('click', function () { go(current - 1); });
    hero.querySelectorAll('[data-hero-go]').forEach(function (b) { b.addEventListener('click', function () { go(+b.dataset.heroGo); }); });

    // pause while hovered or focused, so readers aren't rushed
    function pause() { progress && progress.pause(); }
    function resume() { progress && progress.resume(); }
    hero.addEventListener('mouseenter', pause); hero.addEventListener('mouseleave', resume);
    hero.addEventListener('focusin', pause); hero.addEventListener('focusout', resume);
    hero.addEventListener('keydown', function (e) { if (e.key === 'ArrowRight') go(current + 1); if (e.key === 'ArrowLeft') go(current - 1); });

    var x0 = null;
    hero.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    hero.addEventListener('touchend', function (e) {
        if (x0 === null) return;
        var dx = e.changedTouches[0].clientX - x0; x0 = null;
        if (Math.abs(dx) > 50) go(current + (dx < 0 ? 1 : -1));
    });
})();
</script>
<script>
(function () {
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    gsap.registerPlugin(ScrollTrigger);

    gsap.utils.toArray('[data-reveal]').forEach(function (el) {
        gsap.from(el, { y: 28, autoAlpha: 0, duration: .9, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 88%', once: true } });
    });

    gsap.utils.toArray('[data-stagger]').forEach(function (group) {
        gsap.from(group.children, {
            y: 32, autoAlpha: 0, duration: .7, ease: 'power3.out',
            stagger: { each: .07, grid: 'auto', from: 'start' },
            scrollTrigger: { trigger: group, start: 'top 85%', once: true }
        });
    });

    gsap.utils.toArray('[data-parallax]').forEach(function (img) {
        gsap.fromTo(img, { yPercent: -6 }, { yPercent: 6, ease: 'none', scrollTrigger: { trigger: img.parentElement, start: 'top bottom', end: 'bottom top', scrub: true } });
    });

    // Count the stats up once they scroll into view; snap keeps whole numbers.
    gsap.utils.toArray('[data-count]').forEach(function (el) {
        var target = +el.dataset.count, suffix = el.dataset.suffix || '', snap = gsap.utils.snap(1);
        var counter = { v: 0 };
        gsap.to(counter, {
            v: target, duration: 1.6, ease: 'power2.out',
            scrollTrigger: { trigger: el, start: 'top 90%', once: true },
            onUpdate: function () { el.textContent = snap(counter.v) + suffix; }
        });
    });
})();
</script>
@endpush
