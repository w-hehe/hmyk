(function () {
    var domUtils = UM.dom.domUtils;
    var widgetName = 'video';

    UM.registerWidget(widgetName, {

        tpl: "<link rel=\"stylesheet\" type=\"text/css\" href=\"<%=video_url%>video.css\" />" +
            "<div class=\"edui-video-wrapper\">" +
            "<div id=\"eduiVideoTab\">" +
            "<div id=\"eduiVideoTabBodys\" class=\"edui-video-tabbody\">" +
            "<div id=\"eduiVideoPanel\" class=\"edui-video-panel\">" +
            "<table id='edui-video-input'><tr><td><label for=\"eduiVideoUrl\" class=\"edui-video-url\"><%=lang_video_url%></label></td><td><input id=\"eduiVideoUrl\" type=\"text\"></td><td><div class='video-btn-container'><input type='file' name='videofile' accept='video/mp4, video/webm, application/octet-stream, .mp4, .webm, .flv' id='uploadvideo'><p class='edui-btn edui-btn-primary'>上传视频</p></div></td><td><a href='javascript:' id='choosevideo' class='edui-btn edui-btn-primary'>选择视频</a></td></tr></table>" +
            "<div id=\"eduiVideoPreview\"></div>" +
            "<div id=\"eduiVideoInfo\">" +
            "<fieldset>" +
            "<legend style='font-size:14px;width:auto;border-bottom:none;padding:0 5px;margin-bottom:5px;'><%=lang_video_size%></legend>" +
            "<table>" +
            "<tr><td><label for=\"eduiVideoWidth\"><%=lang_videoW%></label></td><td><input class=\"edui-video-txt\" id=\"eduiVideoWidth\" type=\"text\"/></td></tr>" +
            "<tr><td><label for=\"eduiVideoHeight\"><%=lang_videoH%></label></td><td><input class=\"edui-video-txt\" id=\"eduiVideoHeight\" type=\"text\"/></td></tr>" +
            "</table>" +
            "</fieldset>" +
            "<fieldset>" +
            "<legend style='font-size:14px;width:auto;border-bottom:none;padding:0 5px;margin-bottom:5px;'><%=lang_alignment%></legend>" +
            "<div id=\"eduiVideoFloat\"></div>" +
            "</fieldset>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</div>",
        initContent: function (editor, $widget) {

            var me = this,
                lang = editor.getLang(widgetName),
                video_url = UMEDITOR_CONFIG.UMEDITOR_HOME_URL + 'dialogs/video/';

            me.lang = lang;
            me.editor = editor;
            me.$widget = $widget;
            me.root().html($.parseTmpl(me.tpl, $.extend({video_url: video_url}, lang['static'])));

            me.initController(lang);

        },
        initEvent: function (editor, $w) {

            var me = this,
                url = $("#eduiVideoUrl", me.$widget)[0];

            if ('oninput' in url) {
                url.oninput = function () {
                    me.createPreviewVideo(this.value);
                };
            } else {
                url.onpropertychange = function () {
                    me.createPreviewVideo(this.value);
                }
            }
            me.editor = editor;
            me.dialog = $w;

            $(me.dialog).delegate("#choosevideo", "click", function (e) {
                var selectUrl = typeof Config !== 'undefined' && Config.modulename === 'index' ? 'user/attachment' : 'general/attachment/select';
                parent.Fast.api.open(selectUrl + "?element_id=&multiple=false&mimetype=video/*", "选择", {
                    callback: function (data) {
                        var fullurl = Fast.api.cdnurl(data.url, true);
                        $("#eduiVideoUrl").val(fullurl);
                        me.createPreviewVideo(fullurl);
                    }
                });
                return false;
            });
            $(me.dialog).delegate("#uploadvideo", "change", function (e) {
                var files = $(this).prop('files');
                if (files.length > 0) {
                    var uploadCallback = me.editor.getOpt('imageUploadCallback');
                    uploadCallback.call(me, files[0], function (url, data) {
                        $("#eduiVideoUrl").val(data.fullurl);
                        me.createPreviewVideo(data.fullurl);
                    });
                }
                return;
            });

        },
        initController: function (lang) {

            var me = this,
                img = me.editor.selection.getRange().getClosedNode(),
                url;

            me.createAlignButton(["eduiVideoFloat"]);

            //编辑视频时初始化相关信息
            if (img && img.className == "edui-faked-video") {
                $("#eduiVideoUrl", me.$widget)[0].value = url = img.getAttribute("_url");
                $("#eduiVideoWidth", me.$widget)[0].value = img.width;
                $("#eduiVideoHeight", me.$widget)[0].value = img.height;
                var align = domUtils.getComputedStyle(img, "float"),
                    parentAlign = domUtils.getComputedStyle(img.parentNode, "text-align");
                me.updateAlignButton(parentAlign === "center" ? "center" : align);
            }
            me.createPreviewVideo(url);

        },
        /**
         * 根据url生成视频预览
         */
        createPreviewVideo: function (url) {

            if (!url) return;

            var me = this,
                lang = me.lang,
                conUrl = me.convert_url(url);

            $("#eduiVideoPreview", me.$widget)[0].innerHTML = '<video ' +
                ' src="' + url + '"' +
                ' width="' + 420 + '"' +
                ' height="' + 280 + '"' +
                ' controls autoplay preload="auto"></video><br>';

        },
        /**
         * 将单个视频信息插入编辑器中
         */
        insertSingle: function () {

            var me = this,
                width = $("#eduiVideoWidth", me.$widget)[0],
                height = $("#eduiVideoHeight", me.$widget)[0],
                url = $('#eduiVideoUrl', me.$widget)[0].value,
                align = this.findFocus("eduiVideoFloat", "name");

            if (!url) return false;
            if (!me.checkNum([width, height])) return false;
            this.editor.execCommand('insertvideo', {
                url: me.convert_url(url),
                width: width.value,
                height: height.value,
                align: align
            });

        },
        /**
         * URL转换
         */
        convert_url: function (url) {
            if (!url) return '';
            return url;
        },
        /**
         * 检测传入的所有input框中输入的长宽是否是正数
         */
        checkNum: function checkNum(nodes) {

            var me = this;

            for (var i = 0, ci; ci = nodes[i++];) {
                var value = ci.value;
                if (!me.isNumber(value) && value) {
                    alert(me.lang.numError);
                    ci.value = "";
                    ci.focus();
                    return false;
                }
            }
            return true;
        },
        /**
         * 数字判断
         * @param value
         */
        isNumber: function (value) {
            return /(0|^[1-9]\d*$)/.test(value);
        },
        updateAlignButton: function (align) {
            var aligns = $("#eduiVideoFloat", this.$widget)[0].children;

            for (var i = 0, ci; ci = aligns[i++];) {
                if (ci.getAttribute("name") == align) {
                    if (ci.className != "edui-video-focus") {
                        ci.className = "edui-video-focus";
                    }
                } else {
                    if (ci.className == "edui-video-focus") {
                        ci.className = "";
                    }
                }
            }

        },
        /**
         * 创建图片浮动选择按钮
         * @param ids
         */
        createAlignButton: function (ids) {
            var lang = this.lang,
                vidoe_home = UMEDITOR_CONFIG.UMEDITOR_HOME_URL + 'dialogs/video/';

            for (var i = 0, ci; ci = ids[i++];) {
                var floatContainer = $("#" + ci, this.$widget) [0],
                    nameMaps = {"none": lang['default'], "left": lang.floatLeft, "right": lang.floatRight};
                for (var j in nameMaps) {
                    var div = document.createElement("div");
                    div.setAttribute("name", j);
                    if (j == "none") div.className = "edui-video-focus";
                    div.style.cssText = "background:url(" + vidoe_home + "images/" + j + "_focus.jpg);";
                    div.setAttribute("title", nameMaps[j]);
                    floatContainer.appendChild(div);
                }
                this.switchSelect(ci);
            }
        },
        /**
         * 选择切换
         */
        switchSelect: function (selectParentId) {
            var selects = $("#" + selectParentId, this.$widget)[0].children;
            for (var i = 0, ci; ci = selects[i++];) {
                $(ci).on("click", function () {
                    for (var j = 0, cj; cj = selects[j++];) {
                        cj.className = "";
                        cj.removeAttribute && cj.removeAttribute("class");
                    }
                    this.className = "edui-video-focus";
                })
            }
        },
        /**
         * 找到id下具有focus类的节点并返回该节点下的某个属性
         * @param id
         * @param returnProperty
         */
        findFocus: function (id, returnProperty) {
            var tabs = $("#" + id, this.$widget)[0].children,
                property;
            for (var i = 0, ci; ci = tabs[i++];) {
                if (ci.className == "edui-video-focus") {
                    property = ci.getAttribute(returnProperty);
                    break;
                }
            }
            return property;
        },
        /**
         * 末尾字符检测
         */
        endWith: function (str, endStrArr) {
            for (var i = 0, len = endStrArr.length; i < len; i++) {
                var tmp = endStrArr[i];
                if (str.length - tmp.length < 0) return false;

                if (str.substring(str.length - tmp.length) == tmp) {
                    return true;
                }
            }
            return false;
        },
        width: 610,
        height: 358,
        buttons: {
            ok: {
                exec: function (editor, $w) {
                    $("#eduiVideoPreview", $w).html("");
                    editor.getWidgetData(widgetName).insertSingle();
                }
            },
            cancel: {
                exec: function () {
                    //清除视频
                    $("#eduiVideoPreview").html("");
                }
            }
        }
    });

})();
