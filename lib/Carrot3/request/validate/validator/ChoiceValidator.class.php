<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 選択バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ChoiceValidator extends Validator {

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['class'] = null;
		$this['function'] = 'getStatusOptions';
		$this['choices'] = null;
		$this['choices_error'] = '正しくありません。';
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
		$choices = Tuple::create($value);
		$choices->trim();
		foreach ($choices as $choice) {
			if (!$this->getChoices()->isContain($choice)) {
				$this->error = $this['choices_error'];
				return false;
			}
		}
		return true;
	}

	protected function getChoices () {
		$choices = Tuple::create();
		if ($config = $this['choices']) {
			if (is_array($config) || ($config instanceof ParameterHolder)) {
				$choices->setParameters($config);
			} else {
				$choices = StringUtils::explode(',', $config);
			}
		} else if ($this['class']) {
			try {
				$class = $this->loader->getClass($this['class'] . TableHandler::CLASS_SUFFIX);
			} catch (\Exception $e) {
				$class = $this->loader->getClass($this['class']);
			}
			$choices->setParameters(call_user_func([$class, $this['function']]));
			$choices = $choices->getKeys();
		}
		return $choices;
	}
}
