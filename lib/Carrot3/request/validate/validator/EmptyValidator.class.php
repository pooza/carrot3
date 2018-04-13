<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 必須バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class EmptyValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 */
	public function initialize (?iterable $params = []) {
		$this['required_msg'] = '空欄です。';
		return parent::initialize($params);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return bool 妥当な値ならばTrue
	 */
	public function execute ($value) {
		if (self::isEmpty($value)) {
			$this->error = $this['required_msg'];
			return false;
		}
		return true;
	}

	/**
	 * フィールド値は空欄か？
	 *
	 * @access public
	 * @return bool フィールド値が空欄ならばTrue
	 * @static
	 */
	static public function isEmpty ($value):bool {
		if (is_iterable($value)) {
			$value = Tuple::create($value);
			if ($value['is_file']) {
				return StringUtils::isBlank($value['name']);
			} else {
				$value->trim();
				return !$value->count();
			}
		} else {
			return StringUtils::isBlank($value);
		}
	}
}
