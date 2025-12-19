// Chặn F12, Ctrl+Shift+I/C/J/U, Cmd+Option+I/C/J/U, Cmd+Shift+I/C/J/U và chuột phải
document.addEventListener('keydown', function(e) {
    // F12
    if (e.key === 'F12' || e.keyCode === 123) {
        e.preventDefault();
        return false;
    }
    // Ctrl/Cmd + Shift/Option + I/C/J/U
    if ((e.ctrlKey || e.metaKey) && (e.shiftKey || e.altKey)) {
        if (['I', 'C', 'J', 'U'].includes(e.key.toUpperCase())) {
            e.preventDefault();
            return false;
        }
    }
    // Ctrl/Cmd + U
    if ((e.ctrlKey || e.metaKey) && e.key.toUpperCase() === 'U') {
        e.preventDefault();
        return false;
    }
});
// Chặn riêng tổ hợp Command + Option + I trên Mac
window.addEventListener('keydown', function(e) {
    // Command (metaKey) + Option (altKey) + I
    if (e.metaKey && e.altKey && e.key.toUpperCase() === 'I') {
        e.preventDefault();
        return false;
    }
});
// Chặn chuột phải
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    return false;
});
