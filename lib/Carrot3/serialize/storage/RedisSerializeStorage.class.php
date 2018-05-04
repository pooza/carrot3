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
		$this->server = new Redis;
		$this->server->select(BS_REDIS_DATABASES_SERIALIZE);
		$this->server->setSerializer($handler->getSerializer());
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
		if ($entry = $this->server[$name]) {
			if (!$date || !$this->getUpdateDate($name)->isPast($date)) {
				if (is_array($entry['contents'])) {
					return Tuple::create($entry['contents']);
				} else {
					return $entry['contents'];
				}
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
		if ($ttl = (int)$this->handler->getConfig('template_cache_ttl')) {
			$this->server->setEx($name, $ttl, [
				'update_date' => Date::create()->format('Y-m-d H:i:s'),
				'contents' => $value,
			]);
		} else {
			$this->server[$name] = [
				'update_date' => Date::create()->format('Y-m-d H:i:s'),
				'contents' => $value,
			];
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
		if ($entry = $this->server[$name]) {
			return Date::create($entry['update_date']);
		}
	}

	/**
	 * クリア
	 *
	 * @access public
	 */
	public function clear () {
		$this->server->clear();
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return 'Redisシリアライズストレージ';
	}
}
