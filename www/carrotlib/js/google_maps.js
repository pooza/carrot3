/**
 * Google Maps処理
 *
 * テンプレートでの記述例:
 * <div id="map" style="width:400px; height:400px">Loading...</div> 
 * <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
 * <script src="/carrotlib/js/google_maps.js" type="text/javascript"></script>
 * <script type="text/javascript">
 * document.observe('dom:loaded', function () {ldelim}
 *   handleGoogleMaps($('map'), {$geocode.lat|default:0}, {$geocode.lng|default:0}, 18);
 * {rdelim});
 * </script>
 *
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
var CarrotMapsLib = {
  handleMap: function (container, lat, lng, zoom) {
    if (!zoom) {
      zoom = 17;
    }
    var point = new google.maps.LatLng(lat, lng);
    var map = new google.maps.Map(container, {
      zoom: zoom,
      center: point,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });
    var marker = new google.maps.Marker({
      position: point,
      map: map
    });
  },
  initialized: true
};
