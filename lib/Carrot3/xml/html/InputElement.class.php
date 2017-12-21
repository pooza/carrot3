<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html
 */

namespace Carrot3;

/**
 * input要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class InputElement extends HTMLElement {

	/**
	 * @access public
	 * @param string $name 要素の名前
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function __construct ($name = null, UserAgent $useragent = null) {
		parent::__construct($name, $useragent);
		$this->setType('text');
	}

	/**
	 * タグ名を返す
	 *
	 * @access public
	 * @return string タグ名
	 */
	public function getTag () {
		return 'input';
	}

	/**
	 * 空要素か？
	 *
	 * @access public
	 * @return boolean 空要素ならTrue
	 */
	public function isEmptyElement () {
		return true;
	}

	/**
	 * タイプを設定
	 *
	 * @access public
	 * @param string $type
	 */
	public function setType ($type) {
		$this->setAttribute('type', $type);
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性名
	 * @param mixed $value 属性値
	 */
	public function setAttribute ($name, $value) {
		parent::setAttribute($name, $value);
		switch ($name) {
			case 'name':
				if (StringUtils::isBlank($this->getID())) {
					$this->setID($value . '_text');
				}
				break;
		}
	}
}
