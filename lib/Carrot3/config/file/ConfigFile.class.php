<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.file
 */

namespace Carrot3;

/**
 * 設定ファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ConfigFile extends File {
	use KeyGenerator;
	private $config = [];
	private $parser;
	private $cache;

	/**
	 * バイナリファイルか？
	 *
	 * @access public
	 * @return bool バイナリファイルならTrue
	 */
	public function isBinary ():bool {
		return false;
	}

	/**
	 * 設定パーサーを返す
	 *
	 * @access public
	 * @return ConfigParser 設定パーサー
	 */
	public function getParser () {
		if (!$this->parser) {
			$this->parser = $this->loader->createObject(
				ltrim($this->getSuffix(), '.') . 'ConfigParser'
			);
			$this->parser->setContents($this->getContents());
		}
		return $this->parser;
	}

	/**
	 * コンパイラを返す
	 *
	 * @access public
	 * @return ConfigCompiler コンパイラ
	 */
	public function getCompiler () {
		return ConfigManager::getInstance()->getCompiler($this);
	}

	/**
	 * 設定内容を返す
	 *
	 * @access public
	 * @return array 設定ファイルの内容
	 */
	public function getResult () {
		if (!$this->config) {
			$this->config = $this->getParser()->getResult();
		}
		return $this->config;
	}

	/**
	 * コンパイル
	 *
	 * @access public
	 * @return mixed 設定キャッシュファイル
	 */
	public function compile () {
		if (defined('BS_REDIS_HOST') && defined('BS_REDIS_PORT') && extension_loaded('redis')) {
			$redis = new \Redis;
			$redis->connect(BS_REDIS_HOST, BS_REDIS_PORT);
			$redis->select(BS_REDIS_DATABASES_SERIALIZE);
			$key = $this->createKey([$this->getID()]);
			if ($script = $redis->get($key)) {
				$script = (new PHPSerializer)->decode($script);
			} else {
				$script = $this->getCompiler()->execute($this);
				$script = str_replace('<?php', '', $script);
				$redis->set($key, (new PHPSerializer)->encode($script));
			}
			return eval($script);
		} else {
			$cache = $this->getCacheFile();
			if (!$cache->isExists() || $cache->getUpdateDate()->isPast($this->getUpdateDate())) {
				$cache->setContents($this->getCompiler()->execute($this));
			}
			return require_once $cache->getPath();
		}
	}

	/**
	 * キャッシュファイルを返す
	 *
	 * @access public
	 * @return File キャッシュファイル
	 */
	public function getCacheFile () {
		if (!$this->cache) {
			$path = str_replace(BS_ROOT_DIR, '', $this->getPath());
			$path = sprintf('%s/config_cache/%s.php', BS_VAR_DIR, str_replace('/', '%', $path));
			if (!file_exists($dir = dirname($path))) {
				mkdir($dir);
			}
			$this->cache = new File($path);
		}
		return $this->cache;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('設定ファイル "%s"', $this->getShortPath());
	}
}
