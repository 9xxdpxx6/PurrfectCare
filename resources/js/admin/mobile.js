// JavaScript для мобильной адаптации админ-панели
document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.getElementById('sidebarMenu');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 && sidebar) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        }
    });
    
    // Дополнительные улучшения для мобильных устройств
    // Улучшение отображения таблиц на мобильных
    const tables = document.querySelectorAll('.table-responsive table');
    tables.forEach(table => {
        // Добавляем класс для лучшего отображения на мобильных
        if (window.innerWidth <= 768) {
            table.classList.add('table-sm');
        }
    });
    
    // Адаптация графиков на мобильных устройствах
    const canvases = document.querySelectorAll('canvas');
    canvases.forEach(canvas => {
        if (window.innerWidth <= 768) {
            canvas.style.maxHeight = '200px';
        }
    });
    
    // Обработка изменения размера окна
    window.addEventListener('resize', function() {
        const isMobile = window.innerWidth <= 768;
        const isSmallMobile = window.innerWidth <= 480;
        
        tables.forEach(table => {
            if (isMobile) {
                table.classList.add('table-sm');
            } else {
                table.classList.remove('table-sm');
            }
        });
        
        canvases.forEach(canvas => {
            if (isMobile) {
                canvas.style.maxHeight = '200px';
            } else {
                canvas.style.maxHeight = '';
            }
        });
    });
});
