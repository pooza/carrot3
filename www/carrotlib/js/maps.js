var MapsLib = {
  handleMap: function (container, lat, lng, zoom) {
    if (!zoom) {
      zoom = 17;
    }
    var point = new google.maps.LatLng(lat, lng);
    var map = new google.maps.Map(container, {
      zoom: zoom,
      center: point,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
    });
    var marker = new google.maps.Marker({
      position: point,
      map: map,
    });
  },
};
