"use strict";

/*User profile page JS code [START]*/

jQuery(document).ready(function($) {
	if ($("div[data-profile-page]").length) {
		$('a[data-navmenu-li="'+$("div[data-profile-page]").data("profile-page")+'"]').addClass("active");
	}
});

/*User profile page JS code [END]*/