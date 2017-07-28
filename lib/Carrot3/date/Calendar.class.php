<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage date
 */

namespace Carrot3;

/**
 * カレンダー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Calendar extends ParameterHolder {
	private $start;
	private $end;

	/**
	 * @access public
	 * @param Date $start 開始日
	 * @param Date $end 終了日
	 */
	public function __construct (Date $start, Date $end) {
		if (!$start->isPast($end)) {
			throw new DateException('期間が正しくありません。');
		}

		$this->start = $start;
		$this->end = $end;
		$date = clone $this->getStartDate();
		while ($date->getTimestamp() <= $this->getEndDate()->getTimestamp()) {
			$values = Tuple::create($date);
			$values['date'] = $date->format('Y-m-d');
			$values['today'] = $date->isToday();
			$values['holiday'] = $date->isHoliday();
			$values['holiday_name'] = $date->getHolidayName();
			$values['weekday'] = $date->getWeekday();
			$values['weekday_name'] = $date->getWeekdayName();
			$this->params[$date->format('Y-m-d')] = $values;
			$date['day'] = '+1';
		}
	}

	/**
	 * 開始日を返す
	 *
	 * @access public
	 * @return Date 開始日
	 */
	public function getStartDate () {
		return $this->start;
	}

	/**
	 * 終了日を返す
	 *
	 * @access public
	 * @return Date 終了日
	 */
	public function getEndDate () {
		return $this->end;
	}

	/**
	 * パラメータを返す
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @return mixed パラメータ
	 */
	public function getParameter ($name) {
		if ($name instanceof Date) {
			$name = $name->format('Y-m-d');
		}
		return parent::getParameter($name);
	}

	/**
	 * パラメータを設定
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @param mixed $value 値
	 */
	public function setParameter ($name, $value) {
		if ($name instanceof Date) {
			$name = $name->format('Y-m-d');
		}
		if ($this->hasParameter($name)) {
			parent::setParameter($name, $value);
		}
	}

	/**
	 * パラメータが存在するか？
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @return boolean 存在すればTrue
	 */
	public function hasParameter ($name) {
		if ($name instanceof Date) {
			$name = $name->format('Y-m-d');
		}
		return parent::hasParameter($name);
	}

	/**
	 * パラメータを削除
	 *
	 * @access public
	 * @param string $name パラメータ名
	 */
	public function removeParameter ($name) {
		throw new DateException('カレンダーから日付を削除できません。');
	}
}

