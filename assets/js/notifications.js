// notifications.js - initialize Toastify and helpers
export function initNotifications(){
    // Placeholder - can wire global events here
}

export function showToast(message, type='info'){
    const bg = type === 'success' ? '#22C55E' : (type === 'error' ? '#EF4444' : '#2563EB');
    if (window.Toastify) {
        Toastify({text: message, duration: 4000, gravity: 'top', position: 'right', backgroundColor: bg}).showToast();
        return;
    }
    // Fallback
    const safe = String(message);
    alert(safe);
}
