$('button').on("click", function() {
    $(this).toggleClass('is-closed');
    if(!$(this).hasClass('is-closed')) {
        $('body').addClass('is-open');
    } else {
        $('body').removeClass('is-open');
    }
});