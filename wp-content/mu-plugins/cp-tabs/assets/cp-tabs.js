jQuery(document).ready(function($) {
    // return true;
    function makeGallery($tabsElement) {

        var options = $.extend({
            "speed": 300,
            "desktopitems": 5,
            "tabletitems": 2,
            "mobileitems": 2,
            'navarrows': false,
            'maxheight': 'auto'
        }, $tabsElement.data());
        $tabsElement.css('overflow', 'hidden');
        $tabsElement.css('max-height', $tabsElement.height() + "px");
        var $tabContentContainer = $tabsElement.find('.tab-content'),
            $tabNavContainer = $tabsElement.find('.tab-nav'),
            $navItems = $tabsElement.find('.tab-nav-item'),
            owlContentInstance,
            owlNavInstance;

        $tabContentContainer.find('.tab-pane').css('display', 'block');
        $navItems.show();
        $tabNavContainer.find('.navitems-nav').remove();
        $navItems.each(function(index, el) {
            if (index !== 0) {
                $(this).removeClass('selected');
            } else {
                $(this).addClass('selected');
            }
            $(el).data('el-index', index);
        });

        if (options.maxheight !== 'auto') {
            $tabContentContainer.css({
                'height': options.maxheight,
                'max-height': options.maxheight,
                'overflow': 'auto',
            })
        }

        $tabContentContainer.owlCarousel({
            slideSpeed: options.speed,
            paginationSpeed: 0,
            singleItem: true,
            navigation: false,
            pagination: false,
            autoHeight: true,
            mouseDrag: false,
            touchDrag: false,
            responsiveBaseWidth: $tabsElement,
            transitionStyle: "fade",
            afterAction: function() {
                if (owlContentInstance.currentItem < owlNavInstance.currentItem || owlNavInstance.currentItem + owlNavInstance.options.items - 1 < owlContentInstance.currentItem) {
                    owlNavInstance.goTo(owlContentInstance.currentItem);
                }
            }
        });
        owlContentInstance = $tabContentContainer.data().owlCarousel;
        $tabContentContainer.find('.owl-wrapper-outer').css('overflow', 'hidden');

        $navItems.click(function(event) {
            var index = $(this).data('el-index');
            owlContentInstance.$owlItems.eq(owlContentInstance.currentItem).trigger("animationend");
            if (owlNavInstance.options.items > 1) {

                if (owlContentInstance.currentItem < index) {
                    owlNavInstance.goTo(Math.min(index + 1, $navItems.length - 1));
                } else if (owlContentInstance.currentItem > index) {
                    owlNavInstance.goTo(Math.max(index - 1, 0));
                }
            } else {
                owlNavInstance.goTo(index);
            }

            owlContentInstance.goTo(index);
            var owlItem = $(this).parent();
            $(this).addClass('selected');
            $navItems.not($(this)).removeClass('selected');
        });


        $tabNavContainer.css('width', '100%');
        $tabNavContainer.owlCarousel({
            items: options.desktopitems,
            itemsTablet: [1024, options.tabletitems],
            itemsMobile: [768, options.mobileitems],
            slideSpeed: 300,
            paginationSpeed: 400,
            responsive: true,
            navigation: false,
            pagination: false,
            itemsScaleUp: true,
            navigationText: false,
            afterInit: onWindowResize,
            afterAction: function() {
                if (owlNavInstance.options.items == 1) {
                    $navItems.eq(owlNavInstance.currentItem).trigger('click');
                }
            }
        });



        if (options.navarrows) {
            $prevnavItems = $('<div class="navitems-nav prev-navitems"></div>').click(function() {
                var index = Math.max(owlNavInstance.currentItem - 1, 0);
                $navItems.eq(index).trigger('click');
            }).hide();
            $nextnavItems = $('<div class="navitems-nav next-navitems"></div>').click(function() {
                var index = Math.min(owlContentInstance.currentItem + 1, $navItems.length);
                $navItems.eq(index).trigger('click');
            }).hide();
            $tabNavContainer.append($prevnavItems).append($nextnavItems);
        }


        owlNavInstance = $tabNavContainer.data().owlCarousel;

        function toggleNavControlsVisibility() {
            var width = 0;
            var base = owlNavInstance;
            var $items = base.$owlItems;


            if (base.options.items < $items.length) {
                $tabsElement.find('.navitems-nav').show();
            } else {
                $tabsElement.find('.navitems-nav').hide();
            }
        }


        function onWindowResize() {
            owlNavInstance.$owlWrapper.children().css('height', owlNavInstance.$owlWrapper.height() + "px")
            owlNavInstance.calculateAll();
            owlNavInstance.updatePosition();
            owlNavInstance.goTo(owlNavInstance.currentItem);
            owlNavInstance.goTo(owlContentInstance.currentItem);
            owlContentInstance.autoHeight();
            toggleNavControlsVisibility();
        }

        function debounce(fn, delay) {
            var timer = null;
            return function() {
                var context = this,
                    args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function() {
                    fn.apply(context, args);
                }, delay);
            };
        }

        $(window).resize(debounce(onWindowResize, 500));

        setTimeout(function() {
            this.attr('style', '');
        }.bind($tabsElement), 500);
    };

    var $tabs = $('[data-cptabs="true"]');
    $tabs.each(function(index, el) {
        setTimeout(function() {
            makeGallery(this);

        }.bind($(el)), 0);
    });
});