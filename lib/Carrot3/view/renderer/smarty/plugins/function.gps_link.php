<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * GPS対応のリンクを貼り付ける関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_gps_link ($params, &$smarty) {
	$params = C\Tuple::create($params);
	if ($useragent = $smarty->getUserAgent()) {
		if (C\StringUtils::isBlank($params['contents'])) {
			$url = C\URL::create($params, 'carrot');
		} else {
			$url = C\URL::create($params['contents']);
		}
		$url->setUserAgent($useragent);
		$element = $useragent->createGPSAnchorElement($url, $params['label']);
		return $element->getContents();
	}
}

