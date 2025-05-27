import Splide from '@splidejs/splide';


console.log('home')


document.addEventListener('turbo:load', function () {
    const element = document.querySelector('#produit-carousel');
    if (element && !element.classList.contains('is-initialized')) {
        new Splide(element, {
            type: 'loop',
            perPage: 1,
            gap: '1rem',
            autoplay: true,
            breakpoints: {
                768: { perPage: 1 },
                1024: { perPage: 2 }
            }
        }).mount();
    }
});
