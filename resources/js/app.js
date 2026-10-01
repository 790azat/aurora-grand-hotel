// Theme: persisted in localStorage; applied early in <head> to avoid a flash.
window.toggleTheme = () => {
    const dark = document.documentElement.classList.toggle('dark');
    try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}
};

// Graceful fallback for remote demo images that fail to load.
document.addEventListener('error', (e) => {
    const img = e.target;
    if (img.tagName === 'IMG' && !img.dataset.fallback) {
        img.dataset.fallback = '1';
        img.removeAttribute('srcset');
        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1b2633"/><stop offset=".6" stop-color="#3d2f19"/><stop offset="1" stop-color="#b8914a"/></linearGradient></defs><rect width="800" height="600" fill="url(#g)"/><text x="400" y="310" font-family="Georgia,serif" font-size="34" fill="#e7d3a6" text-anchor="middle" letter-spacing="6">AURORA GRAND</text></svg>'
        );
    }
}, true);
