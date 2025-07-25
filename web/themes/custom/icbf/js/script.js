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

      $('.view-id-calendar.view-display-id-block_page_6 .view-content').slick({
        infinite: false,
        initialSlide: 0,
        adaptiveHeight: true
      });

      // Función recalcular submenu Adopciones
      function recalcularSubmenus() {
        const dropdownItems = document.querySelectorAll('.page-id-7439 .tb-megamenu .tb-megamenu-nav > li.dropdown');

        dropdownItems.forEach(function (item) {
          const submenu = item.querySelector('.tb-megamenu-submenu');
          if (!submenu) return;

          // Reset de estilos anteriores
          submenu.style.left = '';
          submenu.style.right = '';
          submenu.style.position = 'absolute';

          // Calcular posición y ajustes
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
            slidesToShow: 5,
            slidesToScroll: 1,
            arrows: true,
            dots: true
          });
        }
        else if ($('#stpe-quoteslider').length) {
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
        else if ($('.page-id-6662').length) {
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
        else if ($('.page-adopciones-quienes-pueden-ser-adoptados .icbf-owl-carousel-wrapper').length) {
          $('.icbf-owl-carousel-wrapper').slick({
            infinite: true,
            speed: 500,
            slidesToShow: 5,
            slidesToScroll: 1,
            arrows: true,
            dots: true,
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
  // Lista de clases de página válidas
  var paginasPermitidas = [
    '.page-nutricion-bienestarina-mas-y-otros-alimentos-de-alto-valor-nutricional',
    '.page-nutricion-estrategia-de-atencion-y-prevencion-de-la-desnutricion-infantil',
    '.page-nutricion-ensin-encuesta-nacional-de-situacion-nutricional',
    '.page-nutricion-tabla-de-composicion-de-alimentos-colombianos',
    '.page-nutricion-hoja-de-balance-de-alimentos-colombianos',
    '.page-nutricion-educacion-alimentaria-y-nutricional',
    '.page-nutricion-politica-de-seguridad-alimentaria-y-nutricional'
  ];

  var aplicar = paginasPermitidas.some(function (clase) {
    return $(clase).length > 0;
  });

  if (aplicar) {
    const $megamenu = $('#block-icbf-content .content .tb-megamenu');
    const $menu = $megamenu.find('.nav-collapse');
    const $button = $megamenu.find('.tb-megamenu-button');

    if ($menu.length && $button.length) {
      // Ocultar inicialmente
      $menu.removeClass('always-show').removeClass('show').addClass('collapse');

      // Alternar visibilidad al hacer click en el botón
      $button.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation(); // evita que el clic llegue al document
        $menu.toggleClass('show');
      });

      // Prevenir cierre si se hace clic dentro del menú
      $menu.on('click', function (e) {
        e.stopPropagation();
      });

      // Cerrar el menú si se hace clic fuera
      $(document).on('click', function () {
        if ($menu.hasClass('show')) {
          $menu.removeClass('show');
        }
      });
    }
  }
});


//Se añade la numeracion de los select

/*
$(document).ready(function () {
  const clasesPermitidas = [
    '.page-portafolio-de-servicios-icbf-fijacion-cuota-de-alimentos',
    '.page-fijacion-cuota-de-alimentos',
    '.page-portafolio-de-servicios-icbf-ninos-y-ninas2',
    '.page-portafolio-de-servicios-icbf-ninos-y-ninas',
    '.page-portafolio-de-servicios-icbf-soy-adolescente',
    '.page-portafolio-de-servicios-icbf-adolescentes-y-jovenes',
    '.page-portafolio-de-servicios-icbf-soy-entidad',
    '.page-portafolio-de-servicios-icbf-entidades',
    '.page-portafolio-de-servicios-icbf-somos-familia',
    '.page-portafolio-de-servicios-icbf-familias'
  ];

  const paginaValida = clasesPermitidas.some(clase => $(clase).length > 0);

  if (paginaValida) {
    $('.precontent select option').each(function(index) {
      const textoOriginal = $(this).text().trim();
      $(this).text((index + 1) + ' ' + textoOriginal);
    });
  }
});
*/