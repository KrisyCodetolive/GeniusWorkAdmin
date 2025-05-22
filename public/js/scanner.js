document.addEventListener('DOMContentLoaded', function() {
    const scannerInput = document.getElementById('scanner-input');
    const scanDebug = document.getElementById('scan-debug');
    const scanValue = document.getElementById('scan-value');

    // Toggle debug display
    const debugMode = true; // Set to false to hide debug display in production
    

    // Listen for input changes
    scannerInput.addEventListener('input', function(e) {
        if (scanValue) {
            scanValue.textContent = e.target.value;
        }
    });

    // Listen for form submission or enter key
    scannerInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (scanValue) {
                scanValue.textContent = e.target.value;
            }
            // Clear the input after processing
            setTimeout(() => {
                e.target.value = '';
            }, 100);
        }
    });
});
