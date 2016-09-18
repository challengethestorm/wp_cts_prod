(function() {
    tinymce.create('tinymce.plugins.site_url', {
        init : function(ed) {
            var self = this;
            /*ed.onLoadContent.add(function(ed, o) {
                o.content = self._parse_site_url(o.content);
            })*/
            ed.on('BeforeSetContent', function(e) {
                e.content = self._parse_site_url(e.content);
            });
        },

        _parse_site_url : function(content) {
            //tinymce.baseURL points to wp_root/wp_includes/js/tinymce
            return content.replace(/\[tag_link_site_url\]/g, tinymce.baseURL.split('/wp-includes')[0]);
        },

        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('site_url', tinymce.plugins.site_url);
})();