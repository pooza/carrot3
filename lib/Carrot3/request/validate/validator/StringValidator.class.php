<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 文字列バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class StringValidator extends Validator {
	const MAX_SIZE = 1024;

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['max'] = self::MAX_SIZE;
		$this['max_error'] = '長すぎます。';
		$this['min'] = null;
		$this['min_error'] = '短すぎます。';
		$this['invalid_error'] = '正しくありません。';
		return parent::initialize($params);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return boolean 妥当な値ならばTrue
	 */
	public function execute ($value) {
		if (is_array($value) || ($value instanceof ParameterHolder)) {
			$value = Tuple::create($value);
			$value->trim();
			foreach ($value as $entry) {
				$this->execute($entry);
			}
		} else {
			if (!mb_check_encoding($value)) {
				$this->error = $this['invalid_error'];
			}
			if (!StringUtils::isBlank($this['min']) && (StringUtils::getWidth($value) < $this['min'])) {
				$this->error = $this['min_error'];
			}
			if (!StringUtils::isBlank($this['max']) && ($this['max'] < StringUtils::getWidth($value))) {
				$this->error = $this['max_error'];
			}
		}
		return StringUtils::isBlank($this->error);
	}
}
