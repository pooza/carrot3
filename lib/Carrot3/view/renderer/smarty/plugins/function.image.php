<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * キャッシュ画像関数
 *
 * ImageManagerのフロントエンド
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_image ($params, &$smarty) {
	$params = C\Tuple::create($params);
	if (C\StringUtils::isBlank($params['size'])) {
		$params['size'] = 'image';
	}

	$manager = $smarty->getUserAgent()->createImageManager($params['flags']);
	if (($record = $manager->getContainer($params))
		&& ($info = $manager->getImageInfo($record, $params['size'], $params['pixel']))) {

		$element = $manager->createElement($info);
		$element->setAttribute('align', $params['align']);
		$element->setStyles($params['style']);
		$element->registerStyleClass($params['style_class']);
		$element->setID($params['container_id']);

		switch ($mode = C\StringUtils::toLower($params['mode'])) {
			case 'size':
				return $info['pixel_size'];
			case 'pixel_size':
			case 'width':
			case 'height':
			case 'url':
				return $info[$mode];
			case 'lightbox':
			case 'lity':
				$anchor = C\Loader::getInstance()->createObject($mode . 'AnchorElement');
				$element = $element->wrap($anchor);
				$element->setCaption($info['alt']);
				$element->setImage(
					$record, $params['size'], $params['pixel_full'], $params['flags_full']
				);
				break;
		}
		return $element->getContents();
	}
}

