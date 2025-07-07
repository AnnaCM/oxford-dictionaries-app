$(document).ready(function() {
    $('.js-homepage').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            method: 'GET',
            url: '/',
            success: function(response) {
                $('body').html(response);
            }
        })
    });
});
