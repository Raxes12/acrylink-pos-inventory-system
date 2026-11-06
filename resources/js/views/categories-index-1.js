import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
    const search = document.querySelector('[data-category-search]');
    const clear = document.querySelector('[data-category-search-clear]');
    const cards = [...document.querySelectorAll('[data-category-card]')];
    const empty = document.querySelector('[data-category-search-empty]');
    const count = document.querySelector('[data-category-visible-count]');

    if (search && cards.length > 0) {
        const filterCategories = () => {
            const term = search.value.trim().toLocaleLowerCase();
            let visible = 0;

            cards.forEach(card => {
                const matches = !term || (card.dataset.categoryName || '').includes(term);
                card.hidden = !matches;
                if (matches) visible++;
            });

            if (count) count.textContent = String(visible);
            if (empty) empty.hidden = visible !== 0;
            if (clear) clear.hidden = term === '';
        };

        scope.listen(search, 'input', filterCategories);
        scope.listen(search, 'keydown', event => {
            if (event.key !== 'Escape' || search.value === '') return;
            search.value = '';
            filterCategories();
        });
        if (clear) scope.listen(clear, 'click', () => {
            search.value = '';
            filterCategories();
            search.focus();
        });

        filterCategories();
    }

    const nameInput = document.querySelector('[data-category-name-input]');
    const namePreview = document.querySelector('[data-category-name-preview]');
    const initialsPreview = document.querySelector('[data-category-name-initials]');
    const nameCount = document.querySelector('[data-category-name-count]');
    if (nameInput) {
        const updatePreview = () => {
            const value = nameInput.value.trim();
            const words = value.split(/\s+/).filter(Boolean);
            if (namePreview) namePreview.textContent = value || 'Category name';
            if (initialsPreview) initialsPreview.textContent = words.length
                ? words.slice(0, 2).map(word => word[0]).join('').toLocaleUpperCase()
                : 'CA';
            if (nameCount) nameCount.textContent = `${nameInput.value.length}/50`;
        };
        scope.listen(nameInput, 'input', updatePreview);
        updatePreview();
    }
});
