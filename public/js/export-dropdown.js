/**
 * Export Dropdown Functionality
 * File: public/js/export-dropdown.js
 * Handles the export dropdown menu interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Get elements
    const exportBtn = document.getElementById('exportBtn');
    const exportDropdown = exportBtn?.closest('.export-dropdown');
    const exportMenu = document.getElementById('exportMenu');

    if (exportBtn && exportDropdown) {
        
        // Toggle dropdown on button click
        exportBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            exportDropdown.classList.toggle('active');
            
            // Close other dropdowns if any
            document.querySelectorAll('.export-dropdown').forEach(dropdown => {
                if (dropdown !== exportDropdown) {
                    dropdown.classList.remove('active');
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!exportDropdown.contains(e.target)) {
                exportDropdown.classList.remove('active');
            }
        });

        // Close dropdown when clicking export item
        const exportItems = document.querySelectorAll('.export-item');
        exportItems.forEach(item => {
            item.addEventListener('click', function() {
                // Show loading indicator
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-spinner fa-spin';
                
                // Close dropdown after short delay
                setTimeout(() => {
                    exportDropdown.classList.remove('active');
                    icon.className = originalClass;
                }, 1000);
            });
        });

        // Close dropdown on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                exportDropdown.classList.remove('active');
            }
        });

        // Prevent dropdown from closing when clicking inside menu
        exportMenu?.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});