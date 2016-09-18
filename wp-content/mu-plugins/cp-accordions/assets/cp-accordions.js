jQuery(document).ready(function($) {
    function initAccordion($container) {
        var link = $container.find('[data-accordion-title="true"]');
        var content = $container.find('[data-accordion-content="true"]');

        content.not(content.eq(0)).hide();
        link.not(link.eq(0)).add(content).not(content.eq(0)).removeClass('active');
        link.eq(0).add(content.eq(0)).addClass('active');
        content.eq(0).show();


        var options = $.extend({
            'speed': 300,
            'multiopen': false,
            'ease': 'linear',
            'maxheight': 'auto'
        }, $container.data());

        if (options.maxheight !== 'auto') {
            $container.find('[data-accordion-content="true"]').css({
                'overflow': 'auto',
                'max-height': options.maxheight
            })
        }

        function slideUp($el) {

            $el.each(function() {
                $(this).attr('data-oldpaddingtop', $el.css('padding-top'));
                $(this).attr('data-oldpaddingbottom', $el.css('padding-bottom'));
                $(this).attr('data-minheight', $el.css('min-height'));
                $(this).css({
                    "padding-top": '0px',
                    "padding-bottom": '0px',
                    'min-height': '0px'
                });
                $(this).removeClass('active').slideUp(options.speed);
            })
        }

        function slideDown($el) {

            $el.each(function() {
                $(this).css({
                    "padding-top": $(this).attr('data-oldpaddingtop'),
                    "padding-bottom": $(this).attr('data-oldpaddingbottom'),
                    "min-height": $(this).attr('data-minheight')
                });
                $(this).addClass('active').slideDown(options.speed, function() {

                });
            })
        }

        link.on('click', function(e) {
            e.preventDefault();

            var title = $(this);
            var content = $(this).next('[data-accordion-content="true"]');

            var other_titles = $container.find('[data-accordion-title="true"].active').not(title);
            var other_contents = $container.find('[data-accordion-content="true"].active').not(content);

            // accordion animation options
            // close others

            if (!options.multiopen) {
                other_titles.removeClass('active');
                slideUp(other_contents);
            }


            if (title.hasClass('active')) {
                if (options.multiopen) {
                    title.removeClass('active');
                    // content.removeClass('active').slideUp(options.speed);
                    slideUp(content);
                }
            } else {
                title.addClass('active');
                // content.addClass('active').slideDown(options.speed);
                slideDown(content);
            }

            var item_index = title.parent().index();
            $container.trigger('cp_item_expanded', [item_index, title, content, options]);
        });

        $container.on('cp_item_expanded', function(event, item_index, title, content, options) {
            console.log(item_index, title, content, options);
        })
    }
    $('[data-cpaccordion="true"]').each(function(index, el) {
        initAccordion($(el));
    });
});