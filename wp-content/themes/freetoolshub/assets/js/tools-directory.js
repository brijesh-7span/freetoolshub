/**
 * FreeToolsHub — Tools directory
 *
 * Upgrades the server-rendered directory (template-parts/tools-directory.php)
 * to instant search + category filtering, keeping the URL in sync so
 * filtered views can be shared and survive a reload.
 */
(function () {
    'use strict';

    var i18n = window.fthDirectory || {
        all: ['Showing %d tool', 'Showing all %d tools'],
        some: ['Showing %1$d of %2$d tool', 'Showing %1$d of %2$d tools'],
        noMatchQuery: 'No tools match “%s”. Try a different word or browse all categories.',
        noMatchCategory: 'No tools match your search. Try a different word or browse all categories.'
    };

    /**
     * Lowercase, strip accents, collapse spaces.
     * Mirrors fth_normalize_search_text() in inc/template-functions.php.
     */
    function normalize(text) {
        return text
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();
    }

    function plural(forms, count) {
        return forms[count === 1 ? 0 : 1];
    }

    function init(root) {
        var form = root.querySelector('[data-directory-form]');
        var search = root.querySelector('[data-directory-search]');
        var categoryInput = root.querySelector('[data-directory-category-input]');
        var filters = root.querySelectorAll('[data-directory-filter]');
        var items = root.querySelectorAll('[data-directory-item]');
        var status = root.querySelector('[data-directory-status]');
        var empty = root.querySelector('[data-directory-empty]');
        var emptyText = root.querySelector('[data-directory-empty-text]');
        var resetLink = root.querySelector('[data-directory-reset]');
        var heading = document.querySelector('[data-directory-heading]');
        var intro = document.querySelector('[data-directory-intro]');
        var baseUrl = root.getAttribute('data-base-url');

        var initialCategory = categoryInput ? categoryInput.value : '';
        var initialIntro = intro ? intro.textContent.trim() : '';
        var category = initialCategory;

        if (!items.length) {
            return;
        }

        root.classList.add('is-enhanced');

        function apply() {
            var words = normalize(search.value).split(' ').filter(Boolean);
            var visible = 0;

            items.forEach(function (item) {
                var categories = item.getAttribute('data-categories').split(' ');
                var text = item.getAttribute('data-search');
                var matches = (!category || categories.indexOf(category) !== -1) &&
                    words.every(function (word) {
                        return text.indexOf(word) !== -1;
                    });

                item.hidden = !matches;

                if (matches) {
                    visible++;
                }
            });

            var activeLabel = '';

            filters.forEach(function (filter) {
                var isActive = filter.getAttribute('data-directory-filter') === category;

                filter.classList.toggle('is-active', isActive);

                if (isActive) {
                    filter.setAttribute('aria-current', 'true');
                    activeLabel = filter.getAttribute('data-label');
                } else {
                    filter.removeAttribute('aria-current');
                }
            });

            if (categoryInput) {
                categoryInput.value = category;
            }

            // Results summary
            status.textContent = visible === items.length
                ? plural(i18n.all, items.length).replace('%d', items.length)
                : plural(i18n.some, items.length).replace('%1$d', visible).replace('%2$d', items.length);

            // Empty state
            empty.hidden = visible > 0;

            if (!visible) {
                emptyText.textContent = search.value.trim()
                    ? i18n.noMatchQuery.replace('%s', search.value.trim())
                    : i18n.noMatchCategory;
            }

            // Hero heading/intro follow the selected category.
            if (heading && activeLabel) {
                heading.textContent = activeLabel;
            }

            if (intro) {
                intro.textContent = category === initialCategory
                    ? initialIntro
                    : intro.getAttribute('data-default-text');
            }
        }

        function updateUrl() {
            var params = new URLSearchParams();
            var query = search.value.trim();

            if (query) {
                params.set('q', query);
            }

            if (category) {
                params.set('category', category);
            }

            var url = baseUrl + (params.toString() ? '?' + params.toString() : '');

            if (url !== window.location.href) {
                history.replaceState(null, '', url);
            }
        }

        search.addEventListener('input', function () {
            apply();
            updateUrl();
        });

        // Results already update while typing; just keep focus in the box.
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            updateUrl();
        });

        filters.forEach(function (filter) {
            filter.addEventListener('click', function (e) {
                e.preventDefault();
                category = filter.getAttribute('data-directory-filter');
                apply();
                updateUrl();
            });
        });

        if (resetLink) {
            resetLink.addEventListener('click', function (e) {
                e.preventDefault();
                search.value = '';
                category = '';
                apply();
                updateUrl();
                search.focus();
            });
        }

        // Browsers may restore the search box value on back/forward navigation.
        apply();
    }

    function initAll() {
        document.querySelectorAll('[data-tools-directory]').forEach(init);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
