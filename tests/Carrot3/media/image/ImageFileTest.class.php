<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ImageFileTest extends Test {
	public function execute () {
		$dir = FileUtils::getDirectory('sample');
		$src = $dir->getEntry('uproda258674.jpg', 'ImageFile');

		if (extension_loaded('imagick')) {
			$dest = FileUtils::createTemporaryFile('ico');
			$dest->setContents($src->getContents());
			$this->assert('__construct', $dest = new ImageFile($dest->getPath(), 'ImagickImage'));
			$this->assert('setType', !$dest->getRenderer()->setType('image/vnd.microsoft.icon'));
			$this->assert('getType', $dest->getRenderer()->getType() == 'image/vnd.microsoft.icon');
			$dest->getRenderer()->resize(57, 57);
			$this->assert('getWidth', $dest->getRenderer()->getWidth() == 57);
			$this->assert('getHeight', $dest->getRenderer()->getHeight() == 57);
			$dest->save();
			$dest->delete();
		}
	}
}
