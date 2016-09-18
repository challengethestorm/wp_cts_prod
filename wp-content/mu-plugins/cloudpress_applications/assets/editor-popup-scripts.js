  (function($) {
      $.fn.serializeObject = function() {

          var self = this,
              json = {},
              push_counters = {},
              patterns = {
                  "validate": /^[a-zA-Z_][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                  "key": /[a-zA-Z0-9_]+|(?=\[\])/g,
                  "push": /^$/,
                  "fixed": /^\d+$/,
                  "named": /^[a-zA-Z0-9_]+$/
              };


          this.build = function(base, key, value) {
              base[key] = value;
              return base;
          };

          this.push_counter = function(key) {
              if (push_counters[key] === undefined) {
                  push_counters[key] = 0;
              }
              return push_counters[key]++;
          };

          $.each($(this).serializeArray(), function() {

              // skip invalid keys
              if (!patterns.validate.test(this.name)) {
                  return;
              }

              var k,
                  keys = this.name.match(patterns.key),
                  merge = this.value,
                  reverse_key = this.name;

              while ((k = keys.pop()) !== undefined) {

                  // adjust reverse_key
                  reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

                  // push
                  if (k.match(patterns.push)) {
                      merge = self.build([], self.push_counter(reverse_key), merge);
                  }

                  // fixed
                  else if (k.match(patterns.fixed)) {
                      merge = self.build([], k, merge);
                  }

                  // named
                  else if (k.match(patterns.named)) {
                      merge = self.build({}, k, merge);
                  }
              }

              json = $.extend(true, json, merge);
          });

          return json;
      };
  })(jQuery);
  jQuery(document).ready(function($) {

      setTimeout(function() {
          $('.popup-loading').hide();
      }, 0);

      $("form#post").prepend('<input type="hidden" name="xtd_stripped" value="1">');
      $("form#post").append('<input type="hidden" name="post_status" value="publish">');
      // $("form#post").append('<input type="hidden" name="hidden_post_status" value="publish">');
      // $("form#post").append('<input type="hidden" name="original_post_status" value="publish">');
      $("form#post").submit(function(event) {

          event.preventDefault();
          event.stopPropagation();
          jQuery('#message').remove();
          // var data = _.object(jQuery(this).serializeArray().map(function(v) {return [v.name, v.value];} ));
          var data = jQuery(this).serializeObject()
              /*.map(function(v) {
              return [v.name, v.value];
          });*/

          delete data['referredby'];
          var url = $("form#post").attr('action') || (window.location + "").split('?')[0];
          data.content =  jQuery("#content").val();
          if (tinyMCE.activeEditor) {
              data.content = tinyMCE.activeEditor.getContent();
          } else {}
          jQuery(".popup-loading").show();
          $.ajax({
                  url: url,
                  type: 'POST',
                  data: data,
              })
              .done(function() {
                  // jQuery(".popup-loading").hide();
                  jQuery('.wrap h2').after('<div id="message" class="updated below-h2"><p>Post updated.</p></div>');
                  jQuery('body').trigger('POST_UPDATED');

              })
              .fail(function() {
                  console.log("error");
              })
      });

      if (location.href.match(/\?post_type=(.*)&xtd_stripped=1/)) {
          $('form a').attr('href', '#');
      }
  });