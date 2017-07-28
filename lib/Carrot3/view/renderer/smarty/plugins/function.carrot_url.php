<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\Tuple;
use \Carrot3\StringUtils;
use \Carrot3\URL;
use \Carrot3\UserAgent;

/**
 * CarrotアプリケーションのURLを貼り付ける関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_carrot_url ($params, &$smarty) {
	$params = Tuple::create($params);

	if (StringUtils::isBlank($params['contents'])) {
		$url = URL::create($params, 'carrot');
	} else {
		$url = URL::create($params['contents']);
	}

	if (!$params['generic_ua']) {
		if (!StringUtils::isBlank($name = $params[UserAgent::ACCESSOR])) {
			$useragent = UserAgent::create($name);
			$url->setParameter(UserAgent::ACCESSOR, $name);
		} else {
			$useragent = $smarty->getUserAgent();
		}
		$url->setUserAgent($useragent);
	}

	return $url->getContents();
}

