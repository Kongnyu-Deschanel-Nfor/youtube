(function ($, document) {
  "use strict";

  var orderable_layouts = {
    /**
     * On doc ready.
     */
    on_ready: function () {
      orderable_layouts.builder.init();
    },
    /**
     * Helper functions.
     */
    helpers: {
      /**
       * Get field type.
       */
      get_field_type: function ($field) {
        var tag = $field.get(0).tagName;
        if ('SELECT' === tag) {
          return 'select';
        } else {
          return $field.attr('type');
        }
      },
      /**
       * Strip "orderable_" prefix.
       *
       * @param string
       * @return {*}
       */
      strip_prefix: function (string) {
        return string.replace('orderable_', '');
      }
    },
    /**
     * Builder.
     */
    builder: {
      /**
       * Init builder.
       */
      init: function () {
        orderable_layouts.builder.watch();
      },
      /**
       * Watch builder fields.
       */
      watch: function () {
        $(document.body).on('change', '#orderable-layout-settings-metabox input, #orderable-layout-settings-metabox select', function () {
          var $parent = $(this).closest('#orderable-layout-settings-metabox'),
            data = {},
            reload_preview = true;
          $parent.find('.form-field > input, .form-field > select:enabled').each(function (index, field) {
            var $field = $(field),
              type = orderable_layouts.helpers.get_field_type($field),
              value = '';
            if ('checkbox' === type) {
              value = $(this).is(':checked');
            } else if ('select' === type && 'multiple' === $field.attr('multiple')) {
              value = $field.val();
            } else {
              value = $field.val();
            }

            // Has a "pro_" prefix.
            if (typeof value === 'string' && 0 === value.indexOf('pro_')) {
              reload_preview = false;
              $(document.body).trigger('orderable-pro-modal');
            }
            data[orderable_layouts.helpers.strip_prefix(this.name.replace('[]', ''))] = value;
          });
          if (reload_preview) {
            orderable_layouts.builder.reload_preview(data);
          }
        });
      },
      /**
       * Reload preview.
       *
       * @param preview_data
       */
      reload_preview: function (preview_data) {
        var data = {
          'action': 'orderable_preview',
          'data': preview_data
        };
        $.post(ajaxurl, data, function (response) {
          if (!response.success) {
            return;
          }
          $('#orderable-layout-preview-metabox .orderable-main-wrap').html(response.data.shortcode);
        });
      }
    }
  };
  $(document).ready(orderable_layouts.on_ready);
})(jQuery, document);