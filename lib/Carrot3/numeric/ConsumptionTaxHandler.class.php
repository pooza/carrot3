<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage numeric
 */

namespace Carrot3;

/**
 * 消費税計算機
 *
 * //当時100円のドクターペッパーが、1989/4/1より103円になったことを確認。
 * $tax = ConsumptionTaxHandler::getInstance();
 * $price = $tax->includeTax(100, Date::create(19890401));
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ConsumptionTaxHandler {
	use Singleton;
	private $rates;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$config = ConfigManager::getInstance()->compile('consumption_tax');
		$this->rates = Tuple::create();
		foreach ($config['rates'] as $row) {
			$date = Date::create($row['start_date']);
			$this->rates[$date->format('Y-m-d')] = Tuple::create([
				'start_date' => $date,
				'rate' => (float)$row['rate'],
			]);
		}
		$this->rates->sort();
	}

	/**
	 * 税率を返す
	 *
	 * @access public
	 * @param Date $date 対象日、指定がない場合は現在
	 * @return float 税率
	 * @static
	 */
	public function getRate (Date $date = null) {
		if (!$date) {
			$date = Date::create();
		}

		$rate = 0;
		foreach ($this->rates as $row) {
			if ($date->isPast($row['start_date'])) {
				break;
			}
			$rate = $row['rate'];
		}
		return $rate;
	}

	/**
	 * 税込金額を返す
	 *
	 * @access public
	 * @param float $price 税別金額
	 * @param Date $date 対象日、指定がない場合は現在
	 * @return integer 四捨五入された数値
	 */
	public function includeTax ($price, Date $date = null) {
		return Numeric::round($price * (1 + $this->getRate($date)));
	}

	/**
	 * 税別金額を返す
	 *
	 * @access public
	 * @param float $price 税込金額
	 * @param Date $date 対象日、指定がない場合は現在
	 * @return integer 四捨五入された数値
	 */
	public function excludeTax ($price, Date $date = null) {
		return Numeric::round($price / (1 + $this->getRate($date)));
	}
}
