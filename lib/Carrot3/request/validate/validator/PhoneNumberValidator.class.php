<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 電話番号バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @link http://www.soumu.go.jp/main_sosiki/joho_tsusin/top/tel_number/ 電話番号の桁数について
 */
class PhoneNumberValidator extends RegexValidator {
	const PATTERN = '^[[:digit:]]{2,4}-[[:digit:]]{2,4}-[[:digit:]]{3,4}$';
	const PATTERN_LOOSE = '^[-[:digit:]]{10,14}$';

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 * @return bool
	 */
	public function initialize (?iterable $params = []):bool {
		if (!isset($params['loose'])) {
			$params['loose'] = false;
		}
		if (!!$params['loose']) {
			$params['pattern'] = self::PATTERN_LOOSE;
		}

		$this['match'] = true;
		$this['match_error'] = '正しくありません。';
		$this['fields'] = [];
		$this['pattern'] = self::PATTERN;
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
