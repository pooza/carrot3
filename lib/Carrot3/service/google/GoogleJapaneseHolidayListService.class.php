<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * Google Calendar 祝日取得
 *
 * サンプルコード
 * $holidays = new GoogleJapaneseHolidayListService;
 * $holidays->setDate(Date::create());
 * p($holidays[5]); //当月5日の祝日の名前
 * p($holidays->getHolidays()); //当月のすべての祝日を配列で
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @link http://www.finds.jp/wsdocs/calendar/
 */
class GoogleJapaneseHolidayListService extends CurlHTTP implements HolidayList, Serializable {
	use SerializableObject, KeyGenerator;
	private $date;
	private $holidays;
	const DEFAULT_HOST = 'www.googleapis.com';
	const HOLIDAY_ID = 'japanese__ja@holiday.calendar.google.com';

	/**
	 * @access public
	 * @param Host $host ホスト
	 * @param int $port ポート
	 */
	public function __construct (Host $host = null, int $port = null) {
		if (!$host) {
			$host = new Host(self::DEFAULT_HOST);
			$port = NetworkService::getPort('https');
		}
		parent::__construct($host, $port);
		$this->holidays = Tuple::create();
	}

	/**
	 * 対象日付を返す
	 *
	 * @access public
	 * @return Date 対象日付
	 */
	public function getDate () {
		if (!$this->date) {
			$this->setDate();
		}
		return $this->date;
	}

	/**
	 * 対象日付を設定
	 *
	 * 対象日付の年月のみ参照され、日は捨てられる。
	 *
	 * @access public
	 * @param Date $date 対象日付
	 */
	public function setDate (Date $date = null) {
		if ($date) {
			$this->date = clone $date;
		} else {
			$this->date = Date::create()->clearTime();
		}
		$this->date['day'] = 1;

		if (!$this->isSerialized()) {
			$this->serialize();
		}
		$this->holidays->clear();
		$this->holidays->setParameters($this->getSerialized());
	}

	/**
	 * 祝日を返す
	 *
	 * @access public
	 * @return Tuple 祝日配列
	 */
	public function getHolidays () {
		return $this->holidays;
	}

	/**
	 * パスからリクエストURLを生成して返す
	 *
	 * @access public
	 * @param string $href パス
	 * @return HTTPURL リクエストURL
	 */
	public function createRequestURL ($href) {
		$url = parent::createRequestURL($href);
		$url->setParameter('key', BS_SERVICE_GOOGLE_CALENDAR_API_KEY);
		return $url;
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return bool 要素が存在すればTrue
	 */
	public function offsetExists ($key) {
		return $this->getHolidays()->hasParameter($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return mixed 要素
	 */
	public function offsetGet ($key) {
		return $this->getHolidays()[$key];
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @param mixed 要素
	 */
	public function offsetSet ($key, $value) {
		throw new \BadFunctionCallException(Utils::getClass($this) . 'は更新できません。');
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		throw new \BadFunctionCallException(Utils::getClass($this) . 'は更新できません。');
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():?string {
		return $this->createKey([
			$this->getDate()->format('Ym'),
		]);
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		try {
			$date = $this->getDate();
			$url = $this->createRequestURL(
				'/calendar/v3/calendars/' . self::HOLIDAY_ID . '/events'
			);
			$url->setParameter('timeMin', $date->format('c'));
			$url->setParameter('timeMax', $date->getLastDateOfMonth()->format('c'));
			$url->setParameter('maxResults', 30);
			$url->setParameter('orderBy', 'startTime');
			$url->setParameter('singleEvents', 'true');
			$response = $this->sendGET($url->getFullPath());

			$json = new JSONRenderer;
			$json->setContents($response->getRenderer()->getContents());
			$result = $json->getResult();

			$holidays = Tuple::create();
			if (isset($result['items']) && is_iterable($result['items'])) {
				foreach ($result['items'] as $entry) {
					$date = Date::create($entry['start']['date']);
					$holidays[$date['day']] = $entry['summary'];
				}
			}
			(new SerializeHandler)->setAttribute($this, $holidays);
		} catch (\Throwable $e) {
			// ログのみ
		}
	}

	/**
	 * シリアライズ時の値を返す
	 *
	 * @access public
	 * @return mixed シリアライズ時の値
	 */
	public function getSerialized ():?Tuple {
		$date = Date::create()->setParameter('month', '-1');
		if ($value = (new SerializeHandler)->getAttribute($this, $date)) {
			return Tuple::create($value);
		}
		return null;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Google Calendar 祝日取得 "%s"', $this->getName());
	}
}
