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
        initialSlide: 0
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
      $(".owl-carousel").owlCarousel({
        items: 1,
        singleItem: true,
        itemsScaleUp: true,
        slideSpeed: 500,
        autoPlay: 5000,
        stopOnHover: true
      });
    }
  };
})(jQuery, Drupal);



//se añade funcionalkidad global para el uso de collapse
$(document).ready(function () {
  // Mostrar el panel al cargar correctamente usando Bootstrap
  if ($('body').hasClass('page-participacion-para-el-diagnostico-e-identificacion-de-problemas')) {
    $('#n9clt176').collapse('show');
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

(function () {
  if (location.host !== "icbfportal.ddev.site") return;

  const baseUrl = "https://www.icbf.gov.co";
  const relativePath = "/sites/default/files/";
  const fullPath = baseUrl + relativePath;

  const oldHost = "https://icbfportal.ddev.site/sites/default/files";
  const newHost = "https://portalpruebas2.icbf.gov.co/sites/default/files";

  const attributes = ["src", "href"];

  function updateAttributes(root = document) {
    attributes.forEach(attr => {
      const elements = root.querySelectorAll(`[${attr}]`);
      elements.forEach(el => {
        const value = el.getAttribute(attr);
        if (value && value.startsWith(relativePath)) {
          el.setAttribute(attr, value.replace(relativePath, fullPath));
        }
      });
    });

    // Reemplazo de contenido hardcoded en innerHTML
    if (root === document) {
      document.body.innerHTML = document.body.innerHTML.replaceAll(oldHost, newHost);
    } else {
      // Evita reemplazo completo del DOM si root es un nodo parcial
      root.querySelectorAll("*").forEach(el => {
        if (el.innerHTML && el.innerHTML.includes(oldHost)) {
          el.innerHTML = el.innerHTML.replaceAll(oldHost, newHost);
        }
      });
    }
  }

  // Ejecuta inmediatamente
  updateAttributes();

  // Observa cambios dinámicos en el DOM
  const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      mutation.addedNodes.forEach(node => {
        if (node.nodeType === 1) { // ELEMENT_NODE
          updateAttributes(node);
        }
      });
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });
})();
