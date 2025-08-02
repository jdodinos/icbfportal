$ = jQuery;


(function ($, Drupal) {
  Drupal.behaviors.icbfTheme = {
    attach: function (context, settings) {
      var active_class = 'active';

      $('.views-bootstrap-tabs ul.nav-tabs >li >a').click(function () {
        var tabId = $(this).attr('href');
        var $parent = $(this).parents('.views-bootstrap-tabs');
        $(this).parent().addClass(active_class).siblings().removeClass(active_class);
        $parent.find('.tab-pane').removeClass(active_class);
        $parent.find(tabId).addClass(active_class);
      });

      // Slider de calendario de eventos.
      if ($('.view-id-calendar.view-display-id-block_page_6').length) {
        var num_items = $('.view-id-calendar.view-display-id-block_page_6 .view-content >div').length;
        $('.view-id-calendar.view-display-id-block_page_6 .view-content').slick({
          infinite: false,
          initialSlide: num_items - 1,
          adaptiveHeight: true,
          fade: true,
          speed: 300,
          prevArrow: '<button type="button" class="slick-prev">Prev</button>'
        });
      }

      // Funcionalidad para los filtros de la vista de tramites.
      if ($('.view-id-tramites_sapi.view-display-id-block_7').length && !$('.view-id-tramites_sapi.view-display-id-block_7 a.btn-show-more').length) {
        var $wrapper = $('.view-id-tramites_sapi.view-display-id-block_7 fieldset[data-drupal-selector="edit-field-tags-de-tramite"] .bef-checkboxes');
        var $lis = $wrapper.find('li');

        if ($lis.length > 10) {
          $.each($lis, function(key, value) {
            if (key >= 10) {
              $(this).css('display', 'none');
            }
          });

          if (!$wrapper.find('.btn-show-more').length) {
            $wrapper.append('<a class="btn-show-more more" href="">Mostrar Más</a>');
            $wrapper.find('.btn-show-more').click(function(e) {
              e.preventDefault();
              if ($(this).hasClass('more')) {
                $lis.show('slow');
                $(this).text('Mostrar Menos').removeClass('more').addClass('less');
              }
              else {
                $.each($lis, function (key, value) {
                  if (key >= 10) {
                    $(this).hide('slow');
                  }
                });
                $(this).text('Mostrar Más').removeClass('less').addClass('more');
              }
            }).css('margin-left', '20px');
          }
        }
      }

      // Función recalcular submenu Adopciones
      function recalcularSubmenus() {
        const pageSelectors = [
          '.page-id-7439',
          '.page-adopciones',
          '.page-adopciones-quienes-pueden-adoptar',
          '.page-adopciones-quienes-pueden-ser-adoptados',
          '.page-adopciones-quienes-pueden-adoptar',
          '.page-adopciones-requisitos-basicos',
          '.page-adopciones-proceso-de-adopcion',
          '.page-adopciones-paso-paso',

          // aquí puedes agregar más
        ];

        // Crear un selector combinado
        const combinedSelector = pageSelectors
          .map(sel => `${sel} .tb-megamenu .tb-megamenu-nav > li.dropdown`)
          .join(', ');

        const dropdownItems = document.querySelectorAll(combinedSelector);

        dropdownItems.forEach(function (item) {
          const submenu = item.querySelector('.tb-megamenu-submenu');
          if (!submenu) return;

          submenu.style.left = '';
          submenu.style.right = '';
          submenu.style.position = 'absolute';

          const itemRect = item.getBoundingClientRect();
          let viewportWidth = window.innerWidth;
          if (viewportWidth > 1150) {
            viewportWidth = 1150;
          }

          const submenuWidth = submenu.offsetWidth || 400;
          const projectedRight = itemRect.left + submenuWidth;

          if (projectedRight > viewportWidth) {
            submenu.style.left = 'initial';
            submenu.style.right = '0';
          } else {
            submenu.style.left = item.offsetLeft + 'px';
            submenu.style.right = 'initial';
          }
        });
      }

      recalcularSubmenus()
      document.addEventListener('DOMContentLoaded', recalcularSubmenus);
      window.addEventListener('resize', recalcularSubmenus);


    }
  }
})(jQuery, Drupal);


