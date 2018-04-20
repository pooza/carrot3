<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.storage
 */

namespace Carrot3;

/**
 * memcacheシリアライズストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MemcacheSerializeStorage extends SerializeStorage {
	private $server;

	/**
	 * 初期化
	 *
	 * @access public
	 * @param SerializeHandler $handler
	 * @return bool 利用可能ならTrue
	 */
	public function initialize (SerializeHandler $handler):bool {
		parent::initialize($handler);
		if ($this->server = MemcacheManager::getInstance()->getServer()) {
			 return true;
		}
		return false;
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param Date $date 比較する日付 - この日付より古い属性値は破棄
	 * @return mixed 属性値
	 */
	public function getAttribute (string $name, Date $date = null) {
		if ($entry = $this->getEntry($name)) {
			if (!$date || !$entry['update_date']->isPast($date)) {
				return $entry['contents'];
			}
		}
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 */
	public function setAttribute (string $name, $value) {
		$this->server->set($name, $this->getSerializer()->encode([
			'update_date' => Date::create()->format('Y-m-d H:i:s'),
			'contents' => $value,
		]));
	}

	/**
	 * 属性を削除
	 *
	 * @access public
	 * @param string $name 属性の名前
	 */
	public function removeAttribute (string $name) {
		return $this->server->delete($name);
	}

	/**
	 * 属性の更新日を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @return Date 更新日
	 */
	public function getUpdateDate (string $name):?Date {
		if ($entry = $this->getEntry($name)) {
			return $entry['update_date'];
		}
	}

	private function getEntry (string $name) {
		if ($values = $this->server->get($name)) {
			$values = $this->getSerializer()->decode($values);
			$entry = Tuple::create($values);
			$entry['update_date'] = Date::create($entry['update_date']);
			return $entry;
		}
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return 'memcacheシリアライズストレージ';
	}
}
