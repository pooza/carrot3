<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage memcache
 */

namespace Carrot3;

/**
 * memcacheマネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MemcacheManager {
	use Singleton, BasicObject;
	private $constants;
	private $serverNames;
	const CONNECT_INET = 'inet';
	const CONNECT_UNIX = 'unix';

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->constants = new ConstantHandler('MEMCACHE');
	}

	/**
	 * 有効か？
	 *
	 * @access public
	 * @return boolean 有効ならTrue
	 */
	public function isEnabled () {
		return !!extension_loaded('memcached');
	}

	/**
	 * 設定値を返す
	 *
	 * @access public
	 * @param string $name 設定名
	 * @return string 設定値
	 */
	public function getConstant ($name) {
		return $this->constants[$name];
	}

	/**
	 * サーバ名を全て返す
	 *
	 * @access public
	 * @return Tuple サーバ名の配列
	 */
	public function getServerNames () {
		if (!$this->serverNames) {
			$this->serverNames = Tuple::create();
			$pattern = '^' . ConstantHandler::PREFIX . '_MEMCACHE_([A-Z]+)_';
			foreach ($this->constants->getParameters() as $key => $value) {
				if (mb_ereg($pattern, $key, $matches)) {
					$this->serverNames[] = StringUtils::toLower($matches[1]);
				}
			}
			$this->serverNames->uniquize();
		}
		return $this->serverNames;
	}

	/**
	 * サーバを返す
	 *
	 * @access public
	 * @param string $name サーバ名
	 * @param string $class クラス
	 * @return Memcache サーバ
	 */
	public function getServer ($name = 'default', $class = null) {
		if ($this->isEnabled()) {
			if ($class) {
				$server = $this->loader->createObject($class . 'Memcache');
			} else {
				$server = new Memcache;
			}
			$host = $this->getConstant($name . '_host');
			$port = $this->getConstant($name . '_port');
			if ($server->pconnect($host, $port)) {
				return $server;
			}
		}
	}
}
