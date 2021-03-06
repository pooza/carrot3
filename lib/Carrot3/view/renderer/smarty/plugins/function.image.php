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

	$manager = $smarty->getUserAgent()->createImageManager((int)$params['flags']);
	if (($record = $manager->search($params))
		&& ($record instanceof C\ImageContainer)
		&& ($info = $manager->getInfo($record, $params['size'], $params['pixel']))) {

		$element = $manager->createElement($info);
		$element->setAttribute('align', $params['align']);
		$element->setStyles($params['style']);
		$element->registerStyleClass($params['style_class']);
		$element->setID($params['container_id']);

		switch ($mode = C\StringUtils::toLower($params['mode'])) {
			case 'lightbox':
			case 'lity':
				$anchor = C\Loader::getInstance()->createObject($mode . 'AnchorElement');
				$element = $element->wrap($anchor);
				try {
					$element->setImage(
						$record,
						$params['size'],
						(int)$params['pixel_full'],
						$manager->createBits($params['flags_full'])
					);
				} catch (\Throwable $e) {
					$record->removeImageCache($params['size']);
					$element = new C\DivisionElement;
					$element->setBody($e->getMessage());
				}
				break;
		}
		return $element->getContents();
	}
}
