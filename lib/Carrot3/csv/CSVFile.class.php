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
	protected $renderer;

	/**
	 * @access public
	 * @param string $path パス
	 * @param string $class レンダラークラス名
	 */
	public function __construct ($path, $class = 'CSVData') {
		parent::__construct($path);
		$class = $this->loader->getClass($class);
		$csv = new $class;
		$csv->setContents(file_get_contents($path));
		$csv->setEncoding('utf-8');
		$csv->setRecordSeparator("\n");
		$this->setRenderer($csv);
	}

	/**
	 * バイナリファイルか？
	 *
	 * @access public
	 * @return bool バイナリファイルならTrue
	 */
	public function isBinary () {
		return false;
	}

	/**
	 * レンダラーを返す
	 *
	 * @access public
	 * @return ImageRenderer レンダラー
	 */
	public function getRenderer () {
		return $this->renderer;
	}

	/**
	 * レンダラーを設定
	 *
	 * @access public
	 * @param CSVData $renderer レンダラー
	 */
	public function setRenderer (CSVData $renderer) {
		$this->renderer = $renderer;
	}

	/**
	 * 全て返す
	 *
	 * @access public
	 * @return string 読み込んだ内容
	 */
	public function getContents () {
		return $this->getRenderer()->getContents();
	}

	/**
	 * 保存
	 *
	 * @access public
	 */
	public function save () {
		$this->setContents($this->getRenderer()->getContents());
	}

	/**
	 * 書き換える
	 *
	 * @access public
	 * @param string $contents 書き込む内容
	 */
	public function setContents ($contents) {
		$this->renderer->setContents($contents);
		parent::setContents($contents);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('CSVファイル "%s"', $this->getShortPath());
	}
}
