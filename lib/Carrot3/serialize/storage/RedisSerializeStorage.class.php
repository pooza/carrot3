<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.storage
 */

namespace Carrot3;

/**
 * Redisシリアライズストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RedisSerializeStorage extends SerializeStorage {
	private $server;

	/**
	 * 初期化
	 *
	 * @access public
	 * @param SerializeHandler $handler
	 * @return bool
	 */
	public function initialize (SerializeHandler $handler):bool {
		parent::initialize($handler);
		if (!extension_loaded('redis')) {
			return false;
		}
		$this->server = new \Redis;
		$this->server->connect(BS_REDIS_HOST, BS_REDIS_PORT);
		$this->server->select(BS_REDIS_DATABASES_SERIALIZE);
		return true;
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
		$serialized = $this->getSerializer()->encode([
			'update_date' => Date::create()->format('Y-m-d H:i:s'),
			'contents' => $value,
		]);
		if ($ttl = (int)$this->handler->getConfig('template_cache_ttl')) {
			$this->server->setEx($name, $ttl, $serialized);
		} else {
			$this->server->set($name, $serialized);
		}
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
		if ($entry = $this->server->get($name)) {
			$entry = Tuple::create($this->getSerializer()->decode($entry));
			$entry['update_date'] = Date::create($entry['update_date']);
			return $entry;
		}
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return 'Redisシリアライズストレージ';
	}
}
