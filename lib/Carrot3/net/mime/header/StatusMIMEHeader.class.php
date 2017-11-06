<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * Statusヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class StatusMIMEHeader extends MIMEHeader {
	protected $name = 'Status';

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if (mb_ereg('^([[:digit:]]{3}) ', $contents, $matches)) {
			return $this->setContents($matches[1]);
		} else if (!is_numeric($contents)) {
			$message = new StringFormat('ステータス"%s"は正しくありません。');
			$message[] = $contents;		
			throw new HTTPException($message);
		}

		$this['code'] = $contents;
		if (StringUtils::isBlank($status = HTTP::getStatus($contents))) {
			$message = new StringFormat('ステータス"%s"は正しくありません。');
			$message[] = $contents;		
			throw new HTTPException($message);
		}
		parent::setContents($status);
	}
}
