// ── Swiper ────────────────────────────────────────────────

const BREAKPOINTS = {
    0: { slidesPerView: 'auto', spaceBetween: 16 },
    768: { slidesPerView: 2, spaceBetween: 16 },
    992: { slidesPerView: 3, spaceBetween: 16 },
    1200: { slidesPerView: 4, spaceBetween: 24 },
};

const PAGINATION = {
    el: '.swiper-pagination',
    type: 'fraction',
};

function updateNavState(wrap, isBeginning, isEnd) {
    wrap.querySelectorAll('.btn-prev').forEach(function (btn) {
        btn.classList.toggle('swiper-button-disabled', isBeginning);
    });
    wrap.querySelectorAll('.btn-next').forEach(function (btn) {
        btn.classList.toggle('swiper-button-disabled', isEnd);
    });
}

function applySupTags(wrap) {
    wrap.querySelectorAll('.card-title, .card-desc').forEach(function (el) {
        el.innerHTML = el.innerHTML.replace(/(<sup[^>]*>®<\/sup>)|®/g, function (match, already) {
            return already ? already : '<sup class="sup-reg">®</sup>';
        });
    });
}

// ── Real card render ──────────────────────────────────────

function buildCardSlide(r, wrap) {
    const template = wrap.querySelector('.clnc-icon-template');
    const iconHtml = template ? template.innerHTML : '';
    const iconPos = wrap.getAttribute('data-icon-pos') || 'right';

    let linkContent = '';
    if (iconPos === 'left') {
        linkContent = `${iconHtml} Learn More`;
    } else {
        linkContent = `Learn More ${iconHtml}`;
    }

    return `
    <div class="swiper-slide">
      <div class="resource-card">
        <div class="swiper-card-body">
          ${r.isNew ? '<span class="badge-new">New</span>' : ''}
          <div class="card-title">${r.title}</div>
          <div class="card-desc">${r.description}</div>
        </div>
        <a href="${r.link}" class="card-link">
          ${linkContent}
        </a>
      </div>
    </div>`;
}

function renderSlides(wrap, swiperInstanceObj, pages) {
    const wrapper = wrap.querySelector('.swiper-wrapper');
    const countEl = wrap.querySelector('.resource-count');

    if (swiperInstanceObj.instance) {
        swiperInstanceObj.instance.destroy(true, true);
        swiperInstanceObj.instance = null;
    }

    countEl.textContent = `Showing ${pages.length} resource${pages.length !== 1 ? 's' : ''}`;

    if (pages.length === 0) {
        wrapper.innerHTML = '<div class="empty-state">No resources found for this filter.</div>';
        updateNavState(wrap, true, true);
        return;
    }

    wrapper.innerHTML = pages.map(r => buildCardSlide(r, wrap)).join('');

    const swiperEl = wrap.querySelector('.swiper');

    // Adjust pagination element specific to this instance
    const instancePagination = {
        el: wrap.querySelector('.swiper-pagination'),
        type: 'fraction',
    };

    swiperInstanceObj.instance = new Swiper(swiperEl, {
        slidesPerView: 4,
        spaceBetween: 24,
        breakpoints: BREAKPOINTS,
        pagination: instancePagination,
        autoHeight: true,
        on: {
            init: function () {
                updateNavState(wrap, this.isBeginning, this.isEnd);
                applySupTags(wrap);
            },
            slideChange: function () {
                updateNavState(wrap, this.isBeginning, this.isEnd);
            },
        },
    });
}

function initCarousel(wrap) {
    const swiperInstanceObj = { instance: null };

    // Get resources from data attribute
    let allResources = [];
    try {
        const data = wrap.getAttribute('data-resources');
        if (data) {
            allResources = JSON.parse(data);
        }
    } catch (e) {
        console.error('Failed to parse carousel resources:', e);
    }

    function filterResources(category) {
        if (category === 'all') {
            return allResources;
        }
        return allResources.filter(r => r.category === category);
    }

    // ── Nav buttons ───────────────────────────────────────────
    wrap.querySelectorAll('.btn-prev').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (swiperInstanceObj.instance) swiperInstanceObj.instance.slidePrev();
        });
    });

    wrap.querySelectorAll('.btn-next').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (swiperInstanceObj.instance) swiperInstanceObj.instance.slideNext();
        });
    });

    // ── Filter buttons ────────────────────────────────────────
    const filterBtns = wrap.querySelectorAll('.filter-btn');
    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterBtns.forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            const category = this.getAttribute('data-filter');
            const filteredPages = filterResources(category);
            renderSlides(wrap, swiperInstanceObj, filteredPages);
        });
    });

    // ── Initial Render ────────────────────────────────────────
    renderSlides(wrap, swiperInstanceObj, allResources);
}

// ── Document Ready or Elementor Init ───────────────────────
function initAllCarousels() {
    document.querySelectorAll('.clnc-carousel-wrap').forEach(wrap => {
        // Prevent double initialization
        if (!wrap.classList.contains('clnc-initialized')) {
            wrap.classList.add('clnc-initialized');
            initCarousel(wrap);
        }
    });
}

document.addEventListener('DOMContentLoaded', initAllCarousels);

// For Elementor Editor support
if (window.elementorFrontend) {
    window.addEventListener('elementor/frontend/init', () => {
        elementorFrontend.hooks.addAction('frontend/element_ready/lnc_featured_carousel.default', function ($scope) {
            const wrap = $scope[0].querySelector('.clnc-carousel-wrap');
            if (wrap && !wrap.classList.contains('clnc-initialized')) {
                wrap.classList.add('clnc-initialized');
                initCarousel(wrap);
            }
        });
    });
}
