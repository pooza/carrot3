<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * フリガナ項目バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class KanaValidator extends RegexValidator {
	const PATTERN = '^[ぁ-んァ-ンヴー\\n[:digit:]]*$';

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['match'] = true;
		$this['match_error'] = '使用出来ない文字が含まれています。';
		$this['pattern'] = self::PATTERN;
		return Validator::initialize($params);
	}
}

