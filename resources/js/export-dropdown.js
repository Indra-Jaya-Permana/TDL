/**
 * Export Dropdown Toggle
 * Menangani buka/tutup dropdown menu export
 */

document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('exportBtn');
    const exportMenu = document.getElementById('exportMenu');
    
    if (!exportBtn || !exportMenu) {
        console.warn('Export button or menu not found');
        return;
    }

    // Toggle dropdown ketika klik tombol export
    exportBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        // Toggle class 'show' pada menu
        exportMenu.classList.toggle('show');
        
        // Toggle class 'active' pada button (untuk rotasi chevron)
        exportBtn.classList.toggle('active');
    });

    // Tutup dropdown ketika klik di luar
    document.addEventListener('click', function(e) {
        if (!exportBtn.contains(e.target) && !exportMenu.contains(e.target)) {
            exportMenu.classList.remove('show');
            exportBtn.classList.remove('active');
        }
    });

    // Tutup dropdown ketika salah satu item diklik
    const exportItems = exportMenu.querySelectorAll('.export-item');
    exportItems.forEach(item => {
        item.addEventListener('click', function() {
            exportMenu.classList.remove('show');
            exportBtn.classList.remove('active');
        });
    });

    // Tutup dropdown dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && exportMenu.classList.contains('show')) {
            exportMenu.classList.remove('show');
            exportBtn.classList.remove('active');
        }
    });
});