(function ($, Drupal) {
  Drupal.behaviors.owlInit = {
    attach: function (context, settings) {
      if (context === document) {

        if ($('.block-block-content5712aaa6-336a-4df9-b947-c66cd32d67fe').length) {
          // Cards del HOME.
          $('#hgepm7aq .icbf-owl-carousel-wrapper').slick({
            infinite: false,
            speed: 300,
            slidesToShow: 5,
            slidesToScroll: 5,
            arrows: false,
            dots: false,
            responsive: [
              {
                breakpoint: 1024,
                settings: {
                  slidesToShow: 5,
                  slidesToScroll: 5
                }
              },
              {
                breakpoint: 768,
                settings: {
                  slidesToShow: 2,
                  slidesToScroll: 2
                }
              },
              {
                breakpoint: 600,
                settings: {
                  slidesToShow: 1,
                  slidesToScroll: 1,
                  infinite: true
                }
              }
            ]
          });
        }
        else if ($('#xisegdle').length) {
          // Bloque superior de preguntas frecuentes.
          $('#xisegdle .icbf-owl-carousel-wrapper').slick({
            infinite: false,
            autoplay: true,
            autoplaySpeed: 3000,
            speed: 300,
            slidesToShow: 5,
            slidesToScroll: 1,
            arrows: true,
            dots: true
          });
        }
        else if ($('#5qgon8wb').length) {
          $('#5qgon8wb .icbf-owl-carousel-wrapper').slick({
            infinite: false,
            autoplay: true,
            autoplaySpeed: 3000,
            speed: 300,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            dots: true
          });
        }
        else if ($('#stpe-quoteslider').length) {
          // Slider inferior pagina Participa.
          $('#stpe-quoteslider .icbf-owl-carousel-wrapper').slick({
            infinite: true,
            speed: 1000,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            dots: true,
            fade: true
          });
        }
        else if ($('.page-id-6662').length || $('.page-id-8077').length) {
          $(' .icbf-owl-carousel-wrapper').slick({
            infinite: true,
            speed: 500,
            autoplay: true,
            autoplaySpeed: 3000,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            dots: true
          });
        }
        else if ($('.page-id-1233 #qzywunrj').length) {
          const $carousel = $('.page-id-1233 #qzywunrj .icbf-owl-carousel-wrapper');

          // Inicializa Slick
          $carousel.slick({
            fade: true,
            infinite: true,
            speed: 1000,
            autoplaySpeed: 4000,
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: true,
            arrows: true,
            dots: true
          });
        }
        else if ($('#6gy1mn3w').length) {
          $('#6gy1mn3w .icbf-owl-carousel-wrapper').slick({
            infinite: true,
            speed: 1000,
            autoplaySpeed: 12000,
            slidesToShow: 5,
            slidesToScroll: 5,
            autoplay: true,
            arrows: false,
            dots: false,
            responsive: [
              {
                breakpoint: 1024,
                settings: {
                  slidesToShow: 5,
                  slidesToScroll: 1
                }
              },
              {
                breakpoint: 768,
                settings: {
                  slidesToShow: 2,
                  slidesToScroll: 1
                }
              },
              {
                breakpoint: 600,
                settings: {
                  slidesToShow: 1,
                  slidesToScroll: 1,
                }
              }
            ]
          });
        }
        else if ($('.page-adopciones-quienes-pueden-ser-adoptados .icbf-owl-carousel-wrapper').length) {
          $('.icbf-owl-carousel-wrapper').slick({
            infinite: true,
            speed: 500,
            slidesToShow: 5,
            autoplay: true,
            autoplaySpeed: 12000,
            slidesToScroll: 1,
            arrows: true,
            dots: true,
            responsive: [
              {
                breakpoint: 1024,
                settings: {
                  slidesToShow: 5,
                }
              },
              {
                breakpoint: 992,
                settings: {
                  slidesToShow: 4,
                }
              },
              {
                breakpoint: 860,
                settings: {
                  slidesToShow: 3,
                }
              },
              {
                breakpoint: 768,
                settings: {
                  slidesToShow: 2,
                }
              },
              {
                breakpoint: 600,
                settings: {
                  slidesToShow: 1,
                  infinite: true
                }
              }
            ]
          });
        }
        else {
          $('.icbf-owl-carousel-wrapper').slick({
            infinite: true,
            speed: 500,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            dots: true,
          });
        }
      };
    }
  };
})(jQuery, Drupal);



//se añade funcionalkidad global para el uso de collapse
$(document).ready(function () {
  // Mostrar el panel al cargar correctamente usando Bootstrap

  if ($('body').hasClass('page-transparencia-y-acceso-informacion-publica-participa')) {
    $('#pkojmarg').addClass('show');
  }
  $('[data-toggle="collapse"]').click(function (e) {
    const target = $(this).attr('href');
    const parent = $(this).data('parent');

    // Ocultar todos menos el actual
    $(parent).find('.collapse').not(target).collapse('hide');
    $(target).collapse('toggle');
  });

  $('.collapse').on('show.bs.collapse', function () {
    const id = $(this).attr('id');
    $(`[href="#${id}"]`).removeClass('collapsed').attr('aria-expanded', 'true');
  });

  $('.collapse').on('hide.bs.collapse', function () {
    const id = $(this).attr('id');
    $(`[href="#${id}"]`).addClass('collapsed').attr('aria-expanded', 'false');
  });
});




