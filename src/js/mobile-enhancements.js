/**
 * Mobile Enhancements - NetoNerd ITSM v2.0
 * Funcionalidades JavaScript específicas para mobile
 */

(function() {
    'use strict';

    // Detecção de dispositivo
    const MobileDetector = {
        isMobile: function() {
            return window.innerWidth < 768;
        },
        isTouch: function() {
            return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        }
    };

    // Fix para altura em iOS
    const ViewportFix = {
        init: function() {
            this.setVh();
            window.addEventListener('resize', () => this.setVh());
        },
        setVh: function() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
    };

    // Tabelas responsivas
    const ResponsiveTables = {
        init: function() {
            if (!MobileDetector.isMobile()) return;
            const tables = document.querySelectorAll('.table-responsive-mobile table');
            tables.forEach(table => {
                const headers = [];
                const headerCells = table.querySelectorAll('thead th');
                headerCells.forEach(th => headers.push(th.textContent.trim()));
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        if (headers[index]) {
                            cell.setAttribute('data-label', headers[index]);
                        }
                    });
                });
            });
        }
    };

    // Scroll to top
    const ScrollToTop = {
        button: null,
        init: function() {
            this.createButton();
            this.bindEvents();
        },
        createButton: function() {
            if (document.querySelector('.scroll-to-top')) return;
            const btn = document.createElement('button');
            btn.className = 'scroll-to-top';
            btn.innerHTML = '↑';
            btn.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: #007bff;
                color: white;
                border: none;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                display: none;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 1000;
                font-size: 20px;
            `;
            document.body.appendChild(btn);
            this.button = btn;
        },
        bindEvents: function() {
            if (!this.button) return;
            window.addEventListener('scroll', () => {
                this.button.style.display = window.pageYOffset > 300 ? 'flex' : 'none';
            });
            this.button.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    };

    // Inicialização
    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
    }

    function initAll() {
        ViewportFix.init();
        ResponsiveTables.init();
        ScrollToTop.init();
    }

    init();
})();
