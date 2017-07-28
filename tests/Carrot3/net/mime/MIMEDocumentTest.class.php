<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MIMEDocumentTest extends Test {
	public function execute () {
		$mime = new MIMEDocument;
		$file = FileUtils::getDirectory('sample')->getEntry('spam.eml');
		$mime->setContents($file->getContents());
		$this->assert('getDate', $mime->getDate()->format('Ymd') == '20110605');
	}
}
