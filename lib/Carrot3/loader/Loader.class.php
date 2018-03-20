<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage loader
 */

namespace Carrot3;

/**
 * クラスローダー
 *
 * __autoload関数から呼ばれ、クラス名とクラスファイルのひも付けを行う。
 * 原則的に、PHP標準の関数以外は使用してはならない。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Loader {
	private $classes = [];
	private $namespaces = [];
	static private $instance;

	/**
	 * @access private
	 */
	private function __construct () {
	}

	/**
	 * シングルトンインスタンスを返す
	 *
	 * @access public
	 * @return Loader インスタンス
	 * @static
	 */
	static public function getInstance () {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * @access public
	 */
	public function __clone () {
		throw new \Exception('"' . __CLASS__ . '"はコピーできません。');
	}

	/**
	 * クラス名を全て返す
	 *
	 * @access public
	 * @return array クラス名
	 */
	public function getClasses () {
		if (!$this->classes) {
			if (!file_exists($this->getCachePath('classes'))) {
				$this->cache();
			}
			$this->classes = json_decode(
				file_get_contents($this->getCachePath('classes')),
				true
			);
		}
		return $this->classes;
	}

	/**
	 * 名前空間を全て返す
	 *
	 * @access public
	 * @return array 名前空間名
	 */
	public function getNamespaces () {
		if (!$this->namespaces) {
			if (!file_exists($this->getCachePath('namespaces'))) {
				$this->cache();
			}
			$this->namespaces = json_decode(
				file_get_contents($this->getCachePath('namespaces')),
				true
			);
		}
		return $this->namespaces;
	}

	/**
	 * クラスファイルを読み込む
	 *
	 * @access public
	 * @param string $class クラス名、完全修飾名でなくても可。
	 */
	public function includeClass ($class) {
		if ($path = $this->getClasses()[$this->getClass($class)]) {
			require_once $path;
		}
	}

	/**
	 * クラス名を検索して返す
	 *
	 * @access public
	 * @param string $class クラス名
	 * @return string 存在するクラス名
	 */
	public function getClass ($class) {
		foreach (array_merge([null], $this->getNamespaces()) as $namespace) {
			$name = strtolower(mb_ereg_replace('[_[:cntrl:]]', '', $class));
			if ($namespace != '') {
				$name = strtolower($namespace) . '\\' . $name;
			}
			if (class_exists($name, false) || isset($this->getClasses()[$name])) {
				return $name;
			}
		}
		throw new LoaderException($class . 'がロードできません。');
	}

	/**
	 * オブジェクトを返す
	 *
	 * 引数不要なコンストラクタを持ったクラスの、インスタンスを生成して返す
	 *
	 * @access public
	 * @param string $class クラス名
	 * @return string 存在するクラス名
	 */
	public function createObject ($class) {
		$class = $this->getClass($class);
		return new $class;
	}

	private function cache () {
		$classes = [];
		$namespaces = [];
		foreach (['lib', 'module'] as $key) {
			foreach ($this->getPaths($key) as $path) {
				$iterator = new \DirectoryIterator($path);
				foreach ($iterator as $entry) {
					if (in_array($entry->getFilename(), ['.', '..', '.git'])) {
						continue;
					} else if (!mb_ereg('^[A-Z][[:alnum:]]+$', $entry->getFilename())) {
						continue;
					} else if ($iterator->isDir()) {
						$namespaces[] = $this->createNamespace($key, $entry->getFilename());
						$classes += $this->loadPath($key, $entry->getFilename(), $entry->getPathname());
					}
				}
			}
		}
		foreach (['classes', 'namespaces'] as $key) {
			file_put_contents(
				$this->getCachePath($key),
				json_encode($$key, JSON_PRETTY_PRINT),
				LOCK_EX
			);
			chmod($this->getCachePath($key), 0666);
		}
	}

	private function getPaths ($type) {
		switch ($type) {
			case 'lib':
				return [
					BS_WEBAPP_DIR . '/lib',
					BS_LIB_DIR,
				];
			case 'module':
				return [
					BS_WEBAPP_DIR . '/modules',
				];
		}
	}

	private function createNamespace ($type, $key) {
		switch ($type) {
			case 'lib':
				return strtolower($key);
			case 'module':
				return strtolower(__NAMESPACE__ . '\\' . $key . 'Module');
		}
	}

	private function loadPath ($type, string $namespace, $path) {
		$iterator = new \RecursiveDirectoryIterator($path);
		$entries = [];
		foreach ($iterator as $entry) {
			if (in_array($entry->getFilename(), ['.', '..', '.git'])) {
				continue;
			} else if ($iterator->isDir()) {
				$entries += $this->loadPath($type, $namespace, $entry->getPathname());
			} else if ($key = self::extractClass($entry->getFilename())) {
				$key = $this->createNamespace($type, $namespace) . '\\' . strtolower($key);
				$entries[$key] = $entry->getPathname();
			}
		}
		return $entries;
	}

	private function getCachePath ($key) {
		return BS_VAR_DIR . '/serialized/Loader.' .$key . '.json';
	}

	/**
	 * ファイル名からクラス名を返す
	 *
	 * @access public
	 * @param string $filename ファイル名
	 * @return string クラス名
	 * @static
	 */
	static public function extractClass (string $filename) {
		if ($filename && ($filename[0] == '/')) {
			$filename = basename($filename);
		}
		if (mb_ereg('^(.*?)\\.(class|interface|trait)\\.php$', $filename, $matches)) {
			return $matches[1];
		}
	}
}
