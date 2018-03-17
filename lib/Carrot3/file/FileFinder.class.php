<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * ファイル検索
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FileFinder {
	use BasicObject;
	private $directories;
	private $suffixes;
	private $pattern;
	private $class;

	/**
	 * @access public
	 * @param string $class 出力クラス
	 */
	public function __construct ($class = 'File') {
		$this->directories = Tuple::create();
		$this->suffixes = Tuple::create([null]);
		foreach ($this->controller->getSearchDirectories() as $dir) {
			$this->registerDirectory($dir);
		}
		$this->class = $this->loader->getClass($class);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $file ファイル名、File等
	 * @return File 最初にマッチしたファイル
	 */
	public function execute ($file) {
		if ($file instanceof File) {
			return $this->execute($file->getPath());
		} else if (is_iterable($file)) {
			$params = Tuple::create($file);
			if (StringUtils::isBlank($params['src'])) {
				if ($record = (new RecordFinder($params))->execute()) {
					if ($attachment = $record->getAttachment($params['size'])) {
						return $this->execute($attachment);
					}
				}
			} else {
				return $this->execute($file['src']);
			}
		}

		if (Utils::isPathAbsolute($file)) {
			return new $this->class($file);
		}
		foreach ($this->directories as $dir) {
			foreach ($this->suffixes as $suffix) {
				if ($found = $dir->getEntry($file . $suffix, $this->class)) {
					return $found;
				}
			}
		}
	}

	/**
	 * 検索対象ディレクトリを登録
	 *
	 * @access public
	 * @param mixed $dir 検索対象ディレクトリ
	 */
	public function registerDirectory ($dir) {
		if (is_string($dir)) {
			$dir = FileUtils::getDirectory($dir);
		}
		if ($dir instanceof Directory) {
			$this->directories->unshift($dir);
		}
	}

	/**
	 * 検索対象ディレクトリをクリア
	 *
	 * @access public
	 */
	public function clearDirectories () {
		$this->directories->clear();
	}

	/**
	 * 検索対象拡張子を登録
	 *
	 * @access public
	 * @param string $suffix 拡張子
	 */
	public function registerSuffix ($suffix) {
		$this->suffixes->unshift('.' . ltrim($suffix, '.'));
	}

	/**
	 * 検索対象拡張子を登録
	 *
	 * @access public
	 * @param ParameterHolder $suffixes 拡張子の配列
	 */
	public function registerSuffixes (ParameterHolder $suffixes) {
		foreach ($suffixes as $suffix) {
			$this->registerSuffix($suffix);
		}
		$this->suffixes->uniquize();
	}

	/**
	 * 検索対象拡張子をクリア
	 *
	 * @access public
	 */
	public function clearSuffixes () {
		$this->suffixes->clear();
		$this->suffixes[] = null;
	}
}
