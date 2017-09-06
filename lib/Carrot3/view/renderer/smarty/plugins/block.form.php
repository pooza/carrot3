<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * form要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_block_form ($params, $contents, &$smarty) {
	$params = C\Tuple::create($params);
	$useragent = $smarty->getUserAgent();
	$form = new C\FormElement(null, $useragent);
	$form->setBody($contents);

	if (C\StringUtils::isBlank($params['method'])) {
		$params['method'] = 'POST';
	}
	if (!!$params['send_submit_values']) {
		$form->addSubmitFields();
	}
	if ($params['onsubmit']) {
		$form->setAttribute('onsubmit', $params['onsubmit']);
	}
	$form->setMethod($params['method']);
	if (!!$params['attachable']) {
		$form->setAttachable(true);
		if (!C\StringUtils::isBlank($size = $params['attachment_size'])) {
			$form->addHiddenField('MAX_FILE_SIZE', $size * 1024 * 1024);
		}
	}
	$form->setAction($params);

	$params->removeParameter('scheme');
	$params->removeParameter('method');
	$params->removeParameter('attachable');
	$params->removeParameter('path');
	$params->removeParameter('module');
	$params->removeParameter('action');
	$form->setAttributes($params);

	return $form->getContents();
}

