<?php
$pageTitle = 'Home';
$pageStyles = ['css/pages/home.css'];
include 'includes/header.php';
?>
<section class="page-hero hero-dot-grid home-hero reveal-on-scroll" aria-label="Home hero">
    <div class="hero-orbs">
        <span class="hero-orb hero-orb--indigo" aria-hidden="true"></span>
        <span class="hero-orb hero-orb--emerald" aria-hidden="true"></span>
    </div>
    <div class="hero-line" aria-hidden="true"></div>
    <div class="container hero-content">
        <span class="hero-pill">
            <span class="hero-pill-dot" aria-hidden="true"></span>
            Guided by research, inspired by tradition
        </span>
        <h1>
            Discover the Degree<br>
            <span class="hero-highlight">Built for Your Z-Score.</span>
        </h1>
        <p class="page-hero-meta">
            300+ programs across 25+ Sri Lankan universities — matched instantly to your ambitions and A/L results.
        </p>
        <div class="hero-cta-group">
            <a class="btn btn-primary" href="finder.php">Find My Degree</a>
            <a class="btn btn-ghost" href="universities.php">Browse Universities</a>
        </div>
        <div class="hero-stats-grid">
            <article class="stat-card">
                <div class="stat-count" data-target-number="25" data-suffix="+">0</div>
                <p class="stat-label">Universities</p>
            </article>
            <article class="stat-card">
                <div class="stat-count" data-target-number="300" data-suffix="+">0</div>
                <p class="stat-label">Degree programs</p>
            </article>
            <article class="stat-card">
                <div class="stat-count" data-target-number="15000" data-suffix="+">0</div>
                <p class="stat-label">Students guided</p>
            </article>
        </div>
    </div>
    <button class="hero-scroll" data-scroll-target="#bentoGrid" aria-label="Scroll to bento grid">
        <span aria-hidden="true">⌄</span>
        <small>Scroll</small>
    </button>
</section>
<div class="page-transition" aria-hidden="true"></div>
<section class="section-shell bento-section" aria-label="Bento grid">
    <div class="container">
        <div class="bento-section-header">
            <p class="eyebrow">Everything you need</p>
            <h2>Your complete guide to university life in Sri Lanka</h2>
            <p class="section-subtitle">Asymmetric rhythm, premium polish — move through curated stories and z-score clarity without friction.</p>
        </div>
        <div class="bento-grid" id="bentoGrid">
            <article class="bento-card bento-large reveal-on-scroll">
                <div class="large-morph" aria-hidden="true"></div>
                <p class="eyebrow">Universities</p>
                <h3>Browse Universities</h3>
                <p>Navigate tiered campuses, heritage rituals, and future-ready programs with guided stories.</p>
                <div class="card-links">
                    <a class="btn btn-secondary" href="universities.php">Explore →</a>
                </div>
            </article>
            <article class="bento-card bento-medium bento-card--accent reveal-on-scroll">
                <p class="eyebrow">Z-Score Finder</p>
                <h3>Find your fit fast</h3>
                <p>Set your score, pick a stream, and we yield curated programs instantly.</p>
                <div class="card-links">
                    <a class="btn btn-primary" href="finder.php">Launch Finder</a>
                </div>
            </article>
            <article class="bento-card bento-medium bento-card--dark reveal-on-scroll">
                <p class="eyebrow">Gallery</p>
                <h3>Campus stories</h3>
                <div class="mini-gallery">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <p>Light-filled labs, serene lawns, and guided rituals captured in motion.</p>
                <div class="card-links">
                    <a class="btn btn-secondary" href="gallery.php">View gallery</a>
                </div>
            </article>
            <article class="bento-card bento-half reveal-on-scroll">
                <p class="eyebrow">How it works</p>
                <h3>Minimal steps, calm clarity</h3>
                <ul class="kpi-row">
                    <li class="kpi">
                        <strong>01</strong>
                        <p>We listen to your story</p>
                    </li>
                    <li class="kpi">
                        <strong>02</strong>
                        <p>Pair streams + rituals</p>
                    </li>
                    <li class="kpi">
                        <strong>03</strong>
                        <p>Launch with confidence</p>
                    </li>
                </ul>
                <div class="card-links" style="margin-top: 1.5rem;">
                    <a class="btn btn-secondary" href="about.php">Our approach →</a>
                </div>
            </article>
            <article class="bento-card bento-half bento-card--stats reveal-on-scroll">
                <p class="eyebrow">Sri Lanka stats</p>
                <h3>Local data, global craft</h3>
                <div class="kpi-row">
                    <div class="kpi">
                        <strong class="accent">92%</strong>
                        <p>of students feel guided</p>
                    </div>
                    <div class="kpi">
                        <strong class="accent">48</strong>
                        <p>exclusive workshops</p>
                    </div>
                </div>
                <div class="card-links" style="margin-top: 1.5rem; position: relative; z-index: 2;">
                    <a class="btn btn-secondary" href="universities.php">See all universities →</a>
                </div>
                <div class="map-decor" aria-hidden="true"></div>
            </article>
        </div>
    </div>
</section>
<section class="section-shell" aria-label="Feature highlights">
    <div class="container">
        <h2>Where guidance meets craftsmanship</h2>
        <div class="feature-grid">
            <article class="feature-card reveal-on-scroll">
                <div class="feature-icon" aria-hidden="true"><i class="fa-solid fa-waveform-path"></i></div>
                <h3>Guided narratives</h3>
                <p>Every story, ritual, and campus detail is narrated with precision so you feel confident from the first scroll.</p>
                <div style="margin-top: 1rem;"><a class="btn btn-ghost" href="about.php">Read our story →</a></div>
            </article>
            <article class="feature-card reveal-on-scroll">
                <div class="feature-icon" aria-hidden="true"><i class="fa-solid fa-atom"></i></div>
                <h3>Z-score resonance</h3>
                <p>Dedicated algorithms pair your score with culture-first outcomes — no guesswork, just calm certainty.</p>
                <div style="margin-top: 1rem;"><a class="btn btn-ghost" href="finder.php">Try finder →</a></div>
            </article>
            <article class="feature-card reveal-on-scroll">
                <div class="feature-icon" aria-hidden="true"><i class="fa-solid fa-globe"></i></div>
                <h3>Future-ready fits</h3>
                <p>We showcase Sri Lanka’s brightest programs with stories of labs, studios, and traditions shaping tomorrow.</p>
                <div style="margin-top: 1rem;"><a class="btn btn-ghost" href="universities.php">View universities →</a></div>
            </article>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
