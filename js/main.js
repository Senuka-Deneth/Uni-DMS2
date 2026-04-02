const App = (() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const init = () => {
        initNavDrawer();
        initScrollReveal();
        initHeroStats();
        initHeroScroll();
        initFilterChips();
        initFinderControls();
        initSearchPlaceholder();
        initThemeToggle();
    };

    const initNavDrawer = () => {
        const navToggle = document.querySelector('.nav-toggle');
        const navDrawer = document.querySelector('.nav-drawer');
        const navClose = document.querySelector('.nav-drawer__close');
        const floatingNav = document.querySelector('.floating-navbar');

        const setNavState = (open) => {
            if (!navDrawer || !navToggle) return;
            navDrawer.classList.toggle('is-open', open);
            navToggle.setAttribute('aria-expanded', open);
        };

        navToggle?.addEventListener('click', () => setNavState(true));
        navClose?.addEventListener('click', () => setNavState(false));
        navDrawer?.addEventListener('click', (event) => {
            if (event.target === navDrawer) {
                setNavState(false);
            }
        });

        document.addEventListener('keyup', (event) => {
            if (event.key === 'Escape') {
                setNavState(false);
            }
        });

        const handleNavScroll = () => {
            if (!floatingNav) return;
            const isScrolled = window.scrollY > 100;
            floatingNav.classList.toggle('floating-navbar--scrolled', isScrolled);
        };

        handleNavScroll();
        window.addEventListener('scroll', handleNavScroll, { passive: true });
    };

    const initScrollReveal = () => {
        const revealElements = Array.from(document.querySelectorAll('.reveal-on-scroll'));
        if (!revealElements.length) {
            return;
        }

        revealElements.forEach((el, index) => {
            el.style.transitionDelay = `${(index % 6) * 80}ms`;
        });

        if (prefersReducedMotion) {
            revealElements.forEach((el) => el.classList.add('reveal-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('reveal-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        revealElements.forEach((el) => observer.observe(el));
    };

    const initHeroStats = () => {
        const stats = Array.from(document.querySelectorAll('.stat-count'));
        if (!stats.length) {
            return;
        }

        if (prefersReducedMotion) {
            stats.forEach((stat) => {
                const target = stat.dataset.targetNumber;
                if (target) {
                    stat.textContent = `${target}${stat.dataset.suffix || ''}`;
                }
            });
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateValue(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        stats.forEach((stat) => observer.observe(stat));
    };

    const animateValue = (el) => {
        const duration = 1500;
        const target = parseInt(el.dataset.targetNumber || '0', 10);
        const suffix = el.dataset.suffix || '';
        let start = null;

        const step = (timestamp) => {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = `${Math.floor(eased * target)}${suffix}`;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                el.textContent = `${target}${suffix}`;
            }
        };

        window.requestAnimationFrame(step);
    };

    const initHeroScroll = () => {
        const scrollButton = document.querySelector('[data-scroll-target]');
        scrollButton?.addEventListener('click', (event) => {
            event.preventDefault();
            const targetSelector = scrollButton.dataset.scrollTarget;
            const target = targetSelector ? document.querySelector(targetSelector) : null;
            if (target) {
                target.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
            }
        });
    };

    const initFilterChips = () => {
        const chips = Array.from(document.querySelectorAll('.filter-chip'));
        if (!chips.length) {
            return;
        }
        const cards = Array.from(document.querySelectorAll('.university-card'));

        const applyFilter = (chip) => {
            const value = chip.dataset.filter || 'All';
            chips.forEach((item) => {
                const isActive = item === chip;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-pressed', isActive);
            });
            const normalized = value.toLowerCase();
            cards.forEach((card) => {
                const streams = (card.dataset.streams || '').toLowerCase();
                const matches = normalized === 'all' || streams.includes(normalized);
                card.style.display = matches ? '' : 'none';
            });
        };

        chips.forEach((chip) => {
            chip.addEventListener('click', () => applyFilter(chip));
        });
    };

    const initFinderControls = () => {
        const slider = document.getElementById('zscoreRange');
        const sliderValue = document.getElementById('zscoreValue');
        const sliderInput = document.getElementById('zscoreInput');
        const resetSlider = document.querySelector('[data-reset-slider]');
        const streamTiles = Array.from(document.querySelectorAll('.stream-tile'));
        const streamInput = document.getElementById('streamInput');

        const updateSliderDisplay = (value) => {
            if (sliderValue) {
                sliderValue.textContent = value;
            }
            if (sliderInput) {
                sliderInput.value = value;
            }
        };

        slider?.addEventListener('input', (event) => {
            updateSliderDisplay(event.target.value);
        });

        resetSlider?.addEventListener('click', () => {
            const defaultValue = 3.0;
            if (slider) {
                slider.value = defaultValue;
            }
            updateSliderDisplay(defaultValue.toFixed(3));
        });

        streamTiles.forEach((tile) => {
            tile.addEventListener('click', () => {
                const value = tile.dataset.value;
                streamTiles.forEach((item) => item.classList.remove('is-active'));
                tile.classList.add('is-active');
                tile.setAttribute('aria-pressed', 'true');
                streamTiles.forEach((item) => {
                    if (item !== tile) {
                        item.setAttribute('aria-pressed', 'false');
                    }
                });
                if (streamInput) {
                    streamInput.value = value;
                }
            });
        });
    };

    const initSearchPlaceholder = () => {
        const input = document.getElementById('searchInput');
        if (!input) return;
        const options = [
            "Search 'Computer Science'...",
            "Search 'Medicine'...",
            "Search 'Engineering'...",
            "Search 'Business'...",
        ];
        let index = 0;

        const cycle = () => {
            if (prefersReducedMotion) return;
            input.classList.add('placeholder-fade');
            setTimeout(() => {
                index = (index + 1) % options.length;
                input.placeholder = options[index];
                input.classList.remove('placeholder-fade');
            }, 220);
        };

        input.placeholder = options[index];
        setInterval(cycle, 2500);
    };

    const initThemeToggle = () => {
        const themeToggle = document.getElementById('theme-toggle');
        if (!themeToggle) return;

        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');

        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeToggle.textContent = '☀️';
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            themeToggle.textContent = '🌙';
        }

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeToggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
        });
    };

    return { init };
})();

document.addEventListener('DOMContentLoaded', App.init);
