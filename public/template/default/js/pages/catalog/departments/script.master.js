"use strict";

/*Products catalog L1 JS code [START]*/

jQuery(document).ready(function($) {

    $('[data-toggle="catalog-sidebar"]').on("click", function() {
        $(this).parent("div.catalog-department__navigation").toggleClass("open");
    });

	new Swiper('div#categories-slider-1', {
        slidesPerView: 4.5,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#categories-slider-1 button.slider-btn.next',
            prevEl: 'div#categories-slider-1 button.slider-btn.prev'
        },
        breakpoints:{
            320: {
                slidesPerView: 2.2,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 2.5,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 2.5,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3.5,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 4.5,
                spaceBetween: 10
            }
        }
    });

    new Swiper('div#categories-slider-2', {
        slidesPerView: 4.5,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#categories-slider-2 button.slider-btn.next',
            prevEl: 'div#categories-slider-2 button.slider-btn.prev'
        },
        breakpoints:{
            320: {
                slidesPerView: 2.2,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 2.5,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 2.5,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3.5,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 4.5,
                spaceBetween: 10
            }
        }
    });

    new Swiper('div#categories-slider-3', {
        slidesPerView: 4.5,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#categories-slider-3 button.slider-btn.next',
            prevEl: 'div#categories-slider-3 button.slider-btn.prev'
        },
        breakpoints:{
            320: {
                slidesPerView: 2.2,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 2.5,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 2.5,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3.5,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 4.5,
                spaceBetween: 10
            }
        }
    });

    new Swiper('div#categories-slider-4', {
        slidesPerView: 4.5,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#categories-slider-4 button.slider-btn.next',
            prevEl: 'div#categories-slider-4 button.slider-btn.prev'
        },
        breakpoints:{
            320: {
                slidesPerView: 2.2,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 2.5,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 2.5,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3.5,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 4.5,
                spaceBetween: 10
            }
        }
    });

    new Swiper('div#brands-slider', {
        slidesPerView: 8.5,
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
                slidesPerView: 4,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 5.5,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 5.5,
                spaceBetween: 10
            }
        }
    });
});

/*Products catalog L1 JS code [END]*/