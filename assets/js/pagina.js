/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: CONTROLADOR DE NAVEGACIÓN Y COMPORTAMIENTOS DE INTERFAZ
 * * @package     Frontend_UI
 * @subpackage  Navigation_Logic
 * @version     7.0.12 (Basado en Start Bootstrap - Agency)
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Gestiona la interactividad de la barra de navegación superior (Navbar). 
 * Implementa efectos de contracción visual, seguimiento de sección (ScrollSpy) 
 * y auto-colapso en entornos responsivos para mejorar la usabilidad.
 * * CRÉDITOS:
 * Basado en la plantilla Agency de Start Bootstrap (Licencia MIT).
 */

/* global bootstrap */

window.addEventListener('DOMContentLoaded', event => {

    /**
     * 1. SUBSISTEMA DE ADAPTACIÓN VISUAL (NAVBAR SHRINK)
     * Modifica la apariencia de la barra de navegación según la posición del scroll.
     */
    var navbarShrink = function () {
        const navbarCollapsible = document.body.querySelector('#mainNav');
        if (!navbarCollapsible) {
            return;
        }

        // Evalúa si el usuario está en el punto de origen (Top: 0)
        if (window.scrollY === 0) {
            // Remueve la clase de contracción para mostrar el estado original (transparente/amplio)
            navbarCollapsible.classList.remove('navbar-shrink')
        } else {
            // Aplica la clase de contracción para mejorar la legibilidad sobre el contenido
            navbarCollapsible.classList.add('navbar-shrink')
        }
    };

    /**
     * INICIALIZACIÓN Y REGISTRO DE EVENTOS
     */
    // Ejecución inmediata para corregir el estado al recargar la página
    navbarShrink();

    // Vinculación al evento de desplazamiento del documento
    document.addEventListener('scroll', navbarShrink);

    /**
     * 2. SUBSISTEMA DE SEGUIMIENTO (SCROLLSPY)
     * Sincroniza los enlaces de navegación con la sección visible en pantalla.
     */
    const mainNav = document.body.querySelector('#mainNav');
    if (mainNav) {
        new bootstrap.ScrollSpy(document.body, {
            target: '#mainNav',
            rootMargin: '0px 0px -40%', // Activa el cambio de estado antes de llegar al borde superior
        });
    };

    /**
     * 3. SUBSISTEMA DE AUTOCIERRE RESPONSIVO
     * Colapsa el menú desplegable automáticamente al seleccionar una sección en móviles.
     */
    const navbarToggler = document.body.querySelector('.navbar-toggler');
    
    // Transformación de NodeList a Array para manipulación funcional
    const responsiveNavItems = [].slice.call(
        document.querySelectorAll('#navbarResponsive .nav-link')
    );

    /**
     * ASIGNACIÓN DE EVENTOS A ENLACES
     * Cierra el menú hamburguesa solo si el botón 'toggler' es visible (Vista móvil).
     */
    responsiveNavItems.map(function (responsiveNavItem) {
        responsiveNavItem.addEventListener('click', () => {
            // Verifica el estado del display mediante el estilo computado
            if (window.getComputedStyle(navbarToggler).display !== 'none') {
                navbarToggler.click(); // Simula el clic para contraer el menú
            }
        });
    });

});