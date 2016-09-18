jQuery(document).ready(function() {
    var fixScroll = function() {
        var body = jQuery('body');
        var fixer = 'xtd-body-fix';
        if (!body.hasClass(fixer)) {
            body.addClass(fixer);
        }
    };



    var addShade = function() {
            var shade = jQuery('#shade-inner');
            if (shade.length > 0) {
                shade.remove;
            } else {
                jQuery('#fancybox-title').append('<div id="shade" style="height:68px; width:' + jQuery('#fancybox-wrap').width() + 'px;"></div>');
                jQuery('#shade').wrap('<div id="shade-inner"></div>');
            }
        }
        // @instanceName
    jQuery("a[rel=@instanceName]").each(function() {
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
            'overlayOpacity': 0.5,
            'overlayColor': '#000000',
            'showArrows': true,
            'showNavArrows': true,
            'titleShow': false,
            'transitionIn': 'none',
            'transitionOut': 'none',
            'titlePosition': 'none',
            'autoDimensions': true,
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
                jQuery('body').removeClass('xtd-body-fix');
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