(function($) {

    $(document).ready(function() {
        $(".widget_backtotop").each(function() {
            $(this).click(function() {
                var duration = $(this).data('duration');
                var easing = $(this).data('easing');
                $('html, body').animate({
                    scrollTop: 0
                }, duration, easing);
            });
            if ($(this).css('position') == 'fixed') {
                $(this).hide();
            }
        });
        $(document).scroll(function() {
            $(".widget_backtotop").each(function() {
                var documentHeight = $(document).height();
                var showFrom = parseInt($(this).data('from'));
                if ($(this).css('position') == 'fixed') {
                	console.log($(document).scrollTop());
                    if ( ($(document).scrollTop() + window.innerHeight/2) / documentHeight * 100 > showFrom) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                }
            });
        });
    });

})(jQuery);