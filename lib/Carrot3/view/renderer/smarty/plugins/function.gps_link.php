<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\Tuple;
use \Carrot3\StringUtils;
use \Carrot3\URL;

/**
 * GPS対応のリンクを貼り付ける関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_gps_link ($params, &$smarty) {
	$params = Tuple::create($params);
	if ($useragent = $smarty->getUserAgent()) {
		if (StringUtils::isBlank($params['contents'])) {
			$url = URL::create($params, 'carrot');
		} else {
			$url = URL::create($params['contents']);
		}
		$url->setUserAgent($useragent);
		$element = $useragent->createGPSAnchorElement($url, $params['label']);
		return $element->getContents();
	}
}

