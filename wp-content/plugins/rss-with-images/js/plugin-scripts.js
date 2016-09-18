(function($) {
    
    $(document).ready(function(){

        $(".rsswithimages-dismiss").click( function(){

            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: "action=set_rwi_nag_transient",
            });
        
        });

    });
    
})(jQuery);