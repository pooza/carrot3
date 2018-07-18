document.addEventListener('DOMContentLoaded', function () {
  document.getElementsByTagName('img').forEach(function (image) {
    if (image.parentNode.tagName.toLowerCase() != 'a') {
      image.style.pointerEvents = 'none';
    }
  });
});
