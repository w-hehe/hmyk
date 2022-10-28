// composedPath polyfill
(function(E, d, w) {
  if(!E.composedPath) {
    E.composedPath = function() {
      if (this.path) {
        return this.path;
      } 
    var target = this.target;
    
    this.path = [];
    while (target.parentNode !== null) {
      this.path.push(target);
      target = target.parentNode;
    }
    this.path.push(d, w);
    return this.path;
    }
  }
})(Event.prototype, document, window);

(function(){
  // 解析query参数变Object对象
  function parseParamToObj(obj) {
    if (!obj) return;
    var str = obj.search;
    if (!str) {
      str = obj.hash;
    }
    var index = str.indexOf('?');
    str = str.substring(index + 1);
    var result = {};
    var paramArray = str.split('&');
    for (var i = 0, len = paramArray.length; i < len; i++) {
      var pair = paramArray[i].split('=');
      if (pair[1] === '') {
        pair.push('');
      }
      result[pair[0]] = pair[1];
    }
    return result;
  }

  function getAppBuvid() {
    var ua = window.navigator.userAgent
    var buvidMatch = ua.match(/Buvid\/([a-zA-Z0-9]+)/)
    if (buvidMatch && buvidMatch.length > 0) {
      return buvidMatch[1]
    }
    return
  }
  var appBuvid = getAppBuvid();

  // 根据url增加当前访问路径参数
  var relHttp = /^(https?:)?\/\//;
  var whiteHrefListReg = /is_live_half_webview/ig;
  var searchObj = parseParamToObj(location);
  function parseHref(str) {
    if (!str) return;
    
    if (!relHttp.test(_s)) {
      return str;
    }

    var href = window.location.href;
    if (href.match(whiteHrefListReg)) {
      return str;
    }
    
    var _s = str.replace(relHttp, location.protocol + '//');
    var urlSplit = _s.split('#');
    var urlNoHash = urlSplit[0];
    var hash = urlSplit[1] || '';
    var isQuery = _s.indexOf('?') !== -1;

    var searchStr = '';
    Object.keys(searchObj).forEach(function(key){
      if (
        _s.indexOf(key) >= 0 || 
        (['from', 'msource'].indexOf(key) === -1 && location.host.indexOf('mall') > -1)
      ) {
        // delete searchObj[key];
      } else {
        searchStr += key + '=' + searchObj[key];
      }
    });

    if (isQuery && search) {
      return urlNoHash + '&' + searchStr + hash;
    }

    return urlNoHash + '?' + searchStr + hash;
  }

  function getSpmid() {
    if (window.activity && window.activity.spmId) {
      return window.activity.spmId
    }
    var metas = document.getElementsByTagName('meta')

    for (var i = 0; i < metas.length; i++) {
      if (metas[i].getAttribute('name') === 'spm_prefix') {
        return metas[i].getAttribute('content')
      }
    }

    return '888.1';
  }

  // 上报方法（click、show）按照新埋点规则上报
  var spmid = getSpmid();
  function sendReporter(type, modulePath, msg) {
    if (reportObserver && reportObserver.reportCustomData) {
      try {
        setTimeout(function(){
          reportObserver.reportCustomData('click', {
            spm_id: spmid + '.' + modulePath + '.' + type,
            msg: JSON.stringify(msg || {})
          })
        }, 0)
      } catch (e) {
        return;
      } 
    }
  }

  // 先停用
  // window.addEventListener('load', function() {
  //   var shareSource = searchObj.share_source || searchObj.from;
  //   if (shareSource) {
  //     customReporter('shared_', shareSource)
  //   }

  //   if (searchObj.msource) {
  //     customReporter('msource_', searchObj.msource)
  //   }
  
  //   if (searchObj.topic_from) {
  //     customReporter('topic_from_', searchObj.topic_from)
  //   }
    
  //   // 替换a标签地址，增加参数传递
  //   var all = document.querySelectorAll('div a');
  //   for (var i = 0, len = all.length; i < len; i++ ) {
  //     var link = all[i].dataset.link;
  //     var href = all[i].href;
  //     if (link) {
  //       all[i].dataset.link = parseHref(all[i].dataset.link);
  //       var schema = all[i].dataset.schema;
  //       if (schema && !/^bilibili:/.test(schema)) {
  //         all[i].dataset.schema = parseHref(schema);
  //       }
  //     } else if (href) {
  //       all[i].href = parseHref(all[i].href);
  //     }
  //   }
  // });
  
  // 模块的点击上报
  document.addEventListener('click', function (e) {
    if (!e.path && !e.composedPath) {
      sendReporter('error', 'clickpath.0', {
        className: e.target.className,
      });
      return
    };
    var patharr = e.path ? e.path : e.composedPath();
    if (patharr.length > 0) {
      for (var i = 0; i < patharr.length; i++) {
        var node = patharr[i];
        if (
          node &&
          node.className &&
          node.dataset.module
        ) {
          sendReporter('click', node.id + '.0', {
            version: node.dataset.version,
            module: node.dataset.module,
            appBuvid: appBuvid
          });
        }
      }
    }
  }, true);

  /* 曝光上报逻辑 */
  var reportMap = {};
  var io;
  var isIntersectionObserver = !!window.IntersectionObserver;
  sendReporter('pv', '0.0', {
    share_source: searchObj.share_source,
    msource: searchObj.msource || searchObj.from_spmid || searchObj.spm_id_from || searchObj.topic_from || searchObj.from,
    from: searchObj.from,
    appBuvid: appBuvid
  });

  var firstReport = false;
  function sendNodeShow(node){
    var id = node.id;
    if (reportMap[id] === 1) {
      sendReporter('show', id + '.0', {
        version: node.dataset.version,
        module: node.dataset.module,
        appBuvid: appBuvid
      });
      reportMap[id] = 2
      if (!firstReport) {
        sendReporter('show', 'first.0', {
          module: node.dataset.module,
          moduleId: node.id,
          appBuvid: appBuvid,
        });
        firstReport = true;
      }
    }
  }

  if(isIntersectionObserver) {
    io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry) {
        if (entry.intersectionRatio < 0.2) {
          return;
        }
        sendNodeShow(entry.target);
      })
    }, {
      threshold: [0.2]
    });
  }

  document.addEventListener('scroll', function(e) {
    debounceTime();
  });

  document.addEventListener('DOMContentLoaded', function() {
    updateIntersectionObserver();
  }, false);
  debounceTime();

  // 阻塞频繁执行
  var timeout;
  function debounceTime(time) {
    clearTimeout(timeout);
    timeout = setTimeout(function(){
      updateIntersectionObserver();
    }, time || 100);
  }

  function updateIntersectionObserver () {
    var allModule = document.querySelectorAll('div[data-module]');
    if (!allModule) {
      return
    }
    for(var i= 0; i< allModule.length; i++){
      var node = allModule[i];
      var id = node.id;
      if (!reportMap[id]) {
        if (isIntersectionObserver) {
          reportMap[id] = 1;
          io.observe(node);
        } else if(isElementInViewport(node)) {
          reportMap[id] = 1;
          sendNodeShow(node);
        }
      }
    }
  }

  function isElementInViewport(el) {
    var rect = el.getBoundingClientRect();
    if (!rect.width || !rect.height) {
      return false;
    }

    var innerHeight = window.innerHeight;
    var innerWidth = window.innerWidth;

    // 有效坐标
    var left_v = rect.left <= 0 || rect.left > innerWidth ? 0 : rect.left;
    var top_v = rect.top <= 0 || rect.top > innerHeight ? 0 : rect.top;
    var right_v = rect.right <= 0 || (rect.right > innerWidth && rect.left > innerWidth) ? 0 : rect.right > innerWidth ? innerWidth : rect.right;
    var bottom_v = rect.bottom <= 0 || (rect.bottom > innerHeight && rect.top > innerHeight) ? 0 : rect.bottom > innerHeight ? innerHeight : rect.bottom;
    // 在窗口的宽高
    var width_v = right_v - left_v;
    var height_v = bottom_v - top_v;

    var area_v = width_v * height_v;

    var area = rect.width * rect.height;

    return area_v >= area / 5 || height_v > innerHeight / 2;
  }
  /* 曝光上报逻辑 */
})();
