<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * ディレクトリイテレータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DirectoryIterator extends Iterator {
	private $directory;

	/**
	 * @access public
	 * @param Directory $directory ディレクトリ
	 */
	public function __construct (Directory $directory) {
		$this->directory = $directory;
		parent::__construct($directory->getEntryNames());
	}

	/**
	 * 現在のエントリーを返す
	 *
	 * @access public
	 * @return mixed ファイル又はディレクトリ
	 */
	public function current () {
		return $this->directory->getEntry(parent::current());
	}
}

