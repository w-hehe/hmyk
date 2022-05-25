





bui.input({
    id: ".password-input",
    iconClass: ".icon-eye",
    callback: function(e) {
        //切换类型
        this.toggleType();
        //
        $(e.target).toggleClass("active")
    }
})