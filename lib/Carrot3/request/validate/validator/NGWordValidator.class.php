<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * NGワードバリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class NGWordValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['word_error'] = '不適切な言葉が含まれています。';
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
		$words = Tuple::create();
		foreach (['carrot', 'application'] as $name) {
			$config = ConfigManager::getInstance()->compile('ng_word/' . $name);
			$words->merge($config['words']);
		}
		foreach ($words as $word) {
			if (StringUtils::isContain($word, $value)) {
				$this->error = $this['word_error'];
				break;
			}
		}
		return StringUtils::isBlank($this->error);
	}
}

