$ = jQuery;


(function() {
  
    const body = document.body;
    if (!body.classList.contains('page-id-7739')) {
        console.warn('⚠️  No estás en la página page-id-7739, pero el código funcionará igual');
    }

    const buttons = document.querySelectorAll('.btn-plus');
    buttons.forEach(button => {
        if (button._collapseHandler) {
            button.removeEventListener('click', button._collapseHandler);
        }
    });
    
    function findNextCollapseElement(button) {
        let nextElement = button.nextElementSibling;
        
     
        while (nextElement) {
            if (nextElement.classList.contains('collapse')) {
                return nextElement;
            }
            nextElement = nextElement.nextElementSibling;
        }
        
        return null;
    }
   
    function toggleCollapse(button, targetElement) {
        const isHidden = targetElement.style.display === 'none' || 
                        targetElement.style.display === '' || 
                        !targetElement.style.display;
        if (isHidden) {
            // Mostrar elemento
            targetElement.style.display = 'block !important';
            targetElement.style.visibility = 'visible !important';
            targetElement.style.opacity = '1 !important';
            
            targetElement.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');
          
            showAllChildElements(targetElement);
   
            button.textContent = 'Ocultar ';
        } else {
            targetElement.setAttribute('style', 'display: none !important;');
            button.textContent = 'Mostrar ';
        }
    }

    function showAllChildElements(parentElement) {
        const childElements = parentElement.querySelectorAll('*');
        childElements.forEach(child => {
            
            child.style.setProperty('display', 'block', 'important');
            child.style.setProperty('visibility', 'visible', 'important');
            child.style.setProperty('opacity', '1', 'important');
        });
    }

    const collapseElements = [];

    buttons.forEach((button, index) => {
      
        const targetElement = findNextCollapseElement(button);
        
        if (targetElement) {
            
            collapseElements.push({
                button: button,
                target: targetElement,
                id: targetElement.id || `element-${index}`
            });
          
            targetElement.setAttribute('style', 'display: none !important;');

            if (!button.textContent.includes('Mostrar')) {
                button.textContent = 'Mostrar ';
            }
            button._collapseHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleCollapse(button, targetElement);
            };
            button.addEventListener('click', button._collapseHandler);
        } else {
            console.warn(`⚠️  No se encontró elemento .collapse después del botón ${index + 1}`);
        }
    });

    
    window.testCollapse = {
        hideAll: function() {
            collapseElements.forEach(element => {
                element.target.setAttribute('style', 'display: none !important;');
                element.button.textContent = 'Mostrar ';
            });
            
        },
        showAll: function() {
            collapseElements.forEach(element => {
                element.target.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');
                showAllChildElements(element.target);
                element.button.textContent = 'Ocultar ';
            });
           
        },
        showSpecific: function(index) {
            const element = collapseElements[index];
            if (element) {
                element.target.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');
                showAllChildElements(element.target);
                element.button.textContent = 'Ocultar ';
               
            } else {
               
            }
        },
        elements: collapseElements,
        info: function() {
            
            const info = collapseElements.map((el, index) => ({
                index: index,
                id: el.id,
                buttonText: el.button.textContent.trim(),
                isVisible: !el.target.style.display.includes('none'),
                elementType: el.target.tagName
            }));
            console.table(info);
        },
        debugElement: function(index) {
            const element = collapseElements[index];
            if (element) {
              
            }
        },
        testButton: function(index) {
            const element = collapseElements[index];
            if (element) {
             
                element.button.click();
            }
        }
    };
})();