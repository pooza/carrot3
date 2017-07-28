<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\Tuple;
use \Carrot3\GoogleMapsService;
use \Carrot3\StringUtils;
use \Carrot3\StringFormat;
use \Carrot3\DivisionElement;
use \Carrot3\SpanElement;

/**
 * GoogleMaps関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_map ($params, &$smarty) {
	$params = Tuple::create($params);
	try {
		$service = new GoogleMapsService;
		$service->setUserAgent($smarty->getUserAgent());
		if ($params['lat'] && ($params['lng'] || $params['lon'])) {
			if (StringUtils::isBlank($params['lng'])) {
				$params['lng'] = $params['lon'];
				$params->removeParameter('lon');
			}
			$addr = new StringFormat('lat=%s,lng=%s');
			$addr[] = $params['lat'];
			$addr[] = $params['lng'];
			$params['addr'] = $addr->getContents();
		}
		$element = $service->createElement($params['addr'], $params);
	} catch (\Exception $e) {
		$element = new DivisionElement;
		$span = $element->addElement(new SpanElement);
		$span->registerStyleClass('alert');
		$span->setBody('ジオコードが取得できません。');
	}
	return $element->getContents();
}

