"use strict";

jQuery(document).ready(function($) {
    $('[data-toggle="tooltip"]').tooltip({
        delay: 500,
        placement: "top"
    });



    if ($('.nice-select').length && $.fn.niceSelect != undefined) {
        $('.nice-select').niceSelect();
    }

    if ($('.venobox').length && $.fn.venobox != undefined) {
        $('.venobox').venobox();
    }

    $('[data-toggle="dropdown"]').dropdown();

    $('[data-toggle="modal"]').on('click', function(event) {
        event.preventDefault();
        $($(this).data('target')).modal("show");
    });

    $('[data-dismiss="modal"').on('click', function(event) {
        event.preventDefault();
        $("div.modal").modal("hide");
    });

    $('[data-form-ctrl="password"]').on('click', function(event) {
        event.preventDefault();
        
        if ($(this).hasClass('vctrl-visible')) {
            $(this).removeClass('vctrl-visible').addClass('vctrl-hidden');
            $(this).siblings('input[type="password"]').attr("type", "text");
        }
        else{
            $(this).removeClass('vctrl-hidden').addClass('vctrl-visible');
            $(this).siblings('input[type="text"]').attr("type", "password");
        }
    });

    $("button#support-chat-button").on('click', function(event) {
        event.preventDefault();
        
        if ($(this).hasClass("chat-button_closed")) {
            $(this).removeClass("chat-button_closed").addClass("chat-button_opened");

            $("div#bottom-chat-options").slideDown(300);
        }
        else{
            $(this).removeClass("chat-button_opened").addClass("chat-button_closed");
            $("div#bottom-chat-options").slideUp(300);
        }
    });

    $("header#main-nav-header").find("button#catgmenu-btn").on('click', function(event) {
        event.preventDefault();
        $("header#main-nav-header").toggleClass("catgmenu-open");


        if ($(this).hasClass("catalog-btn__closed")) {
            $(this).removeClass("catalog-btn__closed").addClass("catalog-btn__open");
        }
        else{
            $(this).removeClass("catalog-btn__open").addClass("catalog-btn__closed");
        }
    });

    $(window).bind('load', function() {
        if ($(this).width() < 767) {
            $('div.footer-link-group div.accordion-collapse').collapse('hide');
        }

        else {
            $('div.footer-link-group div.accordion-collapse').collapse('show');
        }
    });

    /*$(window).on('scroll', function(event) {
        event.preventDefault();
        
        if($(window).scrollTop() >= 40) {
            $("header#main-nav-header").addClass("fixed-top-header");
            $("body").addClass("fixed-top-header");
        }
        else{
            $("header#main-nav-header").removeClass("fixed-top-header");
            $("body").removeClass("fixed-top-header");
        }
    });*/

    $(document).on('focus', 'input.form-control', function(event) {
        event.preventDefault();
        
        if ($(this).parent("div.form-field").length) {
            $(this).parent("div.form-field").addClass("focused-input");
        }
    });

    $(document).on('blur', 'input.form-control', function(event) {
        event.preventDefault();
        
        if ($(this).parent("div.form-field").length) {
            $(this).parent("div.form-field").removeClass("focused-input");
        }
    });

    new Swiper('div#catgmenu-brands-slider', {
        slidesPerView: 8.5,
        speed: 1000,
        spaceBetween: 20,
        loop: false,
        breakpoints:{
            0: {
                slidesPerView: 2,
                spaceBetween: 15
            },
            320: {
                slidesPerView: 2,
                spaceBetween: 15
            },
            480: {
                slidesPerView: 3,
                spaceBetween: 15
            },
            640: {
                slidesPerView: 3,
                spaceBetween: 15
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 15
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 15
            },
            1400: {
                slidesPerView: 8,
                spaceBetween: 15
            }
        }
    });
});