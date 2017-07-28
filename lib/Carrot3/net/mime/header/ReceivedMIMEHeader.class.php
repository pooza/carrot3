<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * Receivedヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ReceivedMIMEHeader extends MIMEHeader {
	protected $name = 'Received';
	private $servers;

	/**
	 * @access protected
	 */
	protected function __construct () {
		parent::__construct();
		$this->servers = Tuple::create();
	}

	/**
	 * 実体を返す
	 *
	 * @access public
	 * @return mixed 実体
	 */
	public function getEntity () {
		return $this->servers;
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string 内容
	 */
	public function getContents () {
		return $this->servers->join("\n");
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		parent::setContents($contents);
		$this->servers[] = $this->contents;
	}

	/**
	 * 内容を追加
	 *
	 * @access public
	 * @param string $contents 内容
	 */
	public function appendContents ($contents) {
		parent::appendContents($contents);
		$this->servers[] = $this->contents;
	}

	/**
	 * 複数行を許容するか？
	 *
	 * @access public
	 * @return boolean 許容ならばTrue
	 */
	public function isMultiple () {
		return true;
	}

	/**
	 * 可視か？
	 *
	 * @access public
	 * @return boolean 可視ならばTrue
	 */
	public function isVisible () {
		return false;
	}
}

