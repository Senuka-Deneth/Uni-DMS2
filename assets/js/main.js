const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.primary-nav');
    const drawer = document.querySelector('.nav-drawer');
    const drawerToggle = document.querySelector('.nav-toggle');
    const drawerClose = document.querySelector('.nav-drawer__close');
    const searchInput = document.getElementById('searchInput');
    const filterChips = Array.from(document.querySelectorAll('.filter-chip'));
    const universityCards = Array.from(document.querySelectorAll('.university-card'));
    const streamTiles = Array.from(document.querySelectorAll('.stream-tile'));
    const streamInput = document.getElementById('streamInput');
    const sliderRange = document.getElementById('zscoreRange');
    const sliderValue = document.getElementById('zscoreValue');
    const sliderHidden = document.getElementById('zscoreInput');
    const galleryCards = Array.from(document.querySelectorAll('.gallery-card'));
    const lightboxOverlay = document.getElementById('galleryLightbox');
    const lightboxImage = lightboxOverlay?.querySelector('img');
    const lightboxCaption = lightboxOverlay?.querySelector('.lightbox-caption');
    const lightboxClose = lightboxOverlay?.querySelector('.lightbox-close');
    const statCounts = Array.from(document.querySelectorAll('[data-target-number]'));
    const reveals = Array.from(document.querySelectorAll('.reveal-on-scroll'));
    const placeholders = ['Search "Engineering"...', 'Search "Medicine"...', 'Search "Design"...', 'Search "Business"...'];

    let placeholderIndex = 0;
    const updatePlaceholder = () => {
        if (!searchInput || REDUCED_MOTION) {
            return;
        }
        placeholderIndex = (placeholderIndex + 1) % placeholders.length;
        searchInput.setAttribute('placeholder', placeholders[placeholderIndex]);
    };

    let placeholderInterval = null;
    if (searchInput && !REDUCED_MOTION) {
        placeholderInterval = setInterval(updatePlaceholder, 4200);
    }

    const handleNavScroll = () => {
        if (!nav) {
            return;
        }
        nav.classList.toggle('scrolled', window.scrollY > 50);
    };

    handleNavScroll();
    window.addEventListener('scroll', handleNavScroll, { passive: true });

    const toggleDrawer = (open) => {
        if (!drawer) {
            return;
        }
        drawer.classList.toggle('open', open);
        drawer.setAttribute('aria-hidden', String(!open));
        drawerToggle?.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('drawer-open', open);
    };

    drawerToggle?.addEventListener('click', () => {
        toggleDrawer(true);
    });

    drawerClose?.addEventListener('click', () => {
        toggleDrawer(false);
    });

    drawer?.addEventListener('click', (event) => {
        if (event.target === drawer) {
            toggleDrawer(false);
        }
    });

    if (!REDUCED_MOTION && statCounts.length) {
        const statsObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const targetEl = entry.target;
                    const targetValue = Number(targetEl.dataset.targetNumber) || 0;
                    const suffix = targetEl.dataset.suffix || '';
                    const duration = 1400;
                    const startTime = performance.now();

                    const tick = (time) => {
                        const progress = Math.min((time - startTime) / duration, 1);
                        const current = Math.floor(progress * targetValue).toLocaleString();
                        targetEl.textContent = `${current}${suffix}`;
                        if (progress < 1) {
                            requestAnimationFrame(tick);
                        }
                    };

                    requestAnimationFrame(tick);
                    observer.unobserve(targetEl);
                }
            });
        }, { threshold: 0.35 });

        statCounts.forEach((stat) => {
            statsObserver.observe(stat);
        });
    } else {
        statCounts.forEach((stat) => {
            const fallbackValue = Number(stat.dataset.targetNumber) || 0;
            const suffix = stat.dataset.suffix || '';
            stat.textContent = `${fallbackValue.toLocaleString()}${suffix}`;
        });
    }

    if (!REDUCED_MOTION && reveals.length) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, { threshold: 0.2 });

        reveals.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
            revealObserver.observe(item);
        });
    } else {
        reveals.forEach((item) => item.classList.add('is-visible'));
    }

    const applyFilters = () => {
        const query = searchInput?.value?.trim().toLowerCase() || '';
        const activeFilters = filterChips
            .filter((chip) => chip.classList.contains('active'))
            .map((chip) => chip.dataset.filter?.toLowerCase());

        universityCards.forEach((card) => {
            const searchable = card.dataset.search?.toLowerCase() || '';
            const streams = card.dataset.streams?.toLowerCase() || '';
            const matchesQuery = query === '' || searchable.includes(query);
            const matchesFilters = activeFilters.length === 0 || activeFilters.every((filter) => streams.includes(filter));
            card.style.display = matchesQuery && matchesFilters ? '' : 'none';
        });
    };

    searchInput?.addEventListener('input', applyFilters);

    filterChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            chip.classList.toggle('active');
            applyFilters();
        });

    streamTiles.forEach((tile) => {
        tile.addEventListener('click', () => {
            streamTiles.forEach((other) => {
                other.classList.remove('active');
                other.setAttribute('aria-pressed', 'false');
            });
            tile.classList.add('active');
            tile.setAttribute('aria-pressed', 'true');
            if (streamInput) {
                streamInput.value = tile.dataset.value || '';
            }
        });
    });

    if (sliderRange && sliderValue && sliderHidden) {
        const updateSlider = (value) => {
            const formatted = Number(value).toFixed(3);
            sliderValue.textContent = formatted;
            sliderHidden.value = formatted;
        };

        sliderRange.addEventListener('input', (event) => {
            updateSlider(event.target.value);
        });
    }

    if (galleryCards.length && lightboxOverlay && lightboxImage && lightboxCaption) {
        const openLightbox = (card) => {
            const src = card.dataset.src;
            const caption = card.dataset.caption || '';
            lightboxImage.src = src;
            lightboxImage.alt = caption;
            lightboxCaption.textContent = caption;
            lightboxOverlay.classList.add('active');
            lightboxOverlay.setAttribute('aria-hidden', 'false');
        };

        const closeLightbox = () => {
            lightboxOverlay.classList.remove('active');
            lightboxOverlay.setAttribute('aria-hidden', 'true');
            lightboxImage.removeAttribute('src');
        };

        galleryCards.forEach((card) => {
            card.addEventListener('click', () => openLightbox(card));
        });

        lightboxClose?.addEventListener('click', closeLightbox);
        lightboxOverlay.addEventListener('click', (event) => {
            if (event.target === lightboxOverlay) {
                closeLightbox();
            }
        });

        document.addEventListener('keyup', (event) => {
            if (event.key === 'Escape' && lightboxOverlay.classList.contains('active')) {
                closeLightbox();
            }
        });
    }
    });
});
