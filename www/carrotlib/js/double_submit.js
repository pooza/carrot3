/**
 * 二度押し禁止対応処理
 *
 * @package jp.co.dipps.minc3
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
