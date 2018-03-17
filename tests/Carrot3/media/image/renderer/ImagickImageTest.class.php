<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ImagickImageTest extends Test {
	public function execute () {
		if (extension_loaded('imagick')) {
			$image = new ImagickImage;
			$this->assert('getGDHandle', is_resource($image->getGDHandle()));
			$this->assert('resize', !$image->resize(16, 16));
			$this->assert('getWidth', $image->getWidth() == 16);
			$this->assert('getHeight', $image->getHeight() == 16);
			$this->assert('setType', !$image->setType('image/vnd.microsoft.icon'));
			$this->assert('getType', $image->getType() == 'image/vnd.microsoft.icon');
		}
	}
}
