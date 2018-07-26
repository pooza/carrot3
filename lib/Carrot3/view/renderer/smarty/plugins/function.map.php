<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * GoogleMaps関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_map ($params, &$smarty) {
	$params = C\Tuple::create($params);
	try {
		$service = new C\GoogleMapsService;
		$service->setUserAgent($smarty->getUserAgent());
		if ($params['lat'] && ($params['lng'] || $params['lon'])) {
			if (C\StringUtils::isBlank($params['lng'])) {
				$params['lng'] = $params['lon'];
				$params->removeParameter('lon');
			}
			$addr = new C\StringFormat('lat=%s,lng=%s');
			$addr[] = $params['lat'];
			$addr[] = $params['lng'];
			$params['addr'] = $addr->getContents();
		}
		$element = $service->createElement($params['addr'], $params);
	} catch (\Throwable $e) {
		$element = new C\DivisionElement;
		$span = $element->addElement(new C\SpanElement);
		$span->registerStyleClass('alert');
		$span->setBody('ジオコードが取得できません。');
	}
	return $element->getContents();
}
