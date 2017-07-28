<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * Content-Dispositionヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ContentDispositionMIMEHeader extends MIMEHeader {
	protected $name = 'Content-Disposition';

	/**
	 * ヘッダの内容からパラメータを抜き出す
	 *
	 * @access protected
	 */
	protected function parse () {
		parent::parse();
		if ($this['filename'] && ($part = $this->getPart()) && !$part->getFileName()) {
			$part->setFileName($this['filename']);
		}
	}
}

