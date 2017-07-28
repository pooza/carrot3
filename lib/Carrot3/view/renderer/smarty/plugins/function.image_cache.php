<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\Tuple;
use \Carrot3\StringUtils;
use \Carrot3\Loader;

/**
 * キャッシュ画像関数
 *
 * ImageManagerのフロントエンド
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_image_cache ($params, &$smarty) {
	$params = Tuple::create($params);
	if (StringUtils::isBlank($params['size'])) {
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

		switch ($mode = StringUtils::toLower($params['mode'])) {
			case 'size':
				return $info['pixel_size'];
			case 'pixel_size':
			case 'width':
			case 'height':
			case 'url':
				return $info[$mode];
			case 'lightbox':
			case 'lity':
				$anchor = Loader::getInstance()->createObject($mode . 'AnchorElement');
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