//funcionalidad en familias-y-comunidades al seleccionar iconos
$(document).ready(function ($) {
  // Seleccionamos el wrapper de quicktabs específico para esta página
  var $wrapper = $('.page-id-7618 .content .layout:nth-child(3) .quicktabs-wrapper');

  if ($wrapper.length) {
    var $tabItems = $wrapper.find('.quicktabs-tabs li');
    var $tabLinks = $wrapper.find('.quicktabs-tabs li a');
    var $tabPages = $wrapper.find('.quicktabs-tabpage');

    console.log('Elementos encontrados:', {
      wrapper: $wrapper.length,
      tabItems: $tabItems.length,
      tabLinks: $tabLinks.length,
      tabPages: $tabPages.length
    });

    // Función para cambiar pestaña
    function activateTab(index) {
      console.log("Activando pestaña (índice li):", index);

      // Actualizar estado de las pestañas
      $tabItems
        .removeClass('active')
        .attr('aria-selected', 'false');

      $tabItems.eq(index)
        .addClass('active')
        .attr('aria-selected', 'true');

      // Actualizar contenido - Ocultar todos primero
      $tabPages.addClass('quicktabs-hide');

      // Mostrar solo el contenido seleccionado
      $tabPages.eq(index).removeClass('quicktabs-hide');
    }

    // Manejar clic en enlaces de pestañas
    $tabLinks.on('click', function (e) {
      e.preventDefault();
      var index = $tabItems.index($(this).parent());
      console.log("Click en pestaña (índice li):", index, $(this).text());
      activateTab(index);
    });

    // Inicialización - Forzar mostrar el primer tab al cargar
    $tabPages.addClass('quicktabs-hide'); // Ocultar todos primero

    // Verificar si hay un tab activo
    var activeTab = $wrapper.find('.quicktabs-tabs li.active');

    if (activeTab.length > 0) {
      var activeIndex = $tabItems.index(activeTab);
      console.log("Pestaña activa encontrada (índice li):", activeIndex);
      activateTab(activeIndex);
    } else {
      console.log("No hay pestaña activa, activando la primera li");
      $tabItems.eq(0).addClass('active').attr('aria-selected', 'true');
      $tabPages.eq(0).removeClass('quicktabs-hide');
    }
  } else {
    console.log("No se encontró el wrapper de quicktabs");
  }
});



//funcionalidad de Collapse (En las paginas de Nutrición)
$(document).ready(function () {
  var paginasPermitidas = [
    '.region-nav-main',
    '.page-nutricion-bienestarina-mas-y-otros-alimentos-de-alto-valor-nutricional',
    '.page-nutricion-estrategia-de-atencion-y-prevencion-de-la-desnutricion-infantil',
    '.page-nutricion-ensin-encuesta-nacional-de-situacion-nutricional',
    '.page-nutricion-tabla-de-composicion-de-alimentos-colombianos',
    '.page-nutricion-hoja-de-balance-de-alimentos-colombianos',
    '.page-nutricion-educacion-alimentaria-y-nutricional',
    '.page-nutricion-politica-de-seguridad-alimentaria-y-nutricional',
    '.page-programas-y-estrategias-primera-infancia-mediatica-recursos-para-la-primera-infancia',
  ];

  var aplicar = paginasPermitidas.some(function (clase) {
    return $(clase).length > 0;
  });

  if (aplicar) {
    const $megamenu = $('.tb-megamenu');

    $megamenu.each(function () {
      const $thisMenu = $(this);
      const $button = $thisMenu.find('.tb-megamenu-button');
      const $collapse = $thisMenu.find('.nav-collapse');

      if ($button.length && $collapse.length) {
        $collapse.removeClass('always-show show').addClass('collapse');

        $button.on('click', function (e) {
          e.preventDefault();
          e.stopPropagation();

          // Cierra cualquier otro collapse abierto
          $('.tb-megamenu .nav-collapse.show').not($collapse).removeClass('show');

          // Alterna el actual
          $collapse.toggleClass('show');
        });

        $collapse.on('click', function (e) {
          e.stopPropagation();
        });

        $(document).on('click', function () {
          $collapse.removeClass('show');
        });
      }
    });
  }
});



//funcionalidad de filtros para doble tablas (se oculta el segundo filtro pero se debe activar con el primero)
// Espera a que el DOM esté listo
/*
$(document).ready(function () {
  const $pagina = $('.page-id-8031');

  if ($pagina.length) {
    // field_dae_year → field_date
    $pagina.find('input[name="field_dae_year"]').on('change', function () {
      const valor = $(this).val();
      $pagina.find('input[name="field_date"]').each(function () {
        if ($(this).val() === valor) {
          $(this).prop('checked', true).trigger('change');
        }
      });
    });

    // field_date → field_dae_year
    $pagina.find('input[name="field_date"]').on('change', function () {
      const valor = $(this).val();
      $pagina.find('input[name="field_dae_year"]').each(function () {
        if ($(this).val() === valor) {
          $(this).prop('checked', true).trigger('change');
        }
      });
    });
  }
});*/


