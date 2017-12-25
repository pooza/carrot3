<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class AACMediaConvertorTest extends Test {
	public function execute () {
		$convertor = new AACMediaConvertor;
		if ($file = FileUtils::getDirectory('sample')->getEntry('roper.mp3')) {
			$source = $file->copyTo(FileUtils::getDirectory('tmp'), 'MusicFile');
			$dest = $convertor->execute($source);
			$this->assert('analyzeType', ($dest->analyzeType() == 'audio/x-aac'));
			$source->delete();
			$dest->delete();
		}
	}
}
