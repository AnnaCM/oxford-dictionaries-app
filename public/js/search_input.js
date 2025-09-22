export function mountSearch({ rootSelector = '.search-bar' }) {
    const $root = $(rootSelector);
    
    // prevent multiple mounts on the same container
    if ($root.data('search-bound')) return;
    $root.data('search-bound', true);

    let currentIndex = -1;

    const debounce = (fn, delay) => {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    };

    // INPUT (delegated to root)
    $root.on('input.search', '#search_word', debounce(function () {
        const query = $(this).val().trim();
        currentIndex = -1;

        const $suggestions = $root.find('#suggestions').empty();
        if (query.length < 2) return;

        $.getJSON('/autocomplete', { q: query })
        .done(words => {
            words.forEach(word => {
                $('<li>').text(word).on('click', function () {
                    $root.find('#search_word').val(word);
                    $suggestions.empty();
                }).appendTo($suggestions);
            });
        })
        .fail((_, __, err) => console.error('Error fetching suggestions:', err));
    }, 300));

    // KEYBOARD NAV
    $root.on('keydown.search', '#search_word', function (e) {
        const $items = $root.find('#suggestions li');
        if ($items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentIndex = (currentIndex + 1) % $items.length;
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentIndex = (currentIndex - 1 + $items.length) % $items.length;
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentIndex >= 0) {
                $root.find('#search_word').val($items.eq(currentIndex).text());
                $root.find('#suggestions').empty();
            }
            return;
        } else if (e.key === 'Escape') {
            e.preventDefault();
            $root.find('#suggestions').empty();
            currentIndex = -1;
            return;
        }

        $items.removeClass('active');
        if (currentIndex >= 0) $items.eq(currentIndex).addClass('active');
    });

    // BLUR
    $root.on('blur.search', '#search_word', function () {
        setTimeout(() => {
            $root.find('#suggestions').empty();
            currentIndex = -1;
        }, 150);
    });

    // MODE BUTTONS (Definitions / Translations)
    $root.on('click.search', '.js-search-languages', function (e) {
        e.preventDefault();
        const mode = $(this).data('mode'); // "definitions" | "translations"
        const $langSelect = $root.find('#search_language');
        const selected = $langSelect.val() || '';

        const needsRepopulate =
        (mode === 'translations' && !selected.includes(' ')) ||
        (mode === 'definitions'  &&  selected.includes(' '));

        if (!needsRepopulate) return;

        $.getJSON(`/get-languages/${mode}`, (data) => {
        $langSelect.empty();

        if (mode === 'translations') {
            Object.keys(data.sourceLangs).forEach(sourceKey => {
                $langSelect.append(`<option disabled>--------${data.sourceLangs[sourceKey]}--------</option>`);
                Object.keys(data.targetLangs).forEach(targetKey => {
                    if (sourceKey !== targetKey) {
                        $langSelect.append(
                            `<option value="${sourceKey} ${targetKey}" ${sourceKey == data.selectedSourceLang && targetKey == data.selectedTargetLang ? 'selected' : ''}>
                            ${data.sourceLangs[sourceKey]}-${data.targetLangs[targetKey]}
                            </option>`
                        );
                    }
                });
            });
        } else {
            Object.keys(data.sourceLangs).forEach(sourceKey => {
                $langSelect.append(
                    `<option value="${sourceKey}" ${sourceKey == data.selectedSourceLang ? 'selected' : ''}>
                    ${data.sourceLangs[sourceKey]}
                    </option>`
                );
            });
        }
        });
    });

    // SEARCH ACTION
    $root.on('click.search', '.js-search-word', function (e) {
        e.preventDefault();

        const input = $root.find('#search_word').val();
        const selectedLang = $root.find('#search_language').val() || '';

        if (!input) return alert('A word to be searched is required');
        if (input.includes(' ')) return alert('Please type a single word');

        var url = "/";
        if (selectedLang.includes(' ')) {
            const [sourceLang, targetLang] = selectedLang.split(" ");
            url += `translations/${sourceLang}/${targetLang}/${input}`;
        } else {
            url += `definitions/${selectedLang}/${input}`;
        }

        $root.find('#search_word').val('');

        $.ajax({
            method: 'GET',
            url: url,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: (html) => $('#results-container').html(html),
            error: (xhr) => $('#results-container').html(xhr.responseText)
        });
    });

    // once-only style injection (guard by id)
    if (!document.getElementById('search-suggestion-style')) {
        $('<style id="search-suggestion-style">')
        .text(`#suggestions li.active, #suggestions li:hover { background:#f0f0f0; color:#000; }`)
        .appendTo('head');
    }
}

export function unmountSearch({ rootSelector = '.search-bar' } = {}) {
    const $root = $(rootSelector);
    $root.off('.search');
    $root.removeData('search-bound');
}
