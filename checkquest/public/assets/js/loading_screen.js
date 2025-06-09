/** ==================== 🚀 Loading Screen ==================== **/
window.addEventListener('load', () => {
    const loader = document.getElementById('loading-screen');
    if (!loader) return;

    const skip = sessionStorage.getItem('skipLoader');
    if (skip === 'true') {
        loader.remove();
        sessionStorage.removeItem('skipLoader');
        return;
    }

    // стандартний показ з затримкою
    setTimeout(() => {
        loader.classList.add('opacity-0');
        setTimeout(() => loader.remove(), 300);
    }, 300);
});