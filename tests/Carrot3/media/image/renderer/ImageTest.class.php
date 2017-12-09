<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ImageTest extends Test {
	public function execute () {
		$this->assert('getTypes', Image::getTypes()->isContain('image/jpeg'));
		$this->assert('getSuffixes', Image::getSuffixes()->isContain('.gif'));

		$dir = FileUtils::getDirectory('root');
		$src = $dir->getEntry('www/carrotlib/images/button/line.gif', 'ImageFile');
		$dest = FileUtils::createTemporaryFile('gif', 'ImageFile');
		$dest->setContents($src->getContents());
		$this->assert('getType', $dest->getType() == 'image/gif');
		$dest->getRenderer()->resize(57, 57);
		$dest->save();
		$this->assert('getWidth', $dest->getRenderer()->getWidth() == 57);
		$this->assert('getHeight', $dest->getRenderer()->getHeight() == 57);
		$dest->delete();
	}
}
