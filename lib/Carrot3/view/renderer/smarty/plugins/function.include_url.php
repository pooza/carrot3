<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\Tuple;
use \Carrot3\StringUtils;
use \Carrot3\URL;
use \Carrot3\Controller;

/**
 * 外部コンテンツをインクルード
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_include_url ($params, &$smarty) {
	$params = Tuple::create($params);

	if (StringUtils::isBlank($params['src'])) {
		$url = URL::create($params, 'carrot');
	} else {
		$url = URL::create($params['src']);
	}
	if (!$url) {
		return null;
	}

	if (!$url['host']->isForeign(Controller::getInstance()->getHost())) {
		$url->setUserAgent($smarty->getUserAgent());
	}
	return $url->fetch();
}

