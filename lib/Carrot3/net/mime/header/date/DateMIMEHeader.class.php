<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header.date
 */

namespace Carrot3;

/**
 * Dateヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DateMIMEHeader extends MIMEHeader {
	protected $name = 'Date';
	private $date;

	/**
	 * 実体を返す
	 *
	 * @access public
	 * @return Date 実体
	 */
	public function getEntity () {
		return $this->date;
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if ($contents instanceof Date) {
			$contents = $contents->format('r');
		}
		parent::setContents($contents);
	}

	/**
	 * ヘッダの内容からパラメータを抜き出す
	 *
	 * @access protected
	 */
	protected function parse () {
		parent::parse();
		$this->date = Date::create($this->contents);
	}
}
