{*
GoogleAnalyticsテンプレート

@package jp.co.b-shock.carrot3
@author 小石達也 <tkoishi@b-shock.co.jp>
*}

<script>
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-{$params.id}']);
_gaq.push(['_setDomainName', '{$params.domain}']);
_gaq.push(['_trackPageview']);

(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>

