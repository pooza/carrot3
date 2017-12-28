<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 動画バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MovieValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['invalid_error'] = '正しいファイルではありません。';
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
		try {
			$file = new MovieFile($value['tmp_name']);
			if (!$file->isExists() || !$file->validate()) {
				$this->error = $this['invalid_error'];
				if (!StringUtils::isBlank($error = $file->getError())) {
					$this->error .= '(' . $error . ')';
				}
			}
		} catch (\Exception $e) {
			$this->error = $this['invalid_error'] . '(' . $e->getMessage() . ')';
		}
		return StringUtils::isBlank($this->error);
	}
}
