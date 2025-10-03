// Auto-enable time input ketika tanggal dipilih
document.addEventListener('DOMContentLoaded', function() {
    const dueDateInput = document.getElementById('due_date');
    const dueTimeInput = document.getElementById('due_time');
    
    if (dueDateInput && dueTimeInput) {
        dueDateInput.addEventListener('change', function() {
            if (this.value && !dueTimeInput.value) {
                dueTimeInput.focus();
            }
        });
        
        // Add smooth animation when focused
        const allInputs = document.querySelectorAll('.form-input-custom, .form-textarea-custom');
        allInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.01)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    }
});