<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * Content-Transfer-Encodingヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ContentTransferEncodingMIMEHeader extends MIMEHeader {
	protected $name = 'Content-Transfer-Encoding';

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if ($contents instanceof Renderer) {
			$this->contents = self::getContentTransferEncoding($contents);
		} else {
			$this->contents = StringUtils::toLower($contents);
		}
	}

	/**
	 * レンダラーのContent-Transfer-Encodingを返す
	 *
	 * @access public
	 * @param Renderer $renderer レンダラー
	 * @return string Content-Transfer-Encoding
	 * @static
	 */
	static public function getContentTransferEncoding (Renderer $renderer) {
		if ($renderer instanceof TextRenderer) {
			if (StringUtils::toLower($renderer->getEncoding()) == 'iso-2022-jp') {
				return '7bit';
			}
			return '8bit';
		}
		return 'base64';
	}
}
