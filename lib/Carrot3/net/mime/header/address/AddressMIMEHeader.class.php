<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header.address
 */

namespace Carrot3;

/**
 * メールアドレスを格納する抽象ヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class AddressMIMEHeader extends MIMEHeader {
	protected $email;

	/**
	 * 実体を返す
	 *
	 * @access public
	 * @return MailAddress 実体
	 */
	public function getEntity () {
		return $this->email;
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if ($contents instanceof MailAddress) {
			$contents = $contents->format();
		}
		parent::setContents($contents);
	}

	/**
	 * 内容を追加
	 *
	 * @access public
	 * @param string $contents 内容
	 */
	public function appendContents ($contents) {
		if ($contents instanceof MailAddress) {
			$contents = $contents->format();
		}
		parent::appendContents($contents);
	}

	/**
	 * ヘッダの内容からパラメータを抜き出す
	 *
	 * @access protected
	 */
	protected function parse () {
		parent::parse();
		$this->email = MailAddress::create($this->contents);
	}
}

