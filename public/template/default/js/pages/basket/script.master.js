"use strict";

/*User cart page JS code [START]*/

jQuery(document).ready(function($) {
	$(document).on('click', '[data-qty-control]', function(event) {
		event.preventDefault();
		
		var qty_num = Number($(this).siblings("[data-count]").data('count'));
		var qty_dir = $(this).data("qty-control");

		if (qty_dir == "plus") {
			qty_num = (qty_num >= 100) ? 100 : (qty_num += 1);
		}
		else{
			qty_num = (qty_num <= 1) ? 1 : (qty_num -= 1);
		}

		$(this).siblings("[data-count]").data('count', qty_num).text(qty_num);
	});

    $(document).on('click', '[data-toggle="checkout-cart-items"]', function(event) {
        event.preventDefault();

        var cart = $(this).parent("div.checkout-basket");

        if (cart.hasClass("collapsed")) {
            cart.removeClass("collapsed").addClass("open");
        }
        
        else{
            cart.removeClass("open").addClass("collapsed");
        }

        cart.find("div.checkout-basket__body").slideToggle(150);
    });


	new Swiper('div#recommendations-slider', {
        slidesPerView: 5.5,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#recommendations-slider button.slider-btn.next',
            prevEl: 'div#recommendations-slider button.slider-btn.prev'
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
                slidesPerView: 3.5,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3.5,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 4.5,
                spaceBetween: 10
            },
            1200: {
                slidesPerView: 5.5,
                spaceBetween: 10
            },
            1400: {
                slidesPerView: 6,
                spaceBetween: 10
            }
        }
    });

    new Swiper('div#recently-viewed-slider', {
        slidesPerView: 5.5,
        speed: 1000,
        spaceBetween: 10,
        loop: false,
        navigation: {
            nextEl: 'div#recently-viewed-slider button.slider-btn.next',
            prevEl: 'div#recently-viewed-slider button.slider-btn.prev'
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
                slidesPerView: 3.5,
                spaceBetween: 10
            },
            768: {
                slidesPerView: 3.5,
                spaceBetween: 10
            },
            1024: {
                slidesPerView: 4.5,
                spaceBetween: 10
            },
            1200: {
                slidesPerView: 5.5,
                spaceBetween: 10
            },
            1400: {
                slidesPerView: 6,
                spaceBetween: 10
            }
        }
    });
});

/*User cart page JS code [END]*/