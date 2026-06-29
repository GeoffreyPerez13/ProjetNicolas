document.addEventListener('DOMContentLoaded', () => {

    // ========================================
    // Page Loader
    // ========================================
    const loader = document.querySelector('.page-loader');
    if (loader) {
        window.addEventListener('load', () => {
            loader.classList.add('loaded');
            setTimeout(() => loader.remove(), 500);
        });
        // Fallback: hide after 2s max
        setTimeout(() => {
            loader.classList.add('loaded');
        }, 2000);
    }

    // ========================================
    // Mobile Menu
    // ========================================
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');
    const mobileOverlay = document.getElementById('mobileOverlay');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            mainNav.classList.toggle('open');
            mobileOverlay.classList.toggle('active');
            document.body.style.overflow = mainNav.classList.contains('open') ? 'hidden' : '';
        });

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', () => {
                menuToggle.classList.remove('active');
                mainNav.classList.remove('open');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    }

    // Mobile dropdown toggle
    document.querySelectorAll('.nav-dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                toggle.closest('.nav-dropdown').classList.toggle('open');
            }
        });
    });

    // Close mobile menu on resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && mainNav?.classList.contains('open')) {
            menuToggle?.classList.remove('active');
            mainNav.classList.remove('open');
            mobileOverlay?.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // ========================================
    // Header Hide/Show on Scroll
    // ========================================
    const header = document.getElementById('siteHeader');
    let lastScroll = 0;
    let ticking = false;

    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                const currentScroll = window.pageYOffset;

                if (currentScroll > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }

                if (currentScroll > lastScroll && currentScroll > 200) {
                    header.classList.add('hidden');
                } else {
                    header.classList.remove('hidden');
                }

                lastScroll = currentScroll;
                ticking = false;
            });
            ticking = true;
        }
    });

    // ========================================
    // Banner Slider (with touch/swipe support)
    // ========================================
    const slides = document.querySelectorAll('.banner-slide');
    const dots = document.querySelectorAll('.banner-dot');
    const bannerEl = document.querySelector('.hero-banner');

    if (slides.length > 1) {
        let currentSlide = 0;
        let slideInterval;
        let touchStartX = 0;
        let touchEndX = 0;

        function goToSlide(index) {
            slides[currentSlide].classList.remove('active');
            dots[currentSlide]?.classList.remove('active');
            currentSlide = index;
            slides[currentSlide].classList.add('active');
            dots[currentSlide]?.classList.add('active');
        }

        function nextSlide() {
            goToSlide((currentSlide + 1) % slides.length);
        }

        function prevSlide() {
            goToSlide((currentSlide - 1 + slides.length) % slides.length);
        }

        function startSlider() {
            slideInterval = setInterval(nextSlide, 5000);
        }

        function stopSlider() {
            clearInterval(slideInterval);
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                stopSlider();
                goToSlide(index);
                startSlider();
            });
        });

        // Touch/swipe support for banner
        if (bannerEl) {
            bannerEl.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                stopSlider();
            }, { passive: true });

            bannerEl.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > 50) {
                    diff > 0 ? nextSlide() : prevSlide();
                }
                startSlider();
            }, { passive: true });
        }

        startSlider();
    }

    // ========================================
    // Back to Top Button
    // ========================================
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 400) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });

        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ========================================
    // Scroll Arrows
    // ========================================
    const scrollTopBtn = document.getElementById('scrollTop');
    const scrollBottomBtn = document.getElementById('scrollBottom');

    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if (scrollBottomBtn) {
        scrollBottomBtn.addEventListener('click', () => {
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        });
    }

    // ========================================
    // Lightbox with Navigation
    // ========================================
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxMeta = document.getElementById('lightboxMeta');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxPrev = document.getElementById('lightboxPrev');
    const lightboxNext = document.getElementById('lightboxNext');
    const lightboxCounter = document.getElementById('lightboxCounter');

    const lightboxItems = document.querySelectorAll('[data-lightbox]');
    let currentLightboxIndex = 0;

    function showLightboxItem(index) {
        const item = lightboxItems[index];
        if (!item || !lightbox || !lightboxImg) return;

        const src = item.dataset.lightbox;
        const title = item.dataset.title || '';
        const meta = item.dataset.meta || '';

        lightboxImg.src = src;
        if (lightboxTitle) lightboxTitle.textContent = title;
        if (lightboxMeta) lightboxMeta.textContent = meta;
        if (lightboxCounter) lightboxCounter.textContent = `${index + 1} / ${lightboxItems.length}`;
        currentLightboxIndex = index;
    }

    lightboxItems.forEach((item, index) => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            showLightboxItem(index);
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });

    if (lightboxPrev) {
        lightboxPrev.addEventListener('click', (e) => {
            e.stopPropagation();
            const prev = (currentLightboxIndex - 1 + lightboxItems.length) % lightboxItems.length;
            showLightboxItem(prev);
        });
    }

    if (lightboxNext) {
        lightboxNext.addEventListener('click', (e) => {
            e.stopPropagation();
            const next = (currentLightboxIndex + 1) % lightboxItems.length;
            showLightboxItem(next);
        });
    }

    if (lightboxClose) {
        lightboxClose.addEventListener('click', closeLightbox);
    }

    if (lightbox) {
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });

        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft' && lightboxItems.length > 1) {
                showLightboxItem((currentLightboxIndex - 1 + lightboxItems.length) % lightboxItems.length);
            }
            if (e.key === 'ArrowRight' && lightboxItems.length > 1) {
                showLightboxItem((currentLightboxIndex + 1) % lightboxItems.length);
            }
        });

        // Touch swipe in lightbox
        let lbTouchStartX = 0;
        lightbox.addEventListener('touchstart', (e) => {
            lbTouchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        lightbox.addEventListener('touchend', (e) => {
            const diff = lbTouchStartX - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 50 && lightboxItems.length > 1) {
                if (diff > 0) {
                    showLightboxItem((currentLightboxIndex + 1) % lightboxItems.length);
                } else {
                    showLightboxItem((currentLightboxIndex - 1 + lightboxItems.length) % lightboxItems.length);
                }
            }
        }, { passive: true });
    }

    function closeLightbox() {
        if (lightbox) {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // ========================================
    // Lazy Loading Images
    // ========================================
    document.querySelectorAll('img[loading="lazy"]').forEach(img => {
        if (img.complete) {
            img.classList.add('loaded');
        } else {
            img.addEventListener('load', () => img.classList.add('loaded'));
            img.addEventListener('error', () => img.classList.add('loaded'));
        }
    });

    // ========================================
    // Fade-in animations on scroll
    // ========================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const fadeObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-visible');
                fadeObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in').forEach(el => {
        fadeObserver.observe(el);
    });

    // ========================================
    // Admin: Mobile sidebar toggle
    // ========================================
    const adminToggle = document.querySelector('.admin-mobile-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const adminOverlay = document.querySelector('.admin-mobile-overlay');

    if (adminToggle && adminSidebar) {
        adminToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('open');
            adminOverlay?.classList.toggle('active');
            document.body.style.overflow = adminSidebar.classList.contains('open') ? 'hidden' : '';
        });

        if (adminOverlay) {
            adminOverlay.addEventListener('click', () => {
                adminSidebar.classList.remove('open');
                adminOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    }

});
