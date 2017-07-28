<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * GoogleAnalytics関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_analytics ($params, &$smarty) {
	$params = C\Tuple::create($params);
	$service = C\GoogleAnalyticsService::getInstance();
	if ($id = $params['id']) {
		$service->setID($id);
	}

	try {
		return $service->getTrackingCode();
	} catch (\Exception $e) {
	}
}

