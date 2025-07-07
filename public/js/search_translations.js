$(document).ready(function() {
    $('.js-search-translations').on('click', function(e) {
        e.preventDefault();

        var input = $('#search_translations_word').val();

        var selectedLangs = $('#select-translations-languages option:selected').val();

        if (input === "") {
            alert ("A word to be searched is required");
            return;
        }

        var translationUrl = "/translations";
        if (input !== undefined && selectedLangs !== undefined) {
            translationUrl += "/" + selectedLangs.replace(" ", "/") + '/' + input;
        }

        $.ajax({
            method: 'GET',
            url: translationUrl,
            success: function(response) {
                $('body').html(response);
            },
            error: function(xhr) {
                $('body').html(xhr.responseText);
            }
        })
    });
});
