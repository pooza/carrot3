<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage string
 */

namespace Carrot3;

/**
 * フォーマット化文字列
 *
 * sprintfのラッパー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class StringFormat extends Tuple implements MessageContainer {

	/**
	 * @access public
	 * @param mixed $params 要素の配列
	 */
	public function __construct ($params = []) {
		parent::__construct($params);
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string 内容
	 */
	public function getContents ():string {
		try {
			return call_user_func_array('sprintf', $this->getParameters());
		} catch (\Exception $e) {
			return $this->join(', ');
		}
	}

	/**
	 * メッセージ文字列を返す
	 *
	 * @access public
	 * @return string メッセージ文字列
	 */
	public function getMessage ():string {
		return $this->getContents();
	}

	/**
	 * @access public
	 * @return string
	 */
	public function __toString () {
		return $this->getContents();
	}
}
