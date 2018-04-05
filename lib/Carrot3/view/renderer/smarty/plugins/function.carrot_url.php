<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * CarrotアプリケーションのURLを貼り付ける関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_carrot_url ($params, &$smarty) {
	$params = C\Tuple::create($params);

	if (C\StringUtils::isBlank($params['contents'])) {
		$url = C\URL::create(null, 'carrot');
	} else {
		$url = C\URL::create($params['contents']);
	}

	if (!$params['generic_ua']) {
		if (!C\StringUtils::isBlank($name = $params[C\UserAgent::ACCESSOR])) {
			$useragent = C\UserAgent::create($name);
			$url->setParameter(C\UserAgent::ACCESSOR, $name);
		} else {
			$useragent = $smarty->getUserAgent();
		}
		$url->setUserAgent($useragent);
	}

	return $url->getContents();
}
