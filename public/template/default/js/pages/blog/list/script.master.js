"use strict";

/*Blog posts (Posts list) [START]*/
$(document).ready(function() {
    new Swiper('div#blog-slider', {
        slidesPerView: 1.4,
        speed: 500,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#blog-slider button.slider-btn.next',
            prevEl: 'div#blog-slider button.slider-btn.prev'
        },
        autoplay: {
            delay: 5000
        },
        breakpoints:{
            0: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            320: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 1,
                spaceBetween: 10
            }
        }
    });
});
/*Blog posts (Posts list) [END]*/