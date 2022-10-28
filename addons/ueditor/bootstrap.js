window.UEDITOR_HOME_URL = Config.__CDN__ + "/assets/addons/ueditor/";
require.config({
    paths: {
        'ueditor.config': '../addons/ueditor/ueditor.config',
        'ueditor': '../addons/ueditor/ueditor.all.min',
        'ueditor.zh': '../addons/ueditor/lang/zh-cn/zh-cn',
        'zeroclipboard': '../addons/ueditor/third-party/zeroclipboard/ZeroClipboard.min',
    },
    shim: {
        'ueditor': {
            deps: ['zeroclipboard', 'ueditor.config'],
            exports: 'UE',
            init: function (ZeroClipboard) {
                //导出到全局变量，供ueditor使用
                window.ZeroClipboard = ZeroClipboard;
            },
        },
        'ueditor.zh': ['ueditor']
    }
});
require(['form', 'upload', 'ueditor', 'ueditor.zh'], function (Form, Upload, UE, undefined) {
    UE.plugin.register('simpleupload', function () {
        var me = this,
            isLoaded = false,
            containerBtn;

        function initUploadBtn() {
            var w = containerBtn.offsetWidth || 20,
                h = containerBtn.offsetHeight || 20,
                btnIframe = document.createElement('iframe'),
                btnStyle = 'display:block;width:' + w + 'px;height:' + h + 'px;overflow:hidden;border:0;margin:0;padding:0;position:absolute;top:0;left:0;filter:alpha(opacity=0);-moz-opacity:0;-khtml-opacity: 0;opacity: 0;cursor:pointer;';

            UE.dom.domUtils.on(btnIframe, 'load', function () {

                var timestrap = (+new Date()).toString(36),
                    wrapper,
                    btnIframeDoc,
                    btnIframeBody;

                btnIframeDoc = (btnIframe.contentDocument || btnIframe.contentWindow.document);
                btnIframeBody = btnIframeDoc.body;
                wrapper = btnIframeDoc.createElement('div');

                wrapper.innerHTML = '<form id="edui_form_' + timestrap + '" target="edui_iframe_' + timestrap + '" method="POST" enctype="multipart/form-data" action="' + me.getOpt('serverUrl') + '" ' +
                    'style="' + btnStyle + '">' +
                    '<input id="edui_input_' + timestrap + '" type="file" accept="image/*" name="' + me.options.imageFieldName + '" ' +
                    'style="' + btnStyle + '">' +
                    '</form>' +
                    '<iframe id="edui_iframe_' + timestrap + '" name="edui_iframe_' + timestrap + '" style="display:none;width:0;height:0;border:0;margin:0;padding:0;position:absolute;"></iframe>';

                wrapper.className = 'edui-' + me.options.theme;
                wrapper.id = me.ui.id + '_iframeupload';
                btnIframeBody.style.cssText = btnStyle;
                btnIframeBody.style.width = w + 'px';
                btnIframeBody.style.height = h + 'px';
                btnIframeBody.appendChild(wrapper);

                if (btnIframeBody.parentNode) {
                    btnIframeBody.parentNode.style.width = w + 'px';
                    btnIframeBody.parentNode.style.height = w + 'px';
                }

                var form = btnIframeDoc.getElementById('edui_form_' + timestrap);
                var input = btnIframeDoc.getElementById('edui_input_' + timestrap);
                var iframe = btnIframeDoc.getElementById('edui_iframe_' + timestrap);

                UE.dom.domUtils.on(input, 'change', function () {
                    if (!input.value) return;
                    var loadingId = 'loading_' + (+new Date()).toString(36);
                    var params = UE.utils.serializeParam(me.queryCommandValue('serverparam')) || '';

                    var imageActionUrl = me.getActionUrl(me.getOpt('imageActionName'));
                    var allowFiles = me.getOpt('imageAllowFiles');

                    me.focus();
                    me.execCommand('inserthtml', '<img class="loadingclass" id="' + loadingId + '" src="' + me.options.themePath + me.options.theme + '/images/spacer.gif" title="' + (me.getLang('simpleupload.loading') || '') + '" >');

                    function showErrorLoader(title) {
                        if (loadingId) {
                            var loader = me.document.getElementById(loadingId);
                            loader && UE.dom.domUtils.remove(loader);
                            me.fireEvent('showmessage', {
                                'id': loadingId,
                                'content': title,
                                'type': 'error',
                                'timeout': 4000
                            });
                        }
                    }

                    // 判断文件格式是否错误
                    var filename = input.value,
                        fileext = filename ? filename.substr(filename.lastIndexOf('.')) : '';
                    if (!fileext || (allowFiles && (allowFiles.join('') + '.').indexOf(fileext.toLowerCase() + '.') == -1)) {
                        showErrorLoader(me.getLang('simpleupload.exceedTypeError'));
                        return;
                    }
                    for (var i = 0; i < this.files.length; i++) {
                        Upload.api.send(this.files[i], function (data) {
                            var url = Fast.api.cdnurl(data.url);
                            loader = me.document.getElementById(loadingId);
                            loader.setAttribute('src', url);
                            loader.setAttribute('_src', url);
                            loader.setAttribute('title', '');
                            loader.setAttribute('alt', '');
                            loader.removeAttribute('id');
                            UE.dom.domUtils.removeClasses(loader, 'loadingclass');
                            form.reset();
                        });
                    }
                });

                var stateTimer;
                me.addListener('selectionchange', function () {
                    clearTimeout(stateTimer);
                    stateTimer = setTimeout(function () {
                        var state = me.queryCommandState('simpleupload');
                        if (state == -1) {
                            input.disabled = 'disabled';
                        } else {
                            input.disabled = false;
                        }
                    }, 400);
                });
                isLoaded = true;
            });

            btnIframe.style.cssText = btnStyle;
            containerBtn.appendChild(btnIframe);
        }

        return {
            bindEvents: {
                'ready': function () {
                    //设置loading的样式
                    UE.utils.cssRule('loading',
                        '.loadingclass{display:inline-block;cursor:default;background: url(\'' +
                        this.options.themePath +
                        this.options.theme + '/images/loading.gif\') no-repeat center center transparent;border:1px solid #cccccc;margin-right:1px;height: 22px;width: 22px;}\n' +
                        '.loaderrorclass{display:inline-block;cursor:default;background: url(\'' +
                        this.options.themePath +
                        this.options.theme + '/images/loaderror.png\') no-repeat center center transparent;border:1px solid #cccccc;margin-right:1px;height: 22px;width: 22px;' +
                        '}',
                        this.document);
                },
                /* 初始化简单上传按钮 */
                'simpleuploadbtnready': function (type, container) {
                    containerBtn = container;
                    me.afterConfigReady(initUploadBtn);
                }
            },
            outputRule: function (root) {
                UE.utils.each(root.getNodesByTagName('img'), function (n) {
                    if (/\b(loaderrorclass)|(bloaderrorclass)\b/.test(n.getAttr('class'))) {
                        n.parentNode.removeChild(n);
                    }
                });
            },
            commands: {
                'simpleupload': {
                    queryCommandState: function () {
                        return isLoaded ? 0 : -1;
                    }
                }
            }
        }
    });
    UE.plugin.register('autoupload', function () {

        function sendAndInsertFile(file, editor) {
            var me = editor;
            //模拟数据
            var fieldName, urlPrefix, maxSize, allowFiles, actionUrl,
                loadingHtml, errorHandler, successHandler,
                filetype = /image\/\w+/i.test(file.type) ? 'image' : 'file',
                loadingId = 'loading_' + (+new Date()).toString(36);

            fieldName = me.getOpt(filetype + 'FieldName');
            urlPrefix = me.getOpt(filetype + 'UrlPrefix');
            maxSize = me.getOpt(filetype + 'MaxSize');
            allowFiles = me.getOpt(filetype + 'AllowFiles');
            actionUrl = me.getActionUrl(me.getOpt(filetype + 'ActionName'));
            errorHandler = function (title) {
                var loader = me.document.getElementById(loadingId);
                loader && UE.dom.domUtils.remove(loader);
                me.fireEvent('showmessage', {
                    'id': loadingId,
                    'content': title,
                    'type': 'error',
                    'timeout': 4000
                });
            };

            if (filetype == 'image') {
                loadingHtml = '<img class="loadingclass" id="' + loadingId + '" src="' +
                    me.options.themePath + me.options.theme +
                    '/images/spacer.gif" title="' + (me.getLang('autoupload.loading') || '') + '" >';
                successHandler = function (data) {
                    var link = urlPrefix + data.url,
                        loader = me.document.getElementById(loadingId);
                    if (loader) {
                        loader.setAttribute('src', link);
                        loader.setAttribute('_src', link);
                        loader.setAttribute('title', data.title || '');
                        loader.setAttribute('alt', data.original || '');
                        loader.removeAttribute('id');
                        UE.dom.domUtils.removeClasses(loader, 'loadingclass');
                    }
                };
            } else {
                loadingHtml = '<p>' +
                    '<img class="loadingclass" id="' + loadingId + '" src="' +
                    me.options.themePath + me.options.theme +
                    '/images/spacer.gif" title="' + (me.getLang('autoupload.loading') || '') + '" >' +
                    '</p>';
                successHandler = function (data) {
                    var link = urlPrefix + data.url,
                        loader = me.document.getElementById(loadingId);

                    var rng = me.selection.getRange(),
                        bk = rng.createBookmark();
                    rng.selectNode(loader).select();
                    me.execCommand('insertfile', {
                        'url': link
                    });
                    rng.moveToBookmark(bk).select();
                };
            }

            /* 插入loading的占位符 */
            me.execCommand('inserthtml', loadingHtml);

            /* 判断后端配置是否没有加载成功 */
            if (!me.getOpt(filetype + 'ActionName')) {
                errorHandler(me.getLang('autoupload.errorLoadConfig'));
                return;
            }
            /* 判断文件大小是否超出限制 */
            if (file.size > maxSize) {
                errorHandler(me.getLang('autoupload.exceedSizeError'));
                return;
            }
            /* 判断文件格式是否超出允许 */
            var fileext = file.name ? file.name.substr(file.name.lastIndexOf('.')) : '';
            if ((fileext && filetype != 'image') || (allowFiles && (allowFiles.join('') + '.').indexOf(fileext.toLowerCase() + '.') == -1)) {
                errorHandler(me.getLang('autoupload.exceedTypeError'));
                return;
            }
            try {
                Upload.api.send(file, function (data) {
                    var url = Fast.api.cdnurl(data.url);
                    successHandler({
                        "state": "SUCCESS",
                        "url": url,
                        "title": file.name,
                        "original": file.name,
                        "type": fileext,
                        "size": file['size']
                    });
                });
            } catch (er) {
                errorHandler(me.getLang('autoupload.loadError'));
            }
        }

        function getPasteImage(e) {
            return e.clipboardData && e.clipboardData.items && e.clipboardData.items.length == 1 && /^image\//.test(e.clipboardData.items[0].type) ? e.clipboardData.items : null;
        }

        function getDropImage(e) {
            return e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
        }

        return {
            outputRule: function (root) {
                UE.utils.each(root.getNodesByTagName('img'), function (n) {
                    if (/\b(loaderrorclass)|(bloaderrorclass)\b/.test(n.getAttr('class'))) {
                        n.parentNode.removeChild(n);
                    }
                });
                UE.utils.each(root.getNodesByTagName('p'), function (n) {
                    if (/\bloadpara\b/.test(n.getAttr('class'))) {
                        n.parentNode.removeChild(n);
                    }
                });
            },
            bindEvents: {
                //插入粘贴板的图片，拖放插入图片
                'ready': function (e) {
                    var me = this;
                    if (window.FormData && window.FileReader) {
                        UE.dom.domUtils.on(me.body, 'paste drop', function (e) {
                            var hasImg = false,
                                items;
                            //获取粘贴板文件列表或者拖放文件列表
                            items = e.type == 'paste' ? getPasteImage(e) : getDropImage(e);
                            if (items) {
                                var len = items.length,
                                    file;
                                while (len--) {
                                    file = items[len];
                                    if (file.getAsFile) file = file.getAsFile();
                                    if (file && file.size > 0) {
                                        sendAndInsertFile(file, me);
                                        hasImg = true;
                                    }
                                }
                                hasImg && e.preventDefault();
                            }

                        });
                        //取消拖放图片时出现的文字光标位置提示
                        UE.dom.domUtils.on(me.body, 'dragover', function (e) {
                            if (e.dataTransfer.types[0] == 'Files') {
                                e.preventDefault();
                            }
                        });

                        //设置loading的样式
                        UE.utils.cssRule('loading',
                            '.loadingclass{display:inline-block;cursor:default;background: url(\'' +
                            this.options.themePath +
                            this.options.theme + '/images/loading.gif\') no-repeat center center transparent;border:1px solid #cccccc;margin-left:1px;height: 22px;width: 22px;}\n' +
                            '.loaderrorclass{display:inline-block;cursor:default;background: url(\'' +
                            this.options.themePath +
                            this.options.theme + '/images/loaderror.png\') no-repeat center center transparent;border:1px solid #cccccc;margin-right:1px;height: 22px;width: 22px;' +
                            '}',
                            this.document);
                    }
                }
            }
        }
    });

    /**
     * 远程图片抓取,当开启本插件时所有不符合本地域名的图片都将被抓取成为本地服务器上的图片
     */
    UE.plugins['catchremoteimage'] = function () {
        var me = this,
            ajax = UE.ajax;

        /* 设置默认值 */
        if (me.options.catchRemoteImageEnable === false) return;
        me.setOpt({
            catchRemoteImageEnable: false
        });

        me.addListener("afterpaste", function () {
            me.fireEvent("catchRemoteImage");
        });

        me.addListener("catchRemoteImage", function () {

            var catcherLocalDomain = me.getOpt('catcherLocalDomain'),
                catcherActionUrl = me.getActionUrl(me.getOpt('catcherActionName')),
                catcherUrlPrefix = me.getOpt('catcherUrlPrefix'),
                catcherFieldName = me.getOpt('catcherFieldName');
                var remoteImages = [],
                imgs = UE.dom.domUtils.getElementsByTagName(me.document, "img"),
                test = function (src, urls) {
                    if (src.indexOf(location.host) != -1 || /(^\.)|(^\/)/.test(src)) {
                        return true;
                    }
                    if (urls) {
                        for (var j = 0, url; url = urls[j++];) {
                            if (src.indexOf(url) !== -1) {
                                return true;
                            }
                        }
                    }
                    return false;
                };
    
            for (var i = 0, ci; ci = imgs[i++];) {
                if (ci.getAttribute("word_img")) {
                    continue;
                }
                var src = ci.getAttribute("_src") || ci.src || "";
                if (/^(https?|ftp):/i.test(src) && !test(src, catcherLocalDomain)) {
                    remoteImages.push(src);
                }
            }
    
            if (remoteImages.length) {
                catchremoteimage(remoteImages, {
                    //成功抓取
                    success:async function (r) {
                        try {
                            var info = r.state !== undefined ? r:eval("(" + r.responseText + ")");
                        } catch (e) {
                            return;
                        }
    
                        /* 获取源路径和新路径 */
                        var i, j, ci, cj, oldSrc, newSrc, list = info.list;
    
                        for (i = 0; ci = imgs[i++];) {
                            oldSrc = ci.getAttribute("_src") || ci.src || "";
                            for (j = 0; cj = list[j++];) {
                                //抓取失败时不做替换处理
                                if (oldSrc == cj.source && cj.state == "SUCCESS") {  
                                    var file = dataURLtoFile(cj.base64Data, (new Date()).valueOf() + '.jpg');
                                    var imgUrl=await getLoaclImg(file);
                                    newSrc = Fast.api.cdnurl(imgUrl);
                                    UE.dom.domUtils.setAttributes(ci, {
                                        "src": newSrc,
                                        "_src": newSrc
                                    });
                                    break;
                                }
                            }
                        }
                        me.fireEvent('catchremotesuccess')
                    },
                    //回调失败，本次请求超时
                    error: function () {
                        me.fireEvent("catchremoteerror");
                    }
                });
            }

            async function getLoaclImg(file){
                const promise =new Promise((resolve,reject) => {
                    Upload.api.send(file, function (res) {
                        resolve(res.url);
                    })
                })
                return promise;
            }
            
            /**
             * base64转file
             * @param {base64数据} data 
             * @param {文件名} filename 
             */
            function dataURLtoFile(data, filename) {
                var arr = data.split(','),
                    mime = arr[0].match(/:(.*?);/)[1],
                    bstr = atob(arr[1]),
                    n = bstr.length,
                    u8arr = new Uint8Array(n);
                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }
                return new File([u8arr], filename, {
                    type: mime
                });
            }
            /**
             *获取base64
             *
             * @param {*} img
             * @param {*} callbacks
             */
            function catchremoteimage(img, callbacks) {
                var params = UE.utils.serializeParam(me.queryCommandValue('serverparam')) || '',
                    url = UE.utils.formatUrl(catcherActionUrl + (catcherActionUrl.indexOf('?') == -1 ? '?':'&') + params),
                    isJsonp = UE.utils.isCrossDomainUrl(url),
                    opt = {
                        'method': 'POST',
                        'dataType': isJsonp ? 'jsonp':'',
                        'timeout': 60000, //单位：毫秒，回调请求超时设置。目标用户如果网速不是很快的话此处建议设置一个较大的数值
                        'onsuccess': callbacks["success"],
                        'onerror': callbacks["error"]
                    };
                opt[catcherFieldName] = img;
                ajax.request(url, opt);
            }
        });
    };

    $(".editor").each(function () {
        var id = $(this).attr("id");
        $(this).removeClass('form-control');
        UE.list[id] = UE.getEditor(id, {
            serverUrl: Fast.api.fixurl('/addons/ueditor/api/'),
            allowDivTransToP: false, //阻止div自动转p标签
            initialFrameWidth: '100%',
            zIndex: 90,
            xssFilterRules: false,
            outputXssFilter: false,
            inputXssFilter: false,
            catchRemoteImageEnable: true
        });
        //监听图片上传事件
        UE.list[id].addListener("uploadBtn.click", function (e, up, editor) {
            var filesObj = up.getFiles();
            for (var i = 0; i < filesObj.length; i++) {
                (function (j) {
                    var file = filesObj[j];
                    var id = filesObj[j].id;
                    var name = filesObj[j].name;
                    Upload.api.send(file.source.source, function (data) {
                        var pic = {
                            url: Fast.api.cdnurl(data.url),
                            state: "SUCCESS",
                            title: name
                        };
                        editor.fireEvent("upload.success", id, pic, file);
                    });
                })(i);
            }
        });
        //打开图片管理
        UE.list[id].addListener("upload.online", function (e, editor, dialog) {
            dialog.close(false);
            Fast.api.open("general/attachment/select?element_id=&multiple=true&mimetype=image/*", "选择", {
                callback: function (data) {
                    var urlArr = data.url.split(/\,/);
                    urlArr.forEach(function (item, index) {
                        var url = Fast.api.cdnurl(item);
                        editor.execCommand('insertimage', {
                            src: url
                        });
                    });
                }
            });
        });
        // 涂画
        UE.list[id].addListener("upload.scrawl", function (e, editor, base64, dialog) {
            function dataURLtoFile(dataurl, filename) {
                var arr = dataurl.split(','),
                    mime = arr[0].match(/:(.*?);/)[1],
                    bstr = atob(arr[1]),
                    n = bstr.length,
                    u8arr = new Uint8Array(n);
                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }
                return new File([u8arr], filename, {
                    type: mime
                });
            }

            var file = dataURLtoFile('data:image/png;base64,' + base64, editor.getOpt('scrawlFieldName') + '.png');
            Upload.api.send(file, function (data) {
                editor.execCommand('insertimage', {
                    src: Fast.api.cdnurl(data.url)
                });
                dialog.close(false);
            })
        })
        //视频上传
        UE.list[id].addListener("upload.video", function (e, up, editor) {
            var filesObj = up.getFiles();
            for (var i = 0; i < filesObj.length; i++) {
                (function (j) {
                    var file = filesObj[j];
                    var id = filesObj[j].id;
                    var name = filesObj[j].name;
                    Upload.api.send(file.source.source, function (data) {
                        var pic = {
                            url: Fast.api.cdnurl(data.url),
                            state: "SUCCESS",
                            title: name
                        };
                        editor.fireEvent("video.file.success", id, pic, file);
                    });
                })(i);
            }
        });
        // 附件上传
        UE.list[id].addListener("upload.attachment", function (e, up, editor) {
            var filesObj = up.getFiles();
            for (var i = 0; i < filesObj.length; i++) {
                (function (j) {
                    var file = filesObj[j];
                    var id = filesObj[j].id;
                    var name = filesObj[j].name;
                    Upload.api.send(file.source.source, function (data) {
                        var pic = {
                            url: Fast.api.cdnurl(data.url),
                            state: "SUCCESS",
                            title: name
                        };
                        editor.fireEvent("attachment.file.success", id, pic, file);
                    });
                })(i);
            }
        });
        //打开附件管理
        UE.list[id].addListener("file.online", function (e, editor, dialog) {
            dialog.close(false);
            Fast.api.open("general/attachment/select?element_id=&multiple=true&mimetype=application/*", "选择", {
                callback: function (data) {
                    var urlArr = data.url.split(/\,/);
                    urlArr.forEach(function (item, index) {
                        var url = Fast.api.cdnurl(item);
                        editor.execCommand('insertfile', {
                            url: url
                        });
                    });
                }
            });
        });
        // 修复cms提取关键词和违禁词检测
        UE.list[id].addListener("contentChange", function () {
            $('#' + id).val(this.getContent());
        })
    });
});