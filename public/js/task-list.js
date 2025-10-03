// Auto-dismiss alert after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-custom');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'slideUp 0.4s ease forwards';
            setTimeout(() => {
                alert.remove();
            }, 400);
        }, 5000);
    });
});

// Slide up animation for alert removal
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }
`;
document.head.appendChild(style);

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add loading state to action buttons
document.querySelectorAll('.action-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const button = this.querySelector('.btn-action');
        button.disabled = true;
        button.style.opacity = '0.6';
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    });
});