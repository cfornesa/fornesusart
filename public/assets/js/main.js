// Drag-and-drop row reordering for admin tables
document.querySelectorAll('tbody[data-reorder-url]').forEach(tbody => {
    const url = tbody.dataset.reorderUrl;
    const visibility = tbody.dataset.reorderVisibility || '';
    const statusId = tbody.dataset.reorderStatus || 'reorder-status';
    let dragging = null;

    tbody.querySelectorAll('tr').forEach(row => {
        row.setAttribute('draggable', 'true');

        row.addEventListener('dragstart', e => {
            dragging = row;
            row.classList.add('drag-active');
            e.dataTransfer.effectAllowed = 'move';
        });

        row.addEventListener('dragend', () => {
            row.classList.remove('drag-active');
            tbody.querySelectorAll('tr').forEach(r => r.classList.remove('drag-over'));
            dragging = null;

            const ids = [...tbody.querySelectorAll('tr[data-id]')]
                .map(r => r.dataset.id)
                .join(',');

            const status = document.getElementById(statusId);
            const body = new URLSearchParams({ ids });
            if (visibility) {
                body.set('visibility', visibility);
            }
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
            })
            .then(r => r.json())
            .then(() => {
                if (status) { status.textContent = 'Order saved.'; setTimeout(() => status.textContent = '', 2000); }
            });
        });

        row.addEventListener('dragover', e => {
            e.preventDefault();
            if (!dragging || dragging === row) return;
            const rect = row.getBoundingClientRect();
            const after = e.clientY > rect.top + rect.height / 2;
            tbody.insertBefore(dragging, after ? row.nextSibling : row);
        });

        row.addEventListener('dragenter', e => {
            e.preventDefault();
            if (row !== dragging) row.classList.add('drag-over');
        });

        row.addEventListener('dragleave', () => row.classList.remove('drag-over'));
    });
});

// Artwork form: auto-generate slug from title, stop if user edits slug manually
(function () {
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput  = document.querySelector('input[name="slug"]');
    if (!titleInput || !slugInput) return;

    // Don't auto-fill in edit mode (slug already set)
    let autoFill = slugInput.value === '';

    slugInput.addEventListener('input', () => {
        autoFill = slugInput.value === '';
    });

    titleInput.addEventListener('input', () => {
        if (!autoFill) return;
        slugInput.value = titleInput.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-');
    });
})();

// Gallery: "See More" to reveal overflow works
(function () {
    const btn = document.getElementById('works-see-more');
    if (!btn) return;
    btn.addEventListener('click', () => {
        document.querySelectorAll('.gallery-work-overflow').forEach(el => {
            el.classList.remove('gallery-work-overflow');
        });
        btn.setAttribute('aria-expanded', 'true');
        btn.remove();
    });
})();

// Generic slug auto-fill: any input[id$="-name"] → sibling input[id$="-slug"]
['cat', 'exhibit', 'page'].forEach(prefix => {
    const nameInput = document.getElementById(prefix + '-name');
    const slugInput = document.getElementById(prefix + '-slug');
    if (!nameInput || !slugInput) return;

    let autoFill = slugInput.value === '';
    slugInput.addEventListener('input', () => { autoFill = slugInput.value === ''; });
    nameInput.addEventListener('input', () => {
        if (!autoFill) return;
        slugInput.value = nameInput.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-');
    });
});

// New category form: auto-generate slug from name
(function () {
    const nameInput = document.getElementById('new-cat-name');
    const slugInput = document.getElementById('new-cat-slug');
    if (!nameInput || !slugInput) return;

    let autoFill = true;
    slugInput.addEventListener('input', () => { autoFill = slugInput.value === ''; });
    nameInput.addEventListener('input', () => {
        if (!autoFill) return;
        slugInput.value = nameInput.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-');
    });
})();

