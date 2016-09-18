jQuery(document).ready(function() {

    jQuery("a[rel=@instance@]").each(function() {
        var self = jQuery(this);

        self.find('img').each(function(index, el) {
            jQuery(el).closest('a').attr('title',jQuery(el).attr('alt'));
        });

        jQuery(this).extendLightbox({
            'padding': 0,
            'margin': 0,
            'type': 'image',
            'showNumbers': true,
            'showCloseButton': true,
            'overlayOpacity': 0.8,
            'overlayColor': '#000000',
            'showArrows': true,
            'showNavArrows': true,
            'titleShow': false,
            'transitionIn': 'none',
            'transitionOut': 'fade',
            'titlePosition': 'outside',
            'autoDimensions': false,
            'width': '',
            'height': '',
            'onComplete': function() {

            },
            'onCleanup': function() {
                jQuery('#outside-controls').remove();
            },
            'onStart': function() {

            },
            'onClosed': function() {
                jQuery('body').removeClass('body-fix');
            },

            'titleFormat': function(title, currentArray, currentIndex, currentOpts) {
                var showControls = function() {
                    var lastItem = currentArray.length - 1;
                    if (currentOpts.showNumbers) return '<div class="image-number">' + (currentIndex + 1) + ' / ' + currentArray.length + '</div>';
                    else return;
                };

                if (currentArray.length - 1) return '<div class="image-title">' + (title.length ? title : '') + '</div>' + showControls();
                else return '<div class="image-title">' + (title.length ? title : '') + '</div>';

            }

        });
    });
});