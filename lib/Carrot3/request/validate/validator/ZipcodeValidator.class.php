<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 郵便番号バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ZipcodeValidator extends RegexValidator {
	const PATTERN = '^([[:digit:]]{3})-([[:digit:]]{4})$';

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['match'] = true;
		$this['match_error'] = '正しくありません。';
		$this['pattern'] = self::PATTERN;
		$this['fields'] = [];
		return Validator::initialize($params);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return bool 妥当な値ならばTrue
	 */
	public function execute ($value) {
		if ($fields = $this['fields']) {
			$values = Tuple::create();
			foreach ($fields as $field) {
				$values[] = $this->request[$field];
			}
			if (StringUtils::isBlank($values->join(''))) {
				return true;
			}
			$value = $values->join('-');
		}
		return parent::execute($value);
	}
}
