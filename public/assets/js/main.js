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
