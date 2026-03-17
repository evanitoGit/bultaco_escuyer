window.addEventListener('scroll', () => {
    const nav = document.querySelector('.nav');
    if (window.scrollY > 600) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});