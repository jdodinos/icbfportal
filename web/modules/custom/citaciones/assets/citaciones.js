// custom javascript here
(function($, Drupal) {
  Drupal.behaviors.citaciones = {
    attach: function (context, settings) {
      var autoApertura = $('#edit-field-legal-act-und-auto-de-apertura');
      var audiencia = $('#edit-field-legal-act-und-audiencia');
      var kindShip = $('.field-name-field-kinship select');
      var set_parameters = $('#set_button');
      var reportsubmit = $('#edit-report-range .form-submit', context);
      //var regionReport = $('#edit-field-region-office');

      // regionReport
      //   .on('change', function() {
      //     if($(this).val() != '') {
      //       $('#edit-rightcol').slideDown();
      //     } else {
      //       $('#edit-rightcol').slideUp();
      //     }
      //   });

      $('.field-type-field-collection .tabledrag-toggle-weight-wrapper').css("display", "none");
      /*$('.field-name-field-creation-date').css("display", "none");
      $('.field-name-field-unpublish-date').css("display", "none");
      $('.field-name-field-summoned-file').css("display", "none");*/
      $('#edit-scheduler-settings').css("display", "none");
      if(autoApertura.is(':checked')) {
        $('.group-auto-data').slideDown();
      } else {
        $('.group-auto-data').slideUp();
      }

      if(audiencia.is(':checked')) {
        $('.group-audience-data').slideDown();
      } else {
        $('.group-audience-data').slideUp();
      }


      $('#edit-create-end-date-datepicker-popup-0')
        .change(function() {
          if($('#edit-create-start-date-datepicker-popup-0').val() != '') {
            var end_date = new Date($('#edit-create-end-date-datepicker-popup-0').val());
            var start_date = new Date($('#edit-create-start-date-datepicker-popup-0').val());
            if(end_date < start_date) {
              alert('La fecha final debe ser mayor a la fecha inicial');
            } else {
              $('#edit-create-date').find('.form-submit').slideDown();
            }
          }

        });


      $('#edit-publication-end-date-datepicker-popup-0')
        .change(function() {
          if($('#edit-publication-start-date-datepicker-popup-0').val() != '') {
            var end_date = new Date($('#edit-publication-end-date-datepicker-popup-0').val());
            var start_date = new Date($('#edit-publication-start-date-datepicker-popup-0').val());
            if(end_date < start_date) {
              alert('La fecha final debe ser mayor a la fecha inicial');
            } else {
              $('#edit-publication-date').find('.form-submit').slideDown();
            }
          }

        });

      $('#edit-unpublishing-end-date-datepicker-popup-0')
        .change(function() {
          if($('#edit-unpublishing-start-date-datepicker-popup-0').val() != '') {
            var end_date = new Date($('#edit-unpublishing-end-date-datepicker-popup-0').val());
            var start_date = new Date($('#edit-unpublishing-start-date-datepicker-popup-0').val());
            if(end_date < start_date) {
              alert('La fecha final debe ser mayor a la fecha inicial');
            } else {
              $('#edit-unpublish-date').find('.form-submit').slideDown();
            }
          }

        });

      $('#edit-select-action')
        .change(function() {
             $('#edit-legal-action').find('.form-submit').slideDown();
        });

      $('#edit-end-number')
        .change(function() {
          if($('#edit-start-number').val() != '') {
            var end_date = $('#edit-end-number').val();
            var start_date = $('#edit-start-number').val();
            if(end_date < start_date) {
              alert('El número final debe ser mayor al inicial');
            } else {
              $('#edit-sim').find('.form-submit').slideDown();
            }
          }

        });

      set_parameters
        .click(function() {
          return false;
      });

      autoApertura
        .click(function() {
          if($(this).is(':checked')) {
            //audiencia.attr('checked', false);
            $('.group-auto-data').slideDown();
            //$('.group-audience-data').slideUp();

          } else {
            $('.group-auto-data').slideUp();

          }
        });

      reportsubmit
        .click(function() {
          alert('Se ha generado el reporte satisfactoriamente');


        });

      audiencia
        .click(function() {
          if($(this).is(':checked')) {
            //autoApertura.attr('checked', false);
            $('.group-audience-data').slideDown();
            //$('.group-auto-data').slideUp();
          } else {
            $('.group-audience-data').slideUp();
          }
        });

      kindShip
        .change(function() {
          if($(this).val() == 'Otro') {
            $(this).parents('.col-sm-3').find('.field-name-field-other-kinship').slideDown();
          } else {
            $(this).parents('.col-sm-3').find('.field-name-field-other-kinship').slideUp();
          }
        });

      $('#edit-field-lugar-und-hierarchical-select-selects-0 option:first')
        .text('<Seleccione>');

      $('#edit-field-region-office-und-hierarchical-select-selects-0 option:first')
        .text('<Seleccione>');

      var options = $('option[value="_none"]');

      for(var i = 0; i < options.length; i++) {
        $(options[i]).text('<Seleccione>');
      }

      var kindships = $('#field-summoned-values .field-type-list-text');

      for(var i = 0; i < kindships.length; i++) {
        if($(kindships[i]).find('option[selected="selected"]').val() == 'Otro') {
          $(kindships[i])
            .parent()
            .find('.field-name-field-other-kinship')
            .css('display', 'block');
        }
      }

      $('#field-resolution-date-add-more-wrapper span.fieldset-legend')
        .once('myCustomBehavior')
        .append('<span class="form-required" title="Este campo es obligatorio."> &nbsp;*</span>');

      $('#field-legal-act-date-add-more-wrapper span.fieldset-legend')
        .once('myCustomBehavior')
        .append('<span class="form-required" title="Este campo es obligatorio.">&nbsp;*</span>');

      $('#field-summoned-und-0-field-other-kinship-add-more-wrapper > div > label')
        .once('myCustomBehavior')
          .append('<span class="form-required" title="Este campo es obligatorio.">&nbsp;*</span>');



      $('#edit-field-officer-name-wrapper input')
        .attr('maxlength', '100');

      var dateRegex = /^(0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])[- /.](19|20)\d\d$/;

      $('#edit-field-creation-date-wrapper #edit-field-creation-date-datepicker-popup-1')
        .change(function() {
          if(this.value.match(dateRegex) || this.value.length == 0) {
            $('#edit-field-creation-date-wrapper')
              .removeClass("has-error");
            $('#edit-field-creation-date-wrapper .incorrectFormat')
              .remove();
            $('#edit-submit-vista-panel-control-defensores')
              .removeAttr('disabled');
          } else {
            $('#edit-field-creation-date-wrapper')
              .addClass("has-error");
            if(!$('#edit-field-creation-date-wrapper .incorrectFormat').length) {
              $('#edit-field-creation-date-wrapper .form-item-field-creation-date-date')
                .append('<div class="form-required incorrectFormat has-error" style="color: #a94442; display: block;">El formato correcto es mm/dd/aaaa</div>')
            }
            $('#edit-submit-vista-panel-control-defensores')
              .attr('disabled', 'true');
          }
        });

      $('#edit-field-unpublish-date-wrapper #edit-field-unpublish-date-datepicker-popup-1').change(function() {
          if(this.value.match(dateRegex) || this.value.length == 0) {
            $('#edit-field-unpublish-date-wrapper')
              .removeClass("has-error");
            $('#edit-field-unpublish-date-wrapper .incorrectFormat')
              .remove();
            $('#edit-submit-vista-panel-control-defensores')
              .removeAttr('disabled');
          } else {
            $('#edit-field-unpublish-date-wrapper')
              .addClass("has-error");
            if(!$('#edit-field-unpublish-date-wrapper .incorrectFormat').length) {
              $('#edit-field-unpublish-date-wrapper .form-item-field-unpublish-date')
                .append('<div class="form-required incorrectFormat has-error" style="color: #a94442; display: block;">El formato correcto es mm/dd/aaaa</div>')
            }
            $('#edit-submit-vista-panel-control-defensores')
              .attr('disabled', 'true');
          }
        });


      var regionalsMapped = {
          0: 0,
          "label_0": "label_0",
          11: 4869,
          6194: 4743,
          12: 4881,
          6220: 4889,
          14: 4913,
          15: 4915,
          16: 4962,
          17: 5086,
          18: 5114,
          6276: 5131,
          6280: 5151,
          21: 5194,
          22: 5220,
          23: 5251,
          24: 5293,
          25: 5267,
          26: 5288,
          27: 5299,
          28: 5376,
          29: 5409,
          6348: 5472,
          30: 5568,
          31: 5527,
          32: 5633,
          33: 5647,
          34: 5660,
          35: 5675,
          36: 5678,
          37: 5766,
          38: 5793,
          39: 5841,
          40: 5884,
          41: 5891,
          6901: 4913
      };

      //citaciones/add
      $('.hierarchical-select-wrapper-for-name-edit-field-region-office-und').on('update-hierarchical-select', function() {
        console.log('update');
      });
      $('.hierarchical-select-wrapper-for-name-edit-field-region-office-und').on('change-hierarchical-select', function() {
        console.log('change');
        var regionalSelect = $('select[name="field_region_office[und][hierarchical_select][selects][0]"]');
        var departmentsSelect = $('select[name="field_lugar[und][hierarchical_select][selects][0]"]');
        var regionalValue = regionalSelect.
          children('option[selected=selected]')
          .val();
          console.log(regionalSelect.children('option[selected=selected]'));




        for(var regional in regionalsMapped) {
          console.log('Regional Value', regionalValue);
          console.log('Regional', regional);
          if(regionalValue === regional) {
            departmentsSelect
              .children('option[value=' + regionalsMapped[regionalValue] + ']')
              .attr('selected', 'selected');
              departmentsSelect.change();
              break;
          }
        }
        setTimeout(function() {
          $('.hierarchical-select-wrapper-for-config-taxonomy-field_lugar:first')
            .once('triggerEnforceUpdateCitacionesAdd')
            .trigger('enforce-update');
        }, 1500);
      });
      //citaciones/reportes
      $('.hierarchical-select-wrapper-for-config-taxonomy-field_department_category').on('change-hierarchical-select', function() {
        var regionalSelect = $('select[name="field_region_office[hierarchical_select][selects][0]"]');
        var departmentsSelect = $('select[name="locality[hierarchical_select][selects][0]"]');
        var regionalValue = regionalSelect.
          children('option[selected=selected]')
          .val();

        if(!$(this).val().length) {
          $('#edit-rightcol').slideDown();
        } else {
          $('#edit-rightcol').slideUp();
        }
        // departmentsSelect
        //   .children('option[selected=selected]')
        //   .each(function() {
        //     $(this).removeAttr('selected');
        //   });


        for(var regional in regionalsMapped) {
          // console.log('Regional Value', regionalValue);
          // console.log('Regional', regional);
          if(regionalValue === regional) {
            departmentsSelect
              .children('option[value=' + regionalsMapped[regionalValue] + ']')
              .attr('selected', 'selected');
              break;
          }

        }
        setTimeout(function() {
          $('.hs_summon_report.hierarchical-select-wrapper-for-config-taxonomy-field_autopart_category:first')
          .once('triggerEnforceUpdateCitacionesReport')
          .trigger('enforce-update');
        }, 1500);
      });


    }
  };
}(jQuery, Drupal));

