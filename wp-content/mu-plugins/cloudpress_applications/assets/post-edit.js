jQuery(document).ready(function($) {
    var announceSaveDelay = 1000;
    jQuery('body').bind('CUSTOM_FIELD_UPDATED', function(event) {
        if (top.ve) {
            setTimeout(function() {
                top.ve.trigger('CUSTOM_FIELD_UPDATED');
            }, announceSaveDelay)
        }
    });
    jQuery('body').bind('POST_UPDATED', function(event) {
        if (top.ve) {
            setTimeout(function() {
                top.ve.trigger('POST_UPDATED');
            }, announceSaveDelay)
        }
    });
    $('[name="update"]').click(function(event) {
        event.preventDefault();
        event.stopPropagation();
        var self = $(this);
        var $td = self.closest('td');
        var url = $('#postcustomstuff').data('update-url');
        var data = {
            type: $td.data('type'),
            field: $td.data('field'),
            id: $td.data('id'),
            value: ""
        }
        switch (data.type) {
            case "text":
            case "link":
            case "email":
            case "video":
                data.value = $td.find('textarea').val();
                break;
            case "image":
            case "bg_image":
                data.value = $td.find('img').attr('src');
                break;
        }
       
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
        })
            .done(function(response) {
                jQuery('body').trigger('CUSTOM_FIELD_UPDATED');
                switch (data.type) {
                    case "video":
                        $td.find('[id="preview-video"]').replaceWith(response);
                        break;
                }
                $td.find('.field-updated').show();
                $td.find('.field-updated').addClass('visible');
                setTimeout(function() {
                    jQuery(jQuery.find('.field-updated.visible')).removeClass('visible');
                    
                }, 2000);
            })
    });
    $("#the-list").find('textarea, .change-meta-image').change(function(event) {
        var self = $(this);
        var $td = self.closest('td');
        $td.find('[name="update"]').click();
    });
    $('.change-meta-image').click(function(event) {
        event.preventDefault();
        event.stopPropagation();
        var self = $(this);
        var $td = self.closest('td');
        var callback = function(images) {
            if (images) {
                image = images[0];
                url = image.sizes.full.url;
                $td.find('img').attr('src', url);
                self.trigger('change');
            }
        }
        var custom_uploader = wp.media({
            title: 'Choose ' + self.attr('data-meta-key'),
            button: {
                text: 'Choose ' + self.attr('data-meta-key')
            },
            multiple: false
        });
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').toJSON();
            callback(attachment);
        });
        custom_uploader.on('close', function() {
            callback(false);
        });
        custom_uploader.open();
    })
});