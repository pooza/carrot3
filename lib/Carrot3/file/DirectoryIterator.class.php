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
	 * @param integer $flags フラグのビット列
	 *   self::WITHOUT_DOTTED ドットファイルを除く
	 */
	public function __construct (Directory $directory, $flags = 0) {
		$this->directory = $directory;
		parent::__construct($directory->getEntryNames($flags));
	}

	/**
	 * 現在のエントリーを返す
	 *
	 * @access public
	 * @return mixed ファイル又はディレクトリ
	 */
	public function current () {
		if ($name = parent::current()) {
			return $this->directory->getEntry($name);
		}
	}
}
