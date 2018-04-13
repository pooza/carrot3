<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * Subjectヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SubjectMIMEHeader extends MIMEHeader {
	protected $name = 'Subject';

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string 内容
	 */
	public function getContents ():string {
		if (BS_DEBUG) {
			return '[TEST] ' . $this->contents;
		}
		return $this->contents;
	}
}
