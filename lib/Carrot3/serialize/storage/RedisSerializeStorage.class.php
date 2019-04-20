<?php
namespace Carrot3;

class RedisSerializeStorage extends SerializeStorage {
	private $server;

	public function initialize (SerializeHandler $handler):bool {
		parent::initialize($handler);
		if (!extension_loaded('redis')) {
			return false;
		}
		$this->server = new Redis;
		$this->server->select((int)BS_REDIS_DATABASES_SERIALIZE);
		$this->server->setSerializer($handler->getSerializer());
		return true;
	}

	public function select (int $id) {
		$this->server->select($id);
	}

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

	public function removeAttribute (string $name) {
		return $this->server[$name] = null;
	}

	public function getUpdateDate (string $name):?Date {
		if ($entry = $this->server[$name]) {
			return Date::create($entry['update_date']);
		}
	}

	public function clear () {
		$this->server->clear();
	}

	public function __toString () {
		return 'Redisシリアライズストレージ';
	}
}
