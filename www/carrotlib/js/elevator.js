function Elevator (element, options) {
  var x = (options.x || 0);
  var yMin = (options.yMin || 0);
  var yMargin = (options.yMargin || 10);
  var seconds = (options.seconds || 0.1);
  var container = (options.container || window);

  setInterval(function () {
    var y = container.pageYOffset;
    if (y < yMin) {
      y = yMin;
    } else {
      y = y + yMargin;
    }
    element.style.position = 'absolute';
    element.style.left = x + 'px';
    element.style.top = y + 'px';
  }, seconds);
}


