"use strict";

/*About us page JS code [START]*/

jQuery(document).ready(function($) {
    new Swiper('div#news-slider', {
        slidesPerView: 4.5,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#news-slider button.slider-btn.next',
            prevEl: 'div#news-slider button.slider-btn.prev'
        },
        breakpoints:{
            320: {
                slidesPerView: 1.1,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 2.1,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 3.1,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3.3,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 4.5,
                spaceBetween: 10
            }
        }
    });
});

/*About us page JS code [END]*/