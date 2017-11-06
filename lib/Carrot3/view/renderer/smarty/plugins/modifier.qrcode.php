<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * qrcode修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_qrcode ($value) {
	$url = C\URL::create(null, 'carrot');
	$url['module'] = 'Default';
	$url['action'] = 'QRCode';
	$url->setParameter('value', $value);

	$element = new C\ImageElement;
	$element->setURL($url);
	return $element->getContents();
}
