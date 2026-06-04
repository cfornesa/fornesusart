// Drag-and-drop row reordering for admin tables
document.querySelectorAll('tbody[data-reorder-url]').forEach(tbody => {
    const url = tbody.dataset.reorderUrl;
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

            const status = document.getElementById('reorder-status');
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'ids=' + encodeURIComponent(ids),
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
            el.style.display = '';
        });
        btn.remove();
    });
})();

// Generic slug auto-fill: any input[id$="-name"] → sibling input[id$="-slug"]
['cat', 'exhibit'].forEach(prefix => {
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

// Work description: read more / read less toggle
document.querySelectorAll('.desc-read-more').forEach(btn => {
    btn.addEventListener('click', () => {
        const wrap = btn.closest('.work-description');
        wrap.querySelector('.desc-short').hidden = true;
        wrap.querySelector('.desc-full').hidden  = false;
    });
});
document.querySelectorAll('.desc-read-less').forEach(btn => {
    btn.addEventListener('click', () => {
        const wrap = btn.closest('.work-description');
        wrap.querySelector('.desc-short').hidden = false;
        wrap.querySelector('.desc-full').hidden  = true;
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
