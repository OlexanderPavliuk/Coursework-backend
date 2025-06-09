document.addEventListener('DOMContentLoaded', () => {
    const body = document.getElementById('pageBody');
    const savedTheme = localStorage.getItem('selectedTheme') || 'soft-neon';
    if (body) {
        body.className = `min-h-screen flex items-center justify-center p-4 theme-${savedTheme}`;
    }
});