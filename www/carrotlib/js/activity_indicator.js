/**
 * インジケータ
 *
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

ActivityIndicator = Class.create({
  initialize: function () {
    this.img = document.createElement('img');
    this.img.src = '/carrotlib/images/indicator.gif';
    this.img.width = 220;
    this.img.height = 19;
    this.img.style.margin = '10px';

    this.container = document.createElement('div');
    this.container.style.display = 'none';
    this.container.style.position = 'fixed';
    this.container.style.left = '50%';
    this.container.style.top = '50%';
    this.container.style.zIndex = 9999;
    this.container.style.width = '240px';
    this.container.style.height = '40px';
    this.container.style.backgroundColor = '#fff';
    this.container.style.textAlign = 'center';
    this.container.style.borderWidth = '1px';
    this.container.style.borderStyle = 'solid';
    this.container.style.borderColor = '#000';
    this.container.style.opacity = 0.9;
    this.container.style.filter = 'alpha(opacity=90)';
    this.container.appendChild(this.img);

    $$('body')[0].appendChild(this.container);
  },

  show: function () {
    this.container.style.display = 'block';
    this.container.style.marginLeft = (-0.5 * this.img.offsetWidth) + 'px';
    this.container.style.marginTop = (-0.5 * this.img.offsetHeight) + 'px';
  },

  hide: function () {
    this.container.style.display = 'none';
  },

  initialized: true
});

document.observe('dom:loaded', function () {
  var indicator = new ActivityIndicator();
  $$('form').each(function (frm) {
    if (!Element.hasClassName(frm, 'no_indicator')) {
      frm.observe('submit', function (event) {
        indicator.show();
      });
    }
  });
});
