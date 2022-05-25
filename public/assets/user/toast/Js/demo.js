$(function () {
    $("i.fas.fa-file-code").click(function () {
        var codepanel = $("<div class=\"panel-code animated fadeInRight\"></div>");
        var codepanel_bg = $("<div class=\"panel-code-bg\"></div>");
        codepanel_bg.click(function () {
            let bgobj = this;
            codepanel.removeClass("fadeInRight").addClass("fadeOutLeft").on("animationend webkitAnimationEnd", function () {
                $(this).remove();
            });
			codepanel_bg.addClass("animated fadeOut").on("animationend webkitAnimationEnd", function () { $(this).remove(); });
        });
        codepanel.append($(this).parents("li:first").find("div.codelist").children().clone());
        codepanel.find(".codelist").show();
        $(document.body).append(codepanel).append(codepanel_bg);
    });
});