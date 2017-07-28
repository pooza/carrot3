<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * 外部コンテンツをインクルード
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_include_url ($params, &$smarty) {
	$params = C\Tuple::create($params);

	if (C\StringUtils::isBlank($params['src'])) {
		$url = C\URL::create($params, 'carrot');
	} else {
		$url = C\URL::create($params['src']);
	}
	if (!$url) {
		return null;
	}

	if (!$url['host']->isForeign(C\Controller::getInstance()->getHost())) {
		$url->setUserAgent($smarty->getUserAgent());
	}
	return $url->fetch();
}

