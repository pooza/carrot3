<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage string.translate
 */

namespace Carrot3;

/**
 * 辞書ディレクトリ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DictionaryDirectory extends Directory {

	/**
	 * @access public
	 * @param string $path ディレクトリのパス
	 */
	public function __construct ($path = null) {
		if (!$path) {
			$path = FileUtils::getPath('dictionaries');
		}
		parent::__construct($path);
		$this->setDefaultSuffix('.csv');
	}

	/**
	 * サブディレクトリを持つか？
	 *
	 * @access public
	 * @return boolean サブディレクトリを持つならTrue
	 */
	public function hasSubDirectory () {
		return false;
	}

	/**
	 * エントリーのクラス名を返す
	 *
	 * @access public
	 * @return string エントリーのクラス名
	 */
	public function getDefaultEntryClass () {
		return 'DictionaryFile';
	}
}

