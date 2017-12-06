/**
 * carrot汎用 JavaScript
 *
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

var CarrotLib = {
  redirect: function (module, action, id, query) {
    var url = '/' + module + '/' + action;
    if (id) {
      url += '/' + id;
    }
    if (query) {
      url += '?' + $H(query).toQueryString();
    }
    window.location.href = url;
  },

  confirmDelete: function (module, action, recordType, id) {
    if (confirm('この' + recordType + 'を削除しますか？')) {
      CarrotLib.redirect(module, action, id);
    }
  },

  getQueryParameter: function (name) {
    var q = location.search || location.hash;
    if (q && q.match(/\?/)) {
      var pairs = q.split('?')[1].split('&');
      for (var i = 0 ; i < pairs.length ; i ++) {
        if (pairs[i].substring(0, pairs[i].indexOf('=')) == name) {
          return decodeURI(pairs[i].substring((pairs[i].indexOf('=') + 1)));
        }
      }
    }
    return '';
  },

  getRecordID: function () {
    var id;
    if (id = location.href.split('?')[0].split('#')[0].split('/')[5]) {
      return encodeURIComponent(id);
    }
    return '';
  },

  initialized: true
};

if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^[ ]+|[ ]+$/g, '');
  }
}
if (!Number.prototype.suppressZero) {
  Number.prototype.suppressZero = function (n) {
    var str = '';
    var zerolen = n - ('' + this).length;
    for (var i = 0 ; i < zerolen ; i ++) {
      str += '0';
    }
    str += this;
    return str;
  }
}
if (!Array.prototype.contains) {
  Array.prototype.contains = function (value) {
    for (var i in this) {
      if (this.hasOwnProperty(i) && this[i] === value) {
        return true;
      }
    }
    return false;
  }
}

document.observe('dom:loaded', function () {
  $$('.date_picker').each(function (element) {
    new InputCalendar(element.id, {
      lang: 'ja',
      format: 'yyyy.mm.dd'
    });
  });
  $$('.datetime_picker').each(function (element) {
    new InputCalendar(element.id, {
      lang: 'ja',
      format: 'yyyy.mm.dd HH:MM',
      enableHourMinute: true
    });
  });
  $$('.color_picker').each(function (element) {
    new Control.ColorPicker(element.id);
  });
});

document.observe('dom:loaded', function () {
  if ($('tabs')) {
    var urls = {};
    $$('.panel').each(function (element) {
      var href;
      if (href = element.getAttribute('href')) {
        urls[element.id] = href;
      }
    });

    var pane = 'detail_form_pane';
    if (CarrotLib.getQueryParameter('pane')) {
      pane = CarrotLib.getQueryParameter('pane');
    }

    new ProtoTabs('tabs', {
      defaultPanel: pane,
      ajaxUrls: urls
    });
  }
});
