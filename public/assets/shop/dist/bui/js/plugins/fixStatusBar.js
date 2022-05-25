/* 
 * 用于修复iPhoneX及iphone的全屏沉浸式打包,打包的时候在index.html引入脚本就可.
 * 2018-05-30
 */

bui.on("pageinit",function () {
  fixStatusBar();
})


// 修复Binotouch打包沉浸式状态栏
function fixStatusBar() {
  var platform = bui.platform;
  if( platform.isIos() && platform.isIphoneX() ){
    $("body").addClass("iphoneX");
  }else if( platform.isIos() && !platform.isIphoneX() ){
    $("body").addClass("iphone");
  }
}