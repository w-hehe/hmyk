(function () {

    var widgetName = 'map';

    UM.registerWidget(widgetName, {

        tpl: "<style type=\"text/css\">" +
            ".edui-dialog-map .edui-map-content{width:530px; height: 350px;margin: 10px auto;}" +
            ".edui-dialog-map .edui-map-content table{width: 100%}" +
            ".edui-dialog-map .edui-map-content table td{vertical-align: middle;}" +
            ".edui-dialog-map .edui-map-button { border: 1px solid #ccc; float: left; cursor: default; height: 23px; width: 70px; cursor: pointer; margin: 0; font-size: 12px; line-height: 24px; text-align: center; color:#444; }" +
            ".edui-dialog-map .edui-map-button:hover {background:#eee;}" +
            ".edui-dialog-map .edui-map-city,.edui-dialog-map .edui-map-address{height:21px;background: #FFF;border:1px solid #d7d7d7; line-height: 21px;}" +
            ".edui-dialog-map .edui-map-city{width:90px}" +
            ".edui-dialog-map .edui-map-address{width:150px}" +
            ".edui-dialog-map .edui-map-dynamic-label span{vertical-align:middle;margin: 3px 0px 3px 3px;}" +
            ".edui-dialog-map .edui-map-dynamic-label input{vertical-align:middle;margin: 3px;}" +
            "</style>" +
            "<div class=\"edui-map-content\">" +
            "<table>" +
            "<tr>" +
            "<td><%=lang_city%>:</td>" +
            "<td><input class=\"edui-map-city\" type=\"text\" value=\"<%=city.value%>\"/></td>" +
            "<td><%=lang_address%>:</td>" +
            "<td><input class=\"edui-map-address\" type=\"text\" value=\"\" /></td>" +
            "<td><a class=\"edui-map-button\"><%=lang_search%></a></td>" +
            "<td><label class=\"edui-map-dynamic-label\"><input class=\"edui-map-dynamic\" type=\"checkbox\" name=\"edui-map-dynamic\" /><span><%=lang_dynamicmap%></span></label></td>" +
            "</tr>" +
            "</table>" +
            "<div style=\"width:100%;height:340px;margin:5px auto;border:1px solid gray\" class=\"edui-map-container\"></div>" +
            "</div>" +
            "<script class=\"edui-tpl-container\" type=\"text/plain\">" +
            "<!DOCTYPE html>" +
            "<html>" +
            "<head>" +
            "<title></title>" +
            "</head>" +
            "<body>" +
            "<scr_ipt>" +
            "window.onload = function(){" +
            "var scripts = document.scripts || document.getElementsByTagName(\"script\")," +
            "src = [];" +
            "for( var i = 1, len = scripts.length; i<len; i++ ) {" +
            "src.push( scripts[i].src );" +
            "}" +
            "parent.UM.getEditor(<<id>>).getWidgetData(\'map\').requestMapApi( src );" +
            "};" +
            "function mapReadyStateChange ( state ) { " +
            " if ( state === 'complete' || state === 'loaded' ) {" +
            " document.close(); " +
            " } }" +
            "</scr_ipt>" +
            "<scr_ipt onreadystatechange='mapReadyStateChange(this.readyState);' onload='mapReadyStateChange(\"loaded\");' src=\"//api.map.baidu.com/api?v=2.0&ak=<%=key%>&services=true\"></scr_ipt>" +
            "</body>" +
            "</html>" +
            "</script>",
        initContent: function (editor, $widget) {
            if (!editor.options.baiduMapKey) {
                setTimeout(function () {
                    $widget.edui().hide();
                    alert("请在配置中配置百度地图API密钥");
                }, 10);
                return;
            }
            var me = this,
                lang = editor.getLang(widgetName),
                theme_url = editor.options.themePath + editor.options.theme;

            if (me.inited) {
                me.map.clearOverlays();
                var img = $(me.editor.selection.getRange().getClosedNode());
                if (img.length && /api[.]map[.]baidu[.]com/ig.test(img.attr("src"))) {
                    var url = img.attr("src"),
                        centerPos = me.getPars("center", url).split(","),
                        markerPos = me.getPars("markers", url).split(",");
                    var zoom = Number(me.getPars('zoom', url) || 11);
                    var point = new BMap.Point(Number(centerPos[0]), Number(centerPos[1]));
                    if (markerPos.length == 2) {
                        setTimeout(function () {
                            var marker = new BMap.Marker(new BMap.Point(Number(markerPos[0]), Number(markerPos[1])));
                            marker.enableDragging();
                            me.map.setCenter(point, zoom);
                            me.map.addOverlay(marker);
                        }, 100);
                    }
                    me.preventDefault();
                    return false;
                } else {
                    me.preventDefault();
                    return false;
                }
            }

            me.inited = true;

            me.lang = lang;
            me.editor = editor;

            me.root().html($.parseTmpl(me.tpl, $.extend({}, lang['static'], {
                'key': editor.options.baiduMapKey,
                'theme_url': theme_url
            })));

            me.initRequestApi();

        },
        /**
         * 初始化请求API
         */
        initRequestApi: function () {

            var $ifr = null;
            //已经初始化过， 不用再次初始化
            if (window.BMap && window.BMap.Map) {
                this.initBaiduMap();
            } else {
                $ifr = $('<iframe style="display: none;"></iframe>');
                $ifr.appendTo(this.root());

                $ifr = $ifr[0].contentWindow.document;

                $ifr.open();
                $ifr.write(this.root().find(".edui-tpl-container").html().replace(/scr_ipt/g, 'script').replace('<<id>>', "'" + this.editor.id + "'"));
            }

        },
        requestMapApi: function (src) {
            var me = this;
            if (src.length) {
                var _src = src[0];
                src = src.slice(1);
                if (_src) {
                    $.getScript(_src, function () {
                        me.requestMapApi(src);
                    });
                } else {
                    me.requestMapApi(src);
                }
            } else {
                me.initBaiduMap();
            }

        },
        initBaiduMap: function () {
            var $root = this.root(),
                map = new BMap.Map($root.find(".edui-map-container")[0]),
                me = this,
                marker,
                point,
                imgcss,
                img = $(me.editor.selection.getRange().getClosedNode());

            map.enableInertialDragging();
            map.enableScrollWheelZoom();
            map.enableContinuousZoom();

            var url = '';
            if (img.length && /api[.]map[.]baidu[.]com/ig.test(img.attr("src"))) {
                url = img.attr("src");
                imgcss = img.attr('style');
            }

            var centerParam = me.getPars("center", url) || me.editor.options.baiduMapCenter || '116.404362,39.904768';
            var markerParam = me.getPars("markers", url) || '';
            var zoom = Number(me.getPars('zoom', url) || 11);
            var centerPos = centerParam.replace(/[\s]+/g, '').split(",");
            var markerPos = markerParam ? markerParam.replace(/[\s]+/g, '').split(",") : [];

            point = new BMap.Point(Number(centerPos[0]), Number(centerPos[1]));
            map.addControl(new BMap.NavigationControl());
            map.centerAndZoom(point, zoom);

            if (markerPos.length == 2) {
                marker = new BMap.Marker(new BMap.Point(Number(markerPos[0]), Number(markerPos[1])));
                marker.enableDragging();
                map.addOverlay(marker);
            }
            map.addEventListener('click', function (e, type, target, point, overlay) {
                map.clearOverlays();
                me.marker = new BMap.Marker(e.point);
                map.addOverlay(me.marker);
            });

            me.map = map;
            me.marker = marker;
            me.imgcss = imgcss;
        },
        doSearch: function () {
            var me = this,
                city = me.root().find('.edui-map-city').val(),
                address = me.root().find('.edui-map-address').val();

            if (!city) {
                alert(me.lang.cityMsg);
                return;
            }
            var search = new BMap.LocalSearch(city, {
                onSearchComplete: function (results) {
                    if (results && results.getNumPois()) {
                        var points = [];
                        for (var i = 0; i < results.getCurrentNumPois(); i++) {
                            points.push(results.getPoi(i).point);
                        }
                        if (points.length > 1) {
                            me.map.setViewport(points);
                        } else {
                            me.map.centerAndZoom(points[0], 11);
                        }
                        point = me.map.getCenter();
                    } else {
                        alert(me.lang.errorMsg);
                    }
                }
            });
            search.search(address || city);
        },
        getPars: function (name, url) {
            url = url || location.href;
            return url.match(new RegExp('[?&]' + name + '=([^?&]+)', 'i')) ? decodeURIComponent(RegExp.$1) : '';
        },
        reset: function () {
            this.map && this.map.reset();
        },
        initEvent: function () {
            var me = this,
                $root = me.root();

            $root.find('.edui-map-address').on('keydown', function (evt) {
                evt = evt || event;
                if (evt.keyCode == 13) {
                    me.doSearch();
                    return false;
                }
            });

            $root.find(".edui-map-button").on('click', function (evt) {
                me.doSearch();
            });

            $root.find(".edui-map-address").focus();

            $root.on("mousewheel DOMMouseScroll", function (e) {
                return false;
            });

        },
        width: 580,
        height: 408,
        buttons: {
            ok: {
                exec: function (editor) {
                    var widget = editor.getWidgetData(widgetName),
                        center = widget.map.getCenter(),
                        zoom = widget.map.getZoom(),
                        size = widget.map.getSize(),
                        markerPoint = widget.marker ? widget.marker.point : null;
                    if (widget.root().find(".edui-map-dynamic")[0].checked) {

                        var URL = editor.getOpt('UMEDITOR_HOME_URL'),
                            url = [URL + (/\/$/.test(URL) ? '' : '/') + "dialogs/map/map.html" +
                            '?center=' + center.lng + ',' + center.lat,
                                '&zoom=' + zoom,
                                '&width=' + size.width,
                                '&height=' + size.height,
                                '&markers=' + (markerPoint ? markerPoint.lng + ',' + markerPoint.lat : '')].join('');
                        editor.execCommand('inserthtml', '<iframe class="ueditor_baidumap" src="' + url + '" frameborder="0" width="' + (size.width + 4) + '" height="' + (size.height + 4) + '"></iframe>', true);
                    } else {
                        url = "https://api.map.baidu.com/staticimage?center=" + center.lng + ',' + center.lat +
                            "&zoom=" + zoom + "&width=" + size.width + '&height=' + size.height + "&markers=" + (markerPoint ? markerPoint.lng + ',' + markerPoint.lat : '');
                        editor.execCommand('inserthtml', '<img width="' + size.width + '" height="' + size.height + '" src="' + url + '"' + (widget.imgcss ? ' style="' + widget.imgcss + '"' : '') + '/>', true);
                    }
                    try {
                        widget.reset();
                    } catch (e) {

                    }
                }
            },
            cancel: {
                exec: function (editor) {
                    try {
                        editor.getWidgetData(widgetName).reset();
                    } catch (e) {

                    }
                }
            }
        }
    }, function () {
        console.log(122);
    });

})();

