<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * X-Priorityヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class XPriorityMIMEHeader extends MIMEHeader {
	protected $name = 'X-Priority';

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if (!in_array($contents, range(1, 5))) {
			$message = new StringFormat('優先順位"%d"が正しくありません。');
			$message[] = $contents;
			throw new MailException($message);
		}
		parent::setContents($contents);
	}
}
