/**
 * エレベータ処理
 *
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

Elevator = Class.create({
  initialize: function (element, options) {
    element = $(element);
    options = Object.extend({
      x: 0,
      yMin: 0,
      yMargin: 0,
      seconds: 0.1
    }, options);

    new PeriodicalExecuter(function () {
      if (Prototype.Browser.IE){
        var y = (document.body.scrollTop || document.documentElement.scrollTop);
      } else  {
        var y = self.pageYOffset;
      }
      if (y < options.yMin) {
        y = options.yMin;
      } else {
        y = y + options.yMargin;
      }
      element.style.position = 'absolute';
      element.style.left = options.x + 'px';
      element.style.top = y + 'px';
    }, options.seconds);
  },

  initialized: true
});
