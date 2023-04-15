"use strict";

/*Homepage JS code [START]*/

jQuery(document).ready(function($) {
    new Swiper('div#hero-slider', {
        slidesPerView: 1.4,
        speed: 500,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#hero-slider button.slider-btn.next',
            prevEl: 'div#hero-slider button.slider-btn.prev'
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
                slidesPerView: 1.2,
                spaceBetween: 10
            }
        }
    });

    new Swiper('div#promos-slider', {
        slidesPerView: 2.5,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#promos-slider button.slider-btn.next',
            prevEl: 'div#promos-slider button.slider-btn.prev'
        },
        breakpoints:{
            320: {
                slidesPerView: 2,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 2,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 2.5,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 10
            }
        }
    });

    new Swiper('div#blog-slider', {
        slidesPerView: 6,
        speed: 1000,
        spaceBetween: 10,
        loop: true,
        navigation: {
            nextEl: 'div#blog-slider button.slider-btn.next',
            prevEl: 'div#blog-slider button.slider-btn.prev'
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
                slidesPerView: 6,
                spaceBetween: 10
            }
        }
    });

    new Swiper('div#brands-slider', {
        slidesPerView: 8,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#brands-slider button.slider-btn.next',
            prevEl: 'div#brands-slider button.slider-btn.prev'
        },
        breakpoints:{
            0: {
                slidesPerView: 2,
                spaceBetween: 10
            },
            320: {
                slidesPerView: 2,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 3,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 3,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 10
            },
            1400: {
                slidesPerView: 6,
                spaceBetween: 10
            }
        }
    });
});

/*Homepage JS code [END]*/
