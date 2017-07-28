<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage date
 */

namespace Carrot3;

/**
 * 祝日リスト
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface HolidayList extends \ArrayAccess {

	/**
	 * 対象日付を設定
	 *
	 * 対象日付の年月のみ参照され、日は捨てられる。
	 *
	 * @access public
	 * @param Date $date 対象日付
	 */
	public function setDate (Date $date = null);

	/**
	 * 祝日を返す
	 *
	 * @access public
	 * @return Tuple 祝日配列
	 */
	public function getHolidays ();
}

