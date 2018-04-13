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
class RedisSerializeStorage implements SerializeStorage {
	use BasicObject;
	private $server;
	private $serializer;

	/**
	 * @access public
	 * @param Serializer $serializer
	 */
	public function __construct (Serializer $serializer = null) {
		if (!$serializer) {
			$serializer = $this->loader->createObject(BS_SERIALIZE_SERIALIZER . 'Serializer');
		}
		$this->serializer = $serializer;
	}

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize () {
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
	 * @return string シリアライズされた値
	 */
	public function setAttribute (string $name, $value) {
		$values = [
			'update_date' => Date::create()->format('Y-m-d H:i:s'),
			'contents' => $value,
		];
		$serialized = $this->serializer->encode($values);
		$this->server->set($name, $serialized);
		return $serialized;
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
	 * 属性を全て削除
	 *
	 * @access public
	 * @final
	 */
	final public function clearAttributes () {
		return $this->server->flushDb();
	}

	/**
	 * 属性の更新日を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @return Date 更新日
	 */
	public function getUpdateDate (string $name) {
		if ($entry = $this->getEntry($name)) {
			return $entry['update_date'];
		}
	}

	private function getEntry (string $name) {
		if ($entry = $this->server->get($name)) {
			$entry = Tuple::create($this->serializer->decode($entry));
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