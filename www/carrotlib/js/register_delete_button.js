HTMLElement.prototype.registerDeleteButton = function (params) {
  params = params || {};
  params.label = (params.label || 'レコード');
  params.action = (params.action || 'Delete');
  params.redirectTo = (params.redirectTo || '/' + params.module + '/');
  this.value = 'この' + params.label + 'を削除...';
  this.addEventListener('click', function () {
    if (confirm('この' + params.label + 'を削除しますか？')) {
      var indicator = new ActivityIndicator();
      indicator.show();
      window.superagent.post('/' + params.module + '/' + params.action)
      .type('form').send(params).end(function (error, response) {
        indicator.hide();
        if (response.ok) {
          window.location.href = params.redirectTo;
        } else {
          alert(response.body.api.errors.record || response.text);
        }
      });
    }
  });
}
