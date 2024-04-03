document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('.a11y-overlay-search__input');
    searchInput.addEventListener('input', debounce(search));

    let isFetching = false; // Track if a fetch request is in progress

    function search() {
        if (isFetching) {
            // If a fetch request is in progress, do nothing
            return;
        }

        isFetching = true; // Set fetching flag to true

        const searchValue = searchInput.value.trim();
        const theme = theme_name.get_themename;
        const lang = document.documentElement.lang.substr(0, 2);
        const noResultsText = translations.no_results_found;
        const displayedResults = new Set();

        const resultsContainer = document.querySelector(
            '.a11y-overlay-search_results'
        );
        resultsContainer.innerHTML = '';

        if (!searchValue) {
            isFetching = false;
            return;
        }

        fetch(`/wp-json/${theme}/v2/search/?s=${searchValue}&lang=${lang}`)
            .then((response) => response.json())
            .then((results) => {
                if (results.length === 0) {
                    resultsContainer.innerHTML = `<li class="a11y-result no-results"><p>${noResultsText}</p></li>`;
                } else {
                    results.forEach((result) => {
                        // Check if the result is already displayed
                        if (!displayedResults.has(result.id)) {
                            resultsContainer.innerHTML += `
                                <li class="a11y-result" role="option">
                                    <a href="${result.permalink}" tabindex="-1">
                                        <span class="title">${result.title}</span>
                                        <span class="post-type-badge">${result.post_type}</span>
                                    </a>
                                </li>
                            `;
                            displayedResults.add(result.id);
                        }
                    });
                }
            })
            .catch((error) =>
                console.error('Error fetching search results:', error)
            )
            .finally(() => {
                isFetching = false;
            });
    }

    function debounce(func, delay = 250) {
        let timeout;
        return function () {
            clearTimeout(timeout);
            timeout = setTimeout(func, delay);
        };
    }

    const searchDialog = document.querySelector('.a11y-overlay-search__dialog');
    const openSearchBtn = document.querySelector('.a11y-overlay-search__open');
    const closeSearchBtn = document.querySelector(
        '.a11y-overlay-search__close'
    );
    openSearchBtn.addEventListener('click', toggleSearch);
    closeSearchBtn.addEventListener('click', toggleSearch);

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            searchDialog.classList.contains('a11y-overlay-search__dialog--show')
        ) {
            toggleSearch();
        }
    });

    function toggleSearch() {
        const body = document.body;
        const ariaHiddenEls = document.querySelectorAll(
            'div, footer, header, a, button, form, input, select'
        );
        const overlayVisibleClass = 'a11y-overlay-search__dialog-visible';

        searchDialog.classList.toggle('a11y-overlay-search__dialog--show');
        body.classList.toggle(overlayVisibleClass);

        if (
            searchDialog.classList.contains('a11y-overlay-search__dialog--show')
        ) {
            ariaHiddenEls.forEach((el) =>
                el.setAttribute('aria-hidden', 'true')
            );
            searchInput.focus();
        } else {
            ariaHiddenEls.forEach((el) => el.removeAttribute('aria-hidden'));
            openSearchBtn.focus();
        }
    }

    // Focus trap for dialog
    window.addEventListener('keydown', handleKey);

    function handleKey(e) {
        if (e.key === 'Tab') {
            const dialog = document.querySelector(
                '.a11y-overlay-search__dialog'
            );
            const focusable = Array.from(
                dialog.querySelectorAll('input, button')
            );
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            const shift = e.shiftKey;

            if (shift ? e.target === first : e.target === last) {
                // Shift-tab pressed on first input or tab pressed on last input
                e.preventDefault();
                (shift ? last : first).focus();
            }
        }
    }

    // Search results focus trap
    document.addEventListener('keydown', searchResultsFocusTrap);

    function searchResultsFocusTrap(e) {
        const focusableLinks = document.querySelectorAll(
            '.a11y-overlay-search_results a'
        );
        const focusable = Array.from(focusableLinks);
        const index = focusable.indexOf(document.activeElement);
        let nextIndex = 0;

        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
            nextIndex = index + (e.key === 'ArrowUp' ? -1 : 1);
            focusable[nextIndex] && focusable[nextIndex].focus();
        }

        const submitButton = document.querySelector(
            '.a11y-overlay-search__submit'
        );

        if (e.key === 'Tab' && focusable.includes(document.activeElement)) {
            e.preventDefault();
            submitButton.focus();
        }
    }
});
