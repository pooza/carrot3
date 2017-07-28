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
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['invalid_error'] = '正しいメールアドレスではありません。';
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
		if (!$email = MailAddress::create($value)) {
			$this->error = $this['invalid_error'];
			return false;
		}
		return true;
	}
}

