<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 時刻バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TimeValidator extends Validator {

	/**
	 * 対象文字列から時刻を返す
	 *
	 * fiedlsパラメータが設定されている時はそちらを利用し、対象文字列を無視。
	 *
	 * @access protected
	 * @param string $value 対象文字列
	 * @return string 時刻
	 */
	protected function getTime ($value) {
		try {
			if ($fields = $this['fields']) {
				$date = Date::create()->clearTime();
				foreach ($fields as $key => $value) {
					$date[$key] = $this->request[$value];
				}
			} else {
				$date = Date::create($value);
			}
			if ($date && $date->validate()) {
				return $date->format('H:i:s');
			}
		} catch (DateException $e) {
		}
	}

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 */
	public function initialize (?iterable $params = []) {
		$this['fields'] = [];
		$this['invalid_error'] = '正しい時刻ではありません。';
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
		if (!$date = $this->getTime($value)) {
			$this->error = $this['invalid_error'];
			return false;
		}
		return true;
	}
}
