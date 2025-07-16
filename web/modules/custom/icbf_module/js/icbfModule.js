(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.icbfModule = {
    attach: function (context, settings) {
      $('select.select-citizen-category').change(function() {
        var category_citizen = $(this).val();
        var $selectProcedure = $('.select-procedure');

        $.ajax({
          url: '/ajax-callback/get-procedure',
          data: { cat: category_citizen },
          dataType: 'json',
          success: function (response) {
            $selectProcedure.empty();
            $selectProcedure.append($('<option>', {
              value: '',
              text: '2. Qué te gustaría hacer'
            }));

            response.forEach(function (term) {
              $selectProcedure.append($('<option>', {
                value: term.id,
                text: term.label
              }));
            });

            $selectProcedure.change(function() {
              let procedure_id = $(this).val();

              response.forEach(function (term) {
                if (term.id == procedure_id) {
                  window.location.href = term.url;
                }
              })
            });
          }
        });
      })
    }
  };
})(jQuery, Drupal, drupalSettings);
