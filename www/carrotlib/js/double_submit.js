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
