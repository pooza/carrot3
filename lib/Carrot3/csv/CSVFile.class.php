<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage csv
 */

namespace Carrot3;

/**
 * CSVファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class CSVFile extends File {
	private $engine;
	const DEFAULT_ENGINE_CLASS = 'CSVData';

	/**
	 * @access public
	 * @param string $path パス
	 * @param CSVData $engine CSVエンジン
	 */
	public function __construct ($path, CSVData $engine = null) {
		parent::__construct($path);

		if (!$engine) {
			$engine = $this->loader->createObject(self::DEFAULT_ENGINE_CLASS);
		}
		$this->setEngine($engine);
	}

	/**
	 * @access public
	 * @param string $method メソッド名
	 * @param mixed[] $values 引数
	 */
	public function __call ($method, $values) {
		return Utils::executeMethod($this->getEngine(), $method, $values);
	}

	/**
	 * バイナリファイルか？
	 *
	 * @access public
	 * @return boolean バイナリファイルならTrue
	 */
	public function isBinary () {
		return false;
	}

	/**
	 * CSVエンジンを返す
	 *
	 * @access public
	 * @return CSVData CSVエンジン
	 */
	public function getEngine () {
		if (!$this->engine) {
			throw new FileException('CSVエンジンが未設定です。');
		}
		return $this->engine;
	}

	/**
	 * CSVエンジンを設定
	 *
	 * @access public
	 * @param CSVData $engine CSVエンジン
	 */
	public function setEngine (CSVData $engine) {
		$this->engine = $engine;
		if ($this->isExists()) {
			$this->engine->setLines($this->getLines());
		}
	}

	/**
	 * 保存
	 *
	 * @access public
	 */
	public function save () {
		$this->setContents($this->getEngine()->getContents());
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('CSVファイル "%s"', $this->getShortPath());
	}
}
