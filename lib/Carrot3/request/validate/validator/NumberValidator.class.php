<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 数値バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class NumberValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['max'] = null;
		$this['max_error'] = '値が大きすぎます。';
		$this['min'] = null;
		$this['min_error'] = '値が小さすぎます。';
		$this['nan_error'] = '数値を入力して下さい。';
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
		if (!is_numeric($value)) {
			$this->error = $this['nan_error'];
			return false;
		}

		$min = $this['min'];
		if (!!$min && ($value < $min)) {
			$this->error = $this['min_error'];
			return false;
		}

		$max = $this['max'];
		if (!!$max && ($max < $value)) {
			$this->error = $this['max_error'];
			return false;
		}

		return true;
	}
}
