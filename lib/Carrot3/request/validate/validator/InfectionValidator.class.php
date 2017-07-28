<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 感染バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class InfectionValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['infection_error'] = '感染の疑いがあります。';
		return Validator::initialize($params);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return boolean 妥当な値ならばTrue
	 */
	public function execute ($value) {
		$file = new File($value['tmp_name']);
		if ($file->isInfected()) {
			$message = new StringFormat('%s (%s)');
			$message[] = $this['infection_error'];
			$message[] = $file->getError();
			$this->error = $message->getContents();
		}
		return StringUtils::isBlank($this->error);
	}
}

