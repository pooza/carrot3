<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 日付バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DateValidator extends Validator {
	protected function getDate ($value) {
		if ($fields = $this['fields']) {
			$date = Date::create();
			foreach ($fields as $key => $value) {
				$date[$key] = $this->request[$value];
			}
		} else {
			try {
				$date = Date::create($value);
			} catch (DateException $e) {
				return null;
			}
		}
		if ($date && $date->validate()) {
			return $date;
		}
	}

	/**
	 * 初期化
	 *
	 * @access public
	 * @param string[] $params パラメータ配列
	 */
	public function initialize ($params = []) {
		$this['fields'] = [];
		$this['invalid_error'] = '正しい日付ではありません。';
		$this['today'] = true;
		$this['today_error'] = '当日の日付は選べません。';
		$this['past'] = true;
		$this['past_error'] = '過去の日付は選べません。';
		$this['future'] = true;
		$this['future_error'] = '未来の日付は選べません。';
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
		if (!$date = $this->getDate($value)) {
			$this->error = $this['invalid_error'];
			return false;
		} else if (!$this['today'] && $date->isToday()) {
			$this->error = $this['today_error'];
			return false;
		} else if (!$this['past'] && $date->isPast()) {
			$this->error = $this['past_error'];
			return false;
		} else if (!$this['future'] && !$date->isPast()) {
			$this->error = $this['future_error'];
			return false;
		}
		return true;
	}
}
