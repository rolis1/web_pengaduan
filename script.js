document.addEventListener('DOMContentLoaded', function() {
    // ======================== BAGIAN 1: ANIMASI ========================
    
    const startCounters = () => {
        const counterElements = document.querySelectorAll('.stat-item .counter');
        counterElements.forEach(counter => {
            const targetValue = parseInt(counter.parentElement.parentElement.dataset.count) || 0;
            const duration = 1500;
            const step = Math.ceil(targetValue / (duration / 20));
            let current = 0;

            const updateCounter = () => {
                if (current < targetValue) {
                    current += step;
                    if (current > targetValue) current = targetValue;
                    counter.textContent = current;
                    setTimeout(updateCounter, 20);
                }
            };

            updateCounter();
        });
    };

    const parallaxElements = document.querySelector('.about-main-image');
    const handleParallax = () => {
        const scrollPosition = window.pageYOffset;
        if (parallaxElements) {
            parallaxElements.style.transform = `translateY(${scrollPosition * 0.05}px) scale(1)`;
        }
    };

    const animateElement = (element, delay = 0) => {
        setTimeout(() => {
            element.classList.add('animated');
        }, delay);
    };

    const isElementInViewport = (el) => {
        const rect = el.getBoundingClientRect();
        return (
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8 &&
            rect.bottom >= 0
        );
    };

    const checkVisibility = () => {
        const aboutSection = document.querySelector('.about-section');
        if (isElementInViewport(aboutSection)) {
            startCounters();
            const elementsToAnimate = [
                document.querySelector('.about-image-container'),
                document.querySelector('.stats-container'),
                document.querySelector('.who-we-are-header'),
                document.querySelector('.about-right p'),
                document.querySelector('.mission'),
                document.querySelector('.vision')
            ];
            elementsToAnimate.forEach((element, index) => {
                if (element) animateElement(element, index * 200);
            });
            window.removeEventListener('scroll', checkVisibility);
        }
    };

    const addInitialClasses = () => {
        const elementsToAnimate = [
            document.querySelector('.about-image-container'),
            document.querySelector('.stats-container'),
            document.querySelector('.who-we-are-header'),
            document.querySelector('.about-right p'),
            document.querySelector('.mission'),
            document.querySelector('.vision')
        ];
        elementsToAnimate.forEach(element => {
            if (element) {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                element.style.transition = 'opacity 0.7s ease, transform 0.7s ease';
            }
        });

        const style = document.createElement('style');
        style.textContent = `
            .animated {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
        `;
        document.head.appendChild(style);
    };

    addInitialClasses();
    window.addEventListener('scroll', checkVisibility);
    window.addEventListener('scroll', handleParallax, { passive: true });
    checkVisibility();

    // ======================== BAGIAN 2: NAVIGASI ========================

    const navLinks = document.querySelectorAll('.nav-center a');
    const header = document.getElementById('main-header');
    let sections = [];

    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href.startsWith('#')) {
            const sectionId = href.substring(1);
            const section = document.getElementById(sectionId);
            if (section) {
                sections.push({
                    id: sectionId,
                    element: section,
                    top: section.offsetTop,
                    bottom: section.offsetTop + section.offsetHeight
                });
            }
        }
    });

    sections.sort((a, b) => a.top - b.top);

    const setActiveOnScroll = () => {
        const scrollPosition = window.scrollY + (window.innerHeight / 3);
        let currentSection = null;

        for (let i = 0; i < sections.length; i++) {
            const section = sections[i];
            if (scrollPosition >= section.top && scrollPosition < section.bottom) {
                currentSection = section.id;
                break;
            }
        }

        if (!currentSection && sections.length > 0 && scrollPosition >= sections[sections.length - 1].top) {
            currentSection = sections[sections.length - 1].id;
        }

        if (currentSection) {
            navLinks.forEach(link => {
                const linkHref = link.getAttribute('href');
                link.classList.toggle('active', linkHref === `#${currentSection}`);
            });

            if (window.location.hash !== `#${currentSection}`) {
                history.replaceState(null, null, `#${currentSection}`);
            }
        }
    };

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetElement = document.querySelector(href);
                if (targetElement) {
                    navLinks.forEach(item => item.classList.remove('active'));
                    this.classList.add('active');

                    const headerHeight = header.offsetHeight;
                    const targetPosition = targetElement.offsetTop - headerHeight;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                    history.pushState(null, null, href);
                }
            }
        });
    });

    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > 100) {
            header.classList.add('scrolled');
            header.style.transform = 'translateY(0)';
        } else {
            header.classList.remove('scrolled');
            header.style.transform = 'translateY(0)';
        }

        clearTimeout(window.scrollTimeout);
        window.scrollTimeout = setTimeout(setActiveOnScroll, 100);
    });

    setTimeout(() => {
        sections.forEach(section => {
            section.top = section.element.offsetTop;
            section.bottom = section.top + section.element.offsetHeight;
        });

        if (window.location.hash) {
            const hash = window.location.hash;
            const targetElement = document.querySelector(hash);
            if (targetElement) {
                navLinks.forEach(link => {
                    link.classList.toggle('active', link.getAttribute('href') === hash);
                });

                window.scrollTo({
                    top: targetElement.offsetTop - header.offsetHeight,
                    behavior: 'auto'
                });
            }
        } else {
            if (sections.length > 0) {
                history.replaceState(null, null, `#${sections[0].id}`);
                navLinks.forEach(link => {
                    link.classList.toggle('active', link.getAttribute('href') === `#${sections[0].id}`);
                });
            }
        }

        setActiveOnScroll();
    }, 300);

    window.addEventListener('resize', function() {
        sections.forEach(section => {
            section.top = section.element.offsetTop;
            section.bottom = section.top + section.element.offsetHeight;
        });
        setActiveOnScroll();
    });
});

document.addEventListener("DOMContentLoaded", function() {
    // Observer untuk animasi saat elemen terlihat
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                if (entry.target.classList.contains('animate-card')) {
                    const delay = entry.target.getAttribute('data-delay');
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, parseInt(delay));
                } else {
                    entry.target.classList.add('visible');
                }
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    // Mengamati header section
    const header = document.querySelector('.animate-fade-in');
    if (header) observer.observe(header);

    // Mengamati kartu layanan
    const cards = document.querySelectorAll('.animate-card');
    cards.forEach(card => {
        observer.observe(card);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const contactSection = document.querySelector('.contact-section');
    const contactHeader = document.querySelector('.contact-header');
    const contactItems = document.querySelectorAll('.contact-item');

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observerCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                contactHeader.classList.add('visible');
                contactSection.classList.add('visible');

                // Add index-based staggered animation
                contactItems.forEach((item, index) => {
                    item.style.setProperty('--index', index);
                });
            }
        });
    };

    const observer = new IntersectionObserver(observerCallback, observerOptions);
    observer.observe(contactSection);

    // Interactive hover effects
    contactItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.style.transform = 'translateY(-15px) scale(1.05)';
        });

        item.addEventListener('mouseleave', () => {
            item.style.transform = 'translateY(0) scale(1)';
        });
    });
});