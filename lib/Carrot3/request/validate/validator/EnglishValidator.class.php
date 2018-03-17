<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 英字項目バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class EnglishValidator extends RegexValidator {
	const PATTERN = '^[\\n[:ascii:]]*$';

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 */
	public function initialize (?iterable $params = []) {
		$this['match'] = true;
		$this['match_error'] = '使用出来ない文字が含まれています。';
		$this['pattern'] = self::PATTERN;
		return Validator::initialize($params);
	}
}
