$(document).ready(function() {
    $('.js-search-definitions').on('click', function(e) {
        e.preventDefault();

        var input = $('#search_word_definition').val();

        var selectedSourceLang = $('#select-definition-language option:selected').val();

        if (input === "") {
            alert ("A word to be searched is required");
            return;
        }

        var definitionUrl = "/definitions";
        if (input !== undefined && selectedSourceLang !== undefined) {
            definitionUrl += "/" + selectedSourceLang + '/' + input;
        }

        $.ajax({
            method: 'GET',
            url: definitionUrl,
            success: function(response) {
                $('body').html(response);
            },
            error: function(xhr) {
                $('body').html(xhr.responseText);
            }
        })
    });
});
