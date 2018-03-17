<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage date
 */

namespace Carrot3;

/**
 * 日付
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Date extends ParameterHolder implements Assignable {
	use BasicObject;
	const MON = 1;
	const TUE = 2;
	const WED = 3;
	const THU = 4;
	const FRI = 5;
	const SAT = 6;
	const SUN = 7;
	const TIMESTAMP = 1;
	static private $gengos;

	/**
	 * @access private
	 * @param string $date 日付文字列
	 * @param int $flags フラグのビット列
	 *   self::TIMESTAMP タイムスタンプ形式
	 */
	private function __construct ($date, $flags) {
		if (StringUtils::isBlank($date)) {
			$this->setTimestamp($_SERVER['REQUEST_TIME']);
		} else if ($flags & self::TIMESTAMP){
			$this->setTimestamp($date);
		} else if ($time = strtotime($date)) {
			$this->setTimestamp($time);
		} else {
			$date = mb_ereg_replace('[^[:digit:]]+', '', $date);
			$this->params['year'] = (int)substr($date, 0, 4);
			$this->params['month'] = (int)substr($date, 4, 2);
			$this->params['day'] = (int)substr($date, 6, 2);
			$this->params['hour'] = (int)substr($date, 8, 2);
			$this->params['minute'] = (int)substr($date, 10, 2);
			$this->params['second'] = (int)substr($date, 12, 2);
			if (!$this->validate()) {
				throw new DateException('日付が正しくありません。');
			}
		}
	}

	/**
	 * インスタンスを生成して返す
	 *
	 * @param string $date 日付文字列
	 * @return Date インスタンス
	 * @param int $flags フラグのビット列
	 *   self::TIMESTAMP タイムスタンプ形式
	 * @static
	 */
	static public function create ($date = null, int $flags = 0) {
		if ($date instanceof Date) {
			return $date;
		}
		try {
			$date = new self($date, $flags);
			if ($date->validate()) {
				return $date;
			}
		} catch (DateException $e) {
		}
	}

	/**
	 * パラメータを設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 属性の値、(+|-)で始まる文字列も可。
	 * @return Date 適用後の自分自身
	 */
	public function setParameter ($name, $value) {
		$name = StringUtils::toLower((string)$name);
		$this->params['timestamp'] = null;
		$this->params['weekday'] = null;
		$this->params['weekday_name'] = null;
		$this->params['gengo'] = null;
		$this->params['japanese_year'] = null;

		if (in_array($value[0], ['+', '-'])) {
			foreach (['hour', 'minute', 'second', 'month', 'day', 'year'] as $field) {
				$$field = $this[$field];
				if ($field == $name) {
					$$field += (int)$value;
				}
			}
			$this->setTimestamp(mktime($hour, $minute, $second, $month, $day, $year));
		} else {
			parent::setParameter($name, $value);
		}
		return $this;
	}

	/**
	 * UNIXタイムスタンプを返す
	 *
	 * @access public
	 * @return int UNIXタイムスタンプ
	 */
	public function getTimestamp () {
		if (StringUtils::isBlank($this['timestamp'])) {
			$this->params['timestamp'] = mktime(
				$this['hour'], $this['minute'], $this['second'],
				$this['month'], $this['day'], $this['year']
			);
		}
		return $this['timestamp'];
	}

	/**
	 * UNIXタイムスタンプを設定
	 *
	 * @access public
	 * @param int $timestamp UNIXタイムスタンプ
	 * @return Date 適用後の自分自身
	 */
	public function setTimestamp (int $timestamp) {
		$info = getdate($timestamp);
		$this->params['year'] = $info['year'];
		$this->params['month'] = $info['mon'];
		$this->params['day'] = $info['mday'];
		$this->params['hour'] = $info['hours'];
		$this->params['minute'] = $info['minutes'];
		$this->params['second'] = $info['seconds'];
		$this->params['timestamp'] = $timestamp;
		$this->params['weekday'] = null;
		$this->params['weekday_name'] = null;
		$this->params['gengo'] = null;
		$this->params['japanese_year'] = null;
		return $this;
	}

	/**
	 * 時刻を0:00に設定し、返す
	 *
	 * @access public
	 * @return Date 自分自身
	 */
	public function clearTime () {
		$this->params['hour'] = 0;
		$this->params['minute'] = 0;
		$this->params['second'] = 0;
		return $this;
	}

	/**
	 * 日付の妥当性をチェック
	 *
	 * @access public
	 * @return bool 妥当な日付ならtrue
	 */
	public function validate () {
		return (checkdate($this['month'], $this['day'], $this['year'])
			&& (0 <= $this['hour']) && ($this['hour'] <= 23)
			&& (0 <= $this['minute']) && ($this['minute'] <= 59)
			&& (0 <= $this['second']) && ($this['second'] <= 59)
			&& ($this->getTimestamp() !== false)
		);
	}

	/**
	 * 指定日付よりも過去か？
	 *
	 * 配列が与えられたら、その中の最新日付と比較。
	 *
	 * @access public
	 * @param mixed $date 比較対象の日付またはその配列
	 * @return bool 過去日付ならtrue
	 */
	public function isPast ($date = null) {
		if (StringUtils::isBlank($date)) {
			$date = self::create();
		} else if (!($date instanceof Date)) {
			if (!$date = self::create($date)) {
				throw new DateException('日付が正しくありません。');
			}
		}
		return ($this->getTimestamp() < $date->getTimestamp());
	}

	/**
	 * 今日か？
	 *
	 * @access public
	 * @param Date $now 比較対象の日付
	 * @return bool 今日の日付ならtrue
	 */
	public function isToday (Date $now = null) {
		if (!$now) {
			$now = self::create();
		}
		return ($this->format('Ymd') == $now->format('Ymd'));
	}

	/**
	 * 年数（年齢）を返す
	 *
	 * @access public
	 * @param Date $now 比較対象の日付
	 * @return int 年数
	 */
	public function getAge (Date $now = null) {
		if (!$now) {
			$now = self::create();
		}

		$age = $now['year'] - $this['year'];
		if ($now['month'] < $this['month']) {
			$age --;
		} else if (($now['month'] == $this['month']) && ($now['day'] < $this['day'])) {
			$age --;
		}
		return $age;
	}

	/**
	 * 月末日付を返す
	 *
	 * @access public
	 * @return Date 月末日付
	 */
	public function getLastDateOfMonth () {
		return self::create($this->format('Ymt'));
	}

	/**
	 * 週末日付を返す
	 *
	 * @access public
	 * @param int $weekday 曜日
	 * @return Date 週末日付
	 */
	public function getLastDateOfWeek (int $weekday = self::SUN) {
		if (($weekday < self::MON) || (self::SUN < $weekday)) {
			throw new DateException('曜日が正しくありません。');
		}

		$date = clone $this;
		while ($date->getWeekday() != $weekday) {
			$date['day'] = '+1';
		}
		return $date;
	}

	/**
	 * うるう年か？
	 *
	 * @access public
	 * @return bool うるう年ならtrue
	 */
	public function isLeapYear () {
		return ($this->format('L') == 1);
	}

	/**
	 * 休日ならば、その名前を返す
	 *
	 * @access public
	 * @param string $country 国名
	 * @return string 休日の名前
	 */
	public function getHolidayName ($country = 'ja') {
		$config = ConfigManager::getInstance()->compile('date');
		if (!isset($config['holiday'][$country])) {
			$message = new StringFormat('国名 "%s"の休日が未定義です。');
			$message[] = $country;
			throw new ConfigException($message);
		}
		$class = $this->loader->getClass($config['holiday'][$country]['class']);
		$holidays = new $class;
		$holidays->setDate($this);
		return $holidays[$this['day']];
	}

	/**
	 * 休日か？
	 *
	 * @access public
	 * @param string $country 国名
	 * @return bool 日曜日か祭日ならTrue
	 */
	public function isHoliday ($country = 'ja') {
		return (($this->getWeekday() == self::SUN) || !!$this->getHolidayName($country));
	}

	/**
	 * 曜日を返す
	 *
	 * @access public
	 * @return int 曜日
	 */
	public function getWeekday () {
		if (StringUtils::isBlank($this['weekday'])) {
			$this->params['weekday'] = (int)date('N', $this->getTimestamp());
		}
		return $this['weekday'];
	}

	/**
	 * 曜日文字列を返す
	 *
	 * @access public
	 * @return string 曜日
	 */
	public function getWeekdayName () {
		if (StringUtils::isBlank($this['weekday_name'])) {
			$weekdays = Tuple::create([null, '月', '火', '水', '木', '金', '土', '日']);
			$this->params['weekday_name'] = $weekdays[$this->getWeekday()];
		}
		return $this['weekday_name'];
	}

	/**
	 * 元号を返す
	 *
	 * @access public
	 * @return string 元号
	 */
	public function getGengo () {
		if (StringUtils::isBlank($this['gengo'])) {
			foreach (self::getGengos() as $gengo) {
				if (!$this->isPast($gengo['start_date'])) {
					$this->params['gengo'] = $gengo['name'];
					break;
				}
			}
		}
		return $this['gengo'];
	}

	/**
	 * 和暦年を返す
	 *
	 * @access public
	 * @return int 和暦年
	 */
	public function getJapaneseYear () {
		if (StringUtils::isBlank($this['japanese_year'])) {
			foreach (self::getGengos() as $gengo) {
				if (!$this->isPast($gengo['start_date'])) {
					$year = $this['year'] - $gengo['start_date']['year'] + 1;
					$this->params['japanese_year'] = $year;
					break;
				}
			}
		}
		return $this['japanese_year'];
	}

	/**
	 * 書式化した日付を返す
	 *
	 * strftime関数とdate関数で処理。
	 *
	 * @access public
	 * @param string $format 書式
	 * @return string 書式化された日付文字列
	 */
	public function format ($format = 'Y/m/d H:i:s') {
		if (StringUtils::isContain('%', $format)) {
			$format = strftime($format, $this->getTimestamp());
		}
		if (StringUtils::isContain('ww', $format)) {
			$format = str_replace('ww', $this->getWeekdayName(), $format);
		}
		if (StringUtils::isContain('JY', $format)) {
			$year = $this->getGengo();
			if ($this->getJapaneseYear() == 1) {
				$year .= '元';
			} else {
				$year .= $this->getJapaneseYear();
			}
			$format = str_replace('JY', $year, $format);
		}
		return date($format, $this->getTimestamp());
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return $this->format('Y-m-d H:i:s');
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('日付 "%04d-%02d-%02d"', $this['year'], $this['month'], $this['day']);
	}

	/**
	 * 元号の配列を返す
	 *
	 * @access public
	 * @return Tuple 元号の配列
	 * @static
	 */
	static public function getGengos () {
		if (!self::$gengos) {
			self::$gengos = Tuple::create();
			$config = ConfigManager::getInstance()->compile('date');
			if (!isset($config['gengo'])) {
				throw new ConfigException('元号が設定されていません。');
			}
			foreach ($config['gengo'] as $gengo) {
				$gengo = Tuple::create($gengo);
				$gengo['start_date'] = self::create($gengo['start_date']);
				if (!$gengo['start_date']) {
					continue;
				}
				self::$gengos[$gengo['name']] = $gengo;
			}
		}
		return self::$gengos;
	}
}
