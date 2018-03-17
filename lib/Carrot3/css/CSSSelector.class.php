<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage css
 */

namespace Carrot3;

/**
 * CSSセレクタレンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class CSSSelector extends Tuple {

	/**
	 * @access public
	 * @param mixed[] $params 要素の配列
	 */
	public function __construct ($params = []) {
		parent::__construct($params);
	}

	/**
	 * 要素を設定
	 *
	 * @access public
	 * @param string $name 名前
	 * @param mixed $value 要素
	 * @param bool $position 先頭ならTrue
	 */
	public function setParameter ($name, $value, bool $position = self::POSITION_BOTTOM) {
		if ($value instanceof Color) {
			$value = $value->getContents();
		} else if (is_numeric($value)) {
			$value .= 'px';
		}
		if (($name = trim($name)) && ($value = trim($value))) {
			parent::setParameter($name, $value, $position);
		}
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string 内容
	 */
	public function getContents () {
		return $this->join('; ', ':');
	}

	/**
	 * 文字列をパースし、属性を設定
	 *
	 * @access public
	 * @param string $contents 内容
	 */
	public function setContents ($contents) {
		foreach (StringUtils::explode(';', $contents) as $param) {
			$param = StringUtils::explode(':', $param);
			$this[$param[0]] = $param[1];
		}
	}
}
