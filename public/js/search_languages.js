import { mountSearch, unmountSearch } from './search_input.js';

$(document).ready(function () {
    // Ensure we clear any old handlers before mounting new ones
    unmountSearch();

    mountSearch({ rootSelector: '.search-bar' });
});
