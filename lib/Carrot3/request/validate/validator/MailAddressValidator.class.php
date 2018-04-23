<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * メールアドレスバリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MailAddressValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 * @return bool
	 */
	public function initialize (?iterable $params = []):bool {
		$this['invalid_error'] = '正しいメールアドレスではありません。';
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
		if (!extension_loaded('filter')) {
			throw new ValidateException('filterモジュールがロードされていません。');
		}
		if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$this->error = $this['invalid_error'];
			return false;
		}
		return true;
	}
}
