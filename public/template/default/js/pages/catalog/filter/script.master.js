"use strict";

/*Products catalog Filter JS code [START]*/

jQuery(document).ready(function($) {
    $('[data-toggle="catalog-sidebar"]').on("click", function() {
        $(this).parent("div.catalog-listing__filter").toggleClass("open");
    });

    new Swiper('div#popular-offers', {
        slidesPerView: 4.5,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#popular-offers button.slider-btn.next',
            prevEl: 'div#popular-offers button.slider-btn.prev'
        },
        breakpoints:{
            320: {
                slidesPerView: 1.5,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 2,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 2.2,
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
});

/*Products catalog Filter JS code [END]*/