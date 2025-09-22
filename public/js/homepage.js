import { unmountSearch } from './search_input.js';

$(document).ready(function () {
    $('.js-homepage').on('click', function (e) {
        e.preventDefault();

        // Unbind any search handlers before replacing DOM
        unmountSearch();

        $.ajax({
            method: 'GET',
            url: '/',
            success: function (response) {
                // Replace body content
                $('body').html(response);
            },
            error: function (xhr) {
                $('body').html(xhr.responseText);
            }
        });
    });
});
