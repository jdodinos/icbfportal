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


(function () {
  const baseUrl = "https://172.25.30.141";
  const relativePath = "/sites/default/files/";
  const fullPath = baseUrl + relativePath;

  const oldHost = "https://icbfportal.ddev.site/sites/default/files";
  const newHost = "https://172.25.30.141/sites/default/files";

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