// Public nav: slide-out drawer navigation on mobile viewports (< 900px)
(function () {
    const shell = document.querySelector('[data-site-nav-shell]');
    if (!shell) return;

    const nav = shell.querySelector('[data-site-nav]');
    const navList = shell.querySelector('[data-site-nav-list]');
    const toggle = shell.querySelector('[data-site-nav-toggle]');
    const overflow = shell.querySelector('[data-site-nav-overflow]');
    const overflowList = shell.querySelector('[data-site-nav-overflow-list]');
    const items = [...shell.querySelectorAll('[data-site-nav-item]')];

    if (!nav || !navList || !toggle || !overflow || !overflowList || items.length === 0) return;

    // Remove the hidden attribute so CSS can control visibility responsively
    toggle.removeAttribute('hidden');

    function closeMenu() {
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open navigation menu');
        toggle.classList.remove('is-open');
        overflow.hidden = true;
    }

    function buildOverflowItem(sourceLink) {
        const li = document.createElement('li');
        li.className = 'site-nav-overflow-item';

        const clone = sourceLink.cloneNode(true);
        clone.classList.remove('active');
        if (sourceLink.classList.contains('active')) {
            clone.classList.add('active');
            clone.setAttribute('aria-current', 'page');
        }

        li.appendChild(clone);
        return li;
    }

    // Populate the mobile overflow drawer menu once with all items
    items.forEach(item => {
        const link = item.querySelector('a');
        if (link) {
            overflowList.appendChild(buildOverflowItem(link));
        }
    });

    toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        toggle.setAttribute('aria-label', isOpen ? 'Open navigation menu' : 'Close navigation menu');
        toggle.classList.toggle('is-open', !isOpen);
        overflow.hidden = isOpen;
    });

    // iOS Safari: backdrop-filter + sticky header drops synthesised click events
    toggle.addEventListener('touchend', (e) => {
        e.preventDefault();
        toggle.click();
    }, { passive: false });

    document.addEventListener('click', event => {
        if (overflow.hidden) return;
        if (shell.contains(event.target)) return;
        closeMenu();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !overflow.hidden) {
            closeMenu();
            toggle.focus();
        }
    });
})();

// Work description: read more / read less toggle
document.querySelectorAll('.desc-read-more').forEach(btn => {
    btn.addEventListener('click', () => {
        const wrap = btn.closest('.work-description');
        wrap.classList.add('is-expanded');
        btn.setAttribute('aria-expanded', 'true');
        const lessBtn = wrap.querySelector('.desc-read-less');
        if (lessBtn) {
            lessBtn.focus();
        }
    });
});
document.querySelectorAll('.desc-read-less').forEach(btn => {
    btn.addEventListener('click', () => {
        const wrap = btn.closest('.work-description');
        wrap.classList.remove('is-expanded');
        btn.setAttribute('aria-expanded', 'false');
        const moreBtn = wrap.querySelector('.desc-read-more');
        if (moreBtn) {
            moreBtn.setAttribute('aria-expanded', 'false');
            moreBtn.focus();
        }
    });
});

// Admin form: show/hide panels based on radio toggle groups
document.querySelectorAll('.toggle-group').forEach(group => {
    const target = group.dataset.target;
    const radios = group.querySelectorAll('input[type="radio"]');

    function syncPanels() {
        const selected = group.querySelector('input[type="radio"]:checked');
        if (!selected) return;
        const panelKey = target + '-' + selected.value;
        document.querySelectorAll(`.toggle-panel`).forEach(panel => {
            if (panel.dataset.panel && panel.dataset.panel.startsWith(target + '-')) {
                panel.style.display = panel.dataset.panel === panelKey ? 'block' : 'none';
            }
        });
    }

    radios.forEach(r => r.addEventListener('change', syncPanels));
    syncPanels();
});

