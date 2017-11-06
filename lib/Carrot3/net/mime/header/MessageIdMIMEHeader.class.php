<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * Message-IDヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MessageIdMIMEHeader extends MIMEHeader {
	protected $name = 'Message-ID';
	private $id;

	/**
	 * 実体を返す
	 *
	 * @access public
	 * @return Date 実体
	 */
	public function getEntity () {
		return $this->id;
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if (StringUtils::isBlank($contents)) {
			$id = new StringFormat('%s.%s@%s');
			$id[] = Date::create()->format('YmdHis');
			$id[] = Utils::getUniqueID();
			$id[] = $this->controller->getHost()->getName();
			$this->id = $id->getContents();
		} else {
			mb_ereg('^<?([^>]*)>?$', $contents, $matches);
			$this->id = $matches[1];
		}
		$this->contents = '<' . $this->id . '>';
	}

	/**
	 * 改行などの整形を行うか？
	 *
	 * @access protected
	 * @return boolean 整形を行うならTrue
	 */
	protected function isFormattable () {
		return false;
	}
}
