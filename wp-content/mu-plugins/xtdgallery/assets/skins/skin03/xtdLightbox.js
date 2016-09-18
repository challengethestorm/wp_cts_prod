jQuery(document).ready(function() {




    var addShade = function() {
        var shade = jQuery('#shade-inner');
        if (shade.length > 0) {
            shade.remove;
        } else {
            jQuery('#fancybox-wrap').append('<div id="shade" style="height:68px; width:' + jQuery('#fancybox-wrap').width() + 'px;"></div>');
            jQuery('#shade').wrap('<div id="shade-inner"></div>');
        }
    }

    jQuery("a[rel=@instanceName]").each(function() {
        var self = jQuery(this);

        self.find('img').each(function(index, el) {
            jQuery(el).closest('a').attr('title',jQuery(el).attr('alt'));
        });

        jQuery(this).extendLightbox({
            'padding': 0,
            'margin': 10,
            'type': 'image',
            'showCaption': true,
            'showNumbers': true,
            'showArrows': true,
            'showCloseButton': true,
            'overlayOpacity': 0.8,
            'overlayColor': '#000000',
            'showNavArrows': false,
            'titleShow': true,
            'transitionIn': 'none',
            'transitionOut': 'none',
            'titlePosition': 'outside',
            'autoDimensions': false,
            'width': '',
            'height': '',
            'onComplete': function() {
                var browser = window.flexiCssMenus.browser;
                if (!(browser.name == 'msie' && browser.version < 9)) {
                    addShade();
                }
            },
            'onCleanup': function() {
                jQuery('#outside-controls').remove();
                jQuery('#shade-inner').remove()
            },
            'onStart': function() {




            },
            'onClosed': function() {
                jQuery('body').removeClass('xtd-body-fix');
            },
            'titleFormat': function(title, currentArray, currentIndex, currentOpts) {
                var clearfix = '<div class="clearfix"></div> ';
                var showControls = function() {
                    var lastItem = currentArray.length - 1;
                    var nextControl = '<div onclick="jQuery.xtd_fancybox.next();" class="lightbox-control-next"></div>';
                    var prevControl = '<div onclick="jQuery.xtd_fancybox.prev();" class="lightbox-control-prev"></div>';
                    var prevControlLast = '<div onclick="jQuery.xtd_fancybox.prev();" class="lightbox-control-prev prev-last"></div>';
                    var ligthboxControls = '<div class="lightbox-controls-inner">' + '<div class="image-number">' + (currentIndex + 1) + ' / ' + currentArray.length + '</div>' + '<div class="lightbox-controls">';
                    var ligthboxControlsLast = '<div class="lightbox-controls-inner">' + '<div class="image-number number-last">' + (currentIndex + 1) + ' / ' + currentArray.length + '</div>' + '<div class="lightbox-controls">';
                    var onlyOneImage = '<div class="lightbox-controls-inner"></div>';

                    switch (currentIndex) {
                        case 0:

                            if (lastItem == 0) return onlyOneImage;

                            // show image number and prev arrow
                            if (currentOpts.showNumbers && currentOpts.showArrows) return ligthboxControls + nextControl + '</div></div>';
                            // show image number
                            else if (currentOpts.showNumbers) return ligthboxControls + '</div></div>';
                            // show arrows
                            else if (currentOpts.showArrows) return '<div class="lightbox-controls-inner">' + nextControl + '</div>';
                            // show nothing
                            else return;
                            break;
                        case lastItem:
                            // show image number and next arrow
                            if (currentOpts.showNumbers && currentOpts.showArrows) return ligthboxControlsLast + prevControlLast + '</div></div>';
                            // show image number
                            else if (currentOpts.showNumbers) return ligthboxControls + '</div></div>';
                            // show arrows
                            else if (currentOpts.showArrows) return '<div class="lightbox-controls-inner">' + prevControl + '</div>';
                            // show nothing
                            else return;
                            break;
                        default:
                            // show image number and arrows
                            if (currentOpts.showNumbers && currentOpts.showArrows) return ligthboxControls + prevControl + nextControl + '</div></div>';
                            // show image number
                            else if (currentOpts.showNumbers) return ligthboxControls + '</div></div>';
                            // show arrows
                            else if (currentOpts.showArrows) return '<div class="lightbox-controls-inner">' + nextControl + prevControl + '</div>';
                            // show nothing
                            else return;
                            break
                    }
                };

                var fixTitlePosition = function() {
                    var marginFix = ('<div class="margin-fix"></div> ');
                    var lastItem = currentArray.length - 1;
                    switch (currentIndex) {
                        case 0:
                            return '';
                            break;
                        default:
                            return marginFix;
                            break
                    }
                };
                // if type is page or iframe, don't show container
                if (currentOpts.type && currentOpts.type != "image") return clearfix;

                // if no arrows , no image number , no caption , don't show container
                if (!currentOpts.showArrows && !currentOpts.showNumbers && !currentOpts.showCaption) return clearfix;

                // if 1 image and no caption don't show container
                if ((!currentOpts.showCaption || self.attr('title') == '') && currentArray.length - 1 == 0) return clearfix;

                var style = 'style="';

                if (currentIndex == 0) {
                    style += "margin-left:0px;";
                }

                if (currentArray.length == 1) {
                    style += "margin-right:0px;";
                }

                if (style != 'style="') {
                    style += '"';
                } else {
                    style = "";
                }

                if (currentOpts.showCaption)
                    return '<div id="fancybox-title-over">' + fixTitlePosition() + '<div class="image-title"' + style + '>' + (title.length ? title : '') + '</div>' + showControls() + '</div>' + clearfix;
                else
                    return '<div id="fancybox-title-over">' + fixTitlePosition() + showControls() + '</div>' + clearfix;

            }
        });
    });
});