// Admin artwork form: ordered mixed-media carousel builder
(function () {
    const builder = document.querySelector('[data-artwork-media-builder]');
    if (!builder) return;

    const list = builder.querySelector('[data-slide-list]');
    const templates = {
        image: document.getElementById('artwork-slide-template-image'),
        video: document.getElementById('artwork-slide-template-video'),
        iframe: document.getElementById('artwork-slide-template-iframe'),
    };

    function assetUrlFor(kind, asset) {
        if (!asset) return '';
        if (kind === 'image') return asset.legacy_url || asset.url || '';
        return asset.url || '';
    }

    function hydratePreview(card, kind, assetUrl, posterUrl = '') {
        const preview = card.querySelector('[data-slide-preview]');
        if (!preview) return;

        preview.innerHTML = '';
        if (kind === 'image' && assetUrl) {
            const img = document.createElement('img');
            img.src = assetUrl;
            img.alt = '';
            preview.appendChild(img);
            return;
        }

        if (kind === 'video' && assetUrl) {
            const video = document.createElement('video');
            video.src = assetUrl;
            if (posterUrl) video.poster = posterUrl;
            video.muted = true;
            video.preload = 'metadata';
            preview.appendChild(video);
            return;
        }

        const empty = document.createElement('div');
        empty.className = kind === 'iframe' ? 'artwork-slide-preview-embed' : 'artwork-slide-preview-empty';
        empty.textContent = kind === 'iframe' ? 'Iframe embed slide' : `No ${kind} selected yet`;
        preview.appendChild(empty);
    }

    function setActiveSlide(card) {
        list.querySelectorAll('[data-slide-item]').forEach(c => c.classList.add('is-collapsed'));
        card.classList.remove('is-collapsed');
    }

    function renumber() {
        [...list.querySelectorAll('[data-slide-item]')].forEach((card, index) => {
            card.querySelectorAll('input[name], textarea[name]').forEach(field => {
                if (!field.name) return;
                field.name = field.name.replace(/\[\d+\]/, `[${index}]`).replace(/\[__INDEX__\]/, `[${index}]`);
            });
        });
    }

    function bindCard(card) {
        const kind = card.dataset.kind;
        const removeBtn = card.querySelector('[data-remove-slide]');
        const assetBtn = card.querySelector('[data-slide-pick-asset]');
        const posterBtn = card.querySelector('[data-slide-pick-poster]');
        const assetUrlInput = card.querySelector('[data-slide-asset-url]');
        const posterUrlInput = card.querySelector('[data-slide-poster-url]');
        const mediaIdField = card.querySelector('[data-field="media_file_id"]');
        const posterIdField = card.querySelector('[data-field="poster_media_file_id"]');

        card.draggable = true;

        card.querySelector('[data-edit-slide]')?.addEventListener('click', () => setActiveSlide(card));

        removeBtn?.addEventListener('click', () => {
            card.remove();
            renumber();
        });

        assetBtn?.addEventListener('click', () => {
            if (!window.openMediaPicker) return;
            window.openMediaPicker(result => {
                if (!result?.id) return;
                mediaIdField.value = result.id;
                assetUrlInput.value = assetUrlFor(kind, result);
                hydratePreview(card, kind, assetUrlInput.value, posterUrlInput?.value || '');
            }, 'select', { mode: assetBtn.dataset.pickerMode || kind });
        });

        posterBtn?.addEventListener('click', () => {
            if (!window.openMediaPicker) return;
            window.openMediaPicker(result => {
                if (!result?.id) return;
                posterIdField.value = result.id;
                posterUrlInput.value = result.legacy_url || result.url || '';
                hydratePreview(card, 'video', assetUrlInput?.value || '', posterUrlInput.value);
            }, 'select', { mode: 'image' });
        });
    }

    let dragging = null;
    list.addEventListener('dragstart', event => {
        const card = event.target.closest('[data-slide-item]');
        if (!card) return;
        dragging = card;
        card.classList.add('drag-active');
    });

    list.addEventListener('dragend', () => {
        if (!dragging) return;
        dragging.classList.remove('drag-active');
        dragging = null;
        renumber();
    });

    list.addEventListener('dragover', event => {
        event.preventDefault();
        const over = event.target.closest('[data-slide-item]');
        if (!dragging || !over || over === dragging) return;
        const rect = over.getBoundingClientRect();
        const after = event.clientY > rect.top + rect.height / 2;
        list.insertBefore(dragging, after ? over.nextSibling : over);
    });

    function addSlide(kind) {
        const template = templates[kind];
        if (!template) return;
        const index = list.querySelectorAll('[data-slide-item]').length;
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        const wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        const card = wrap.firstElementChild;
        list.appendChild(card);
        bindCard(card);
        renumber();
        setActiveSlide(card);

        const assetBtn = card.querySelector('[data-slide-pick-asset]');
        if (assetBtn && kind !== 'iframe') {
            assetBtn.click();
        }
    }

    builder.querySelectorAll('[data-add-slide]').forEach(btn => {
        btn.addEventListener('click', () => addSlide(btn.dataset.addSlide));
    });

    list.querySelectorAll('[data-slide-item]').forEach(bindCard);
    renumber();
    const firstSlide = list.querySelector('[data-slide-item]');
    if (firstSlide) setActiveSlide(firstSlide);
})();

