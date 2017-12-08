<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config
 */

namespace Carrot3;

/**
 * 設定マネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ConfigManager {
	use Singleton;
	private $compilers;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$file = self::getConfigFile('config_compilers', 'RootConfigFile');
		$this->compilers = Tuple::create($this->compile($file));
		$this->compilers[] = new DefaultConfigCompiler(['pattern' => '.']);
	}

	/**
	 * 設定ファイルをコンパイル
	 *
	 * @access public
	 * @param mixed $file File又はファイル名
	 * @return mixed 設定ファイルからの戻り値
	 */
	public function compile ($file) {
		if (!($file instanceof File)) {
			if (!$file = self::getConfigFile($file)) {
				return;
			}
		}
		if (!$file->isReadable()) {
			throw new ConfigException($file . 'が読めません。');
		}
		return $file->compile();
	}

	/**
	 * 設定ファイルに適切なコンパイラを返す
	 *
	 * @access public
	 * @param ConfigFile $file 設定ファイル
	 * @return ConfigCompiler 設定コンパイラ
	 */
	public function getCompiler (ConfigFile $file) {
		foreach ($this->compilers as $compiler) {
			if (mb_ereg($compiler['pattern'], $file->getPath())) {
				return $compiler;
			}
		}
		throw new ConfigException($file . 'の設定コンパイラがありません。');
	}

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 */
	public function clear () {
		FileUtils::getDirectory('config_cache')->clear();
	}

	/**
	 * 設定ファイルを返す
	 *
	 * @access public
	 * @param string $name 設定ファイル名、但し拡張子は含まない
	 * @param string $class 設定ファイルのクラス名
	 * @return ConfigFile 設定ファイル
	 */
	static public function getConfigFile ($name, $class = 'ConfigFile') {
		if (!Utils::isPathAbsolute($name)) {
			$name = BS_WEBAPP_DIR . '/config/' . $name;
		}
		$class = Loader::getInstance()->getClass($class);
		$file = new $class($name . '.yaml');
		if ($file->isExists()) {
			if (!$file->isReadable()) {
				throw new ConfigException($file . 'が読めません。');
			}
			return $file;
		}
	}
}
