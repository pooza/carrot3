function ActivityIndicator () {
  var progress = document.createElement('progress');
  var img = document.createElement('img');
  img.src = '/carrotlib/images/indicator.gif';
  img.width = 220;
  img.height = 19;
  img.style.margin = '10px';
  progress.appendChild(img);

  var container = document.createElement('div');
  container.style.display = 'none';
  container.style.position = 'fixed';
  container.style.left = '50%';
  container.style.top = '50%';
  container.style.zIndex = 9999;
  container.style.backgroundColor = '#fff';
  container.style.textAlign = 'center';
  container.style.borderWidth = '1px';
  container.style.borderStyle = 'solid';
  container.style.borderColor = '#000';
  container.style.opacity = 0.9;
  container.appendChild(progress);

  document.getElementsByTagName('body')[0].appendChild(container);

  this.show = function () {
    container.style.display = 'block';
    container.style.width = (progress.clientWidth + 8) + 'px';
    container.style.height = (progress.clientHeight + 8) + 'px';
    container.style.marginLeft = (-0.5 * progress.offsetWidth) + 'px';
    container.style.marginTop = (-0.5 * progress.offsetHeight) + 'px';
  }

  this.hide = function () {
    container.style.display = 'none';
  }

  this.setMax = function (max) {
    if (max === undefined || max === null) {
      progress.removeAttribute('value');
      progress.removeAttribute('max');
    } else {
      progress.max = max;
      progress.value = 0;
    }
  }

  this.setValue = function (value) {
    progress.value = value;
  }
}

document.addEventListener('DOMContentLoaded', function () {
  var indicator = new ActivityIndicator();
  document.getElementsByTagName('form').forEach(function (frm) {
    if (!frm.className.match(/no_indicator/)) {
      frm.addEventListener('submit', function () {
        indicator.show();
      });
    }
  });
});