// Public work page: lazy-loaded artwork carousel
(function () {
    const carousel = document.querySelector('[data-artwork-carousel]');
    if (!carousel) return;

    const slides = [...carousel.querySelectorAll('[data-carousel-slide]')];
    const prevBtn = carousel.querySelector('[data-carousel-prev]');
    const nextBtn = carousel.querySelector('[data-carousel-next]');
    const dots = [...carousel.querySelectorAll('[data-carousel-dot]')];
    const titleEl = carousel.querySelector('[data-carousel-title]');
    const captionEl = carousel.querySelector('[data-carousel-caption]');
    let activeIndex = Math.max(0, slides.findIndex(slide => slide.classList.contains('is-active')));
    if (activeIndex < 0) activeIndex = 0;

    function teardownSlide(slide) {
        const kind = slide.dataset.kind;
        if (kind === 'iframe') {
            slide.innerHTML = '<div class="work-slide-placeholder"><span>IFRAME loads when activated</span></div>';
            return;
        }

        const video = slide.querySelector('video');
        if (video) {
            video.pause();
        }
    }

    function ensureSlideContent(slide) {
        const kind = slide.dataset.kind;
        if (slide.dataset.loaded === 'true' && kind !== 'iframe') return;

        if (kind === 'image') {
            const img = document.createElement('img');
            img.className = 'work-image';
            img.src = slide.dataset.source;
            img.alt = slide.dataset.alt || '';
            img.decoding = 'async';
            slide.innerHTML = '';
            slide.appendChild(img);
            slide.dataset.loaded = 'true';
            return;
        }

        if (kind === 'video') {
            const video = document.createElement('video');
            video.className = 'work-video';
            video.controls = true;
            video.preload = 'metadata';
            video.src = slide.dataset.source;
            if (slide.dataset.poster) video.poster = slide.dataset.poster;
            slide.innerHTML = '';
            slide.appendChild(video);
            slide.dataset.loaded = 'true';
            return;
        }

        if (kind === 'iframe') {
            const wrap = document.createElement('div');
            wrap.className = 'work-embed';
            wrap.innerHTML = slide.dataset.iframeHtml || '';
            slide.innerHTML = '';
            slide.appendChild(wrap);
        }
    }

    function syncUi() {
        slides.forEach((slide, index) => {
            const isActive = index === activeIndex;
            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            if (isActive) ensureSlideContent(slide);
            else teardownSlide(slide);
        });

        dots.forEach((dot, index) => {
            const isActive = index === activeIndex;
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        if (prevBtn) prevBtn.disabled = activeIndex === 0;
        if (nextBtn) nextBtn.disabled = activeIndex === slides.length - 1;

        if (titleEl) {
            titleEl.textContent = slides[activeIndex]?.dataset.title || '';
        }

        if (captionEl) {
            captionEl.textContent = slides[activeIndex]?.dataset.caption || '';
        }
    }

    function goTo(index) {
        if (index < 0 || index >= slides.length || index === activeIndex) return;
        activeIndex = index;
        syncUi();
    }

    prevBtn?.addEventListener('click', () => goTo(activeIndex - 1));
    nextBtn?.addEventListener('click', () => goTo(activeIndex + 1));
    dots.forEach(dot => dot.addEventListener('click', () => goTo(Number(dot.dataset.index || 0))));

    // iOS Safari: synthesised click events can be dropped on absolutely-positioned controls
    prevBtn?.addEventListener('touchend', (e) => {
        e.preventDefault();
        prevBtn.click();
    }, { passive: false });

    nextBtn?.addEventListener('touchend', (e) => {
        e.preventDefault();
        nextBtn.click();
    }, { passive: false });

    dots.forEach(dot => {
        dot.addEventListener('touchend', (e) => {
            e.preventDefault();
            dot.click();
        }, { passive: false });
    });

    carousel.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            goTo(activeIndex - 1);
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            goTo(activeIndex + 1);
        }
    });

    syncUi();
})();
