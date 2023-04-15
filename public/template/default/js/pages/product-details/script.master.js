"use strict";

/*Product details JS code [START]*/

jQuery(document).ready(function($) {



    var _prod_bot_gallery_ = new Swiper('div#prod-bot-gallery', {
        slidesPerView: 6,
        speed: 300,
        loop: true,
        spaceBetween: 10,
        freeMode: true,
        watchSlidesProgress: true,
        breakpoints:{
            320: {
                slidesPerView: 4,
                spaceBetween: 10
            },
            480: {
                slidesPerView: 4,
                spaceBetween: 10
            },
            640: {
                slidesPerView: 4,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 4,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 5,
                spaceBetween: 10
            }
        }
    });



});

/*Product details JS code [END]*/