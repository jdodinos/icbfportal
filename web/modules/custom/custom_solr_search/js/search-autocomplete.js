(function ($, Drupal, once) {
  Drupal.behaviors.customSearchAutocomplete = {
    attach: function (context, settings) {
      once('autocomplete', '#search-input', context).forEach(function (element) {
        if ($.fn.autocomplete) {  // Check if autocomplete is available
          $(element).autocomplete({
            source: function (request, response) {
              $.getJSON('/custom-search/autocomplete', { q: request.term }, response);
            },
            minLength: 2
          });
        }
      });
    }
  };
})(jQuery, Drupal, once);