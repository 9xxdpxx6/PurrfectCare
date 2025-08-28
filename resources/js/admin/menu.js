// JavaScript для управления меню админ-панели
document.addEventListener('DOMContentLoaded', function() {
    // Load saved collapse states first
    function loadSavedMenuStates() {
        // Set flag to prevent saving during initial load
        window.isLoadingMenuState = true;
        
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(link => {
            const arrow = link.querySelector('.collapse-arrow');
            const targetId = link.getAttribute('data-bs-target');
            const target = document.querySelector(targetId);
            
            // Check if elements exist
            if (!arrow || !target) {
                return;
            }
            
            // Load saved state from localStorage
            const savedState = localStorage.getItem(`menu_${targetId}`);
            if (savedState === 'collapsed') {
                target.classList.remove('show');
                link.setAttribute('aria-expanded', 'false');
                arrow.classList.remove('rotated'); // Стрелка вправо для свернутого
            } else if (savedState === 'expanded') {
                target.classList.add('show');
                link.setAttribute('aria-expanded', 'true');
                arrow.classList.add('rotated'); // Стрелка вниз для развернутого
            }
            
            // Set initial state - if expanded, arrow should point down
            if (target.classList.contains('show')) {
                arrow.classList.add('rotated'); // Стрелка вниз для развернутого
            } else {
                arrow.classList.remove('rotated'); // Стрелка вправо для свернутого
            }
        });
        
        // Clear flag after loading
        setTimeout(() => {
            window.isLoadingMenuState = false;
        }, 500);
    }

    // Auto-expand sections with active items
    function expandActiveSections() {
        const activeLinks = document.querySelectorAll('.sidebar .nav-link.active');
        activeLinks.forEach(link => {
            const parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                const parentLink = document.querySelector(`[data-bs-target="#${parentCollapse.id}"]`);
                if (parentLink) {
                    // Always expand sections with active items, regardless of saved state
                    parentCollapse.classList.add('show');
                    parentLink.setAttribute('aria-expanded', 'true');
                    const arrow = parentLink.querySelector('.collapse-arrow');
                    if (arrow) {
                        arrow.classList.add('rotated'); // Point down when expanded
                    }
                    // Save the expanded state
                    localStorage.setItem(`menu_#${parentCollapse.id}`, 'expanded');
                }
            }
        });
    }

    // Load saved states first, then expand active sections
    loadSavedMenuStates();
    expandActiveSections();

    // Add event listeners for collapse state changes
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(link => {
        const arrow = link.querySelector('.collapse-arrow');
        const targetId = link.getAttribute('data-bs-target');
        const target = document.querySelector(targetId);
        
        if (!arrow || !target) {
            return;
        }
        
        // Listen for Bootstrap collapse events
        target.addEventListener('show.bs.collapse', function () {
            arrow.classList.add('rotated'); // Стрелка вниз для развернутого
            // Save state to localStorage only if not loading
            if (!window.isLoadingMenuState) {
                localStorage.setItem(`menu_${targetId}`, 'expanded');
            }
        });
        
        target.addEventListener('hide.bs.collapse', function () {
            arrow.classList.remove('rotated'); // Стрелка вправо для свернутого
            // Save state to localStorage only if not loading
            if (!window.isLoadingMenuState) {
                localStorage.setItem(`menu_${targetId}`, 'collapsed');
            }
        });
        
        // Use MutationObserver to watch for class changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class' && !window.isLoadingMenuState) {
                    const isExpanded = target.classList.contains('show');
                    const state = isExpanded ? 'expanded' : 'collapsed';
                    localStorage.setItem(`menu_${targetId}`, state);
                }
            });
        });
        
        // Start observing
        observer.observe(target, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        // Also listen for manual clicks on the toggle
        link.addEventListener('click', function() {
            setTimeout(() => {
                if (!window.isLoadingMenuState) {
                    const isExpanded = target.classList.contains('show');
                    const state = isExpanded ? 'expanded' : 'collapsed';
                    localStorage.setItem(`menu_${targetId}`, state);
                }
            }, 100);
        });
    });

    // Save and restore sidebar scroll position
    const sidebarElement = document.getElementById('sidebarMenu');
    if (sidebarElement) {
        // Restore scroll position on page load with a small delay
        const savedScrollTop = localStorage.getItem('sidebar_scroll_top');
        if (savedScrollTop) {
            // Use setTimeout to ensure DOM is fully loaded
            setTimeout(() => {
                sidebarElement.scrollTop = parseInt(savedScrollTop);
            }, 100);
        }
        
        // Save scroll position when scrolling
        sidebarElement.addEventListener('scroll', function() {
            localStorage.setItem('sidebar_scroll_top', sidebarElement.scrollTop);
        });
    }

    // Function to clear saved menu state (can be called when needed)
    window.clearMenuState = function() {
        // Clear all menu collapse states
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(link => {
            const targetId = link.getAttribute('data-bs-target');
            localStorage.removeItem(`menu_${targetId}`);
        });
        // Clear scroll position
        localStorage.removeItem('sidebar_scroll_top');
    };

    // Optional: Clear menu state on logout or user change
    // Uncomment the following line if you want to clear state on page unload
    // window.addEventListener('beforeunload', clearMenuState);
});
