/**
 * 二度押し禁止対応
 *
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

(function () {
  var flag = false;
  document.addEventListener('submit', function (event) {
    if (flag) {
      event.preventDefault();
    } else {
      event.target.querySelector('[type=submit]').disabled = true;
      flag = true;
    }
  });
})();
