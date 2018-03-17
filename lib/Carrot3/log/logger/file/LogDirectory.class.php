<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage log.logger.file
 */

namespace Carrot3;

/**
 * ログディレクトリ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LogDirectory extends Directory {

	/**
	 * @access public
	 * @param string $path ディレクトリのパス
	 */
	public function __construct ($path = null) {
		if (!$path) {
			$path = FileUtils::getPath('log');
		}
		parent::__construct($path);
		$this->setDefaultSuffix('.log');
	}

	/**
	 * サブディレクトリを持つか？
	 *
	 * @access public
	 * @return bool サブディレクトリを持つならTrue
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
		return 'LogFile';
	}

	/**
	 * ソート順を返す
	 *
	 * @access public
	 * @return string (ソート順 self::SORT_ASC | self::SORT_DESC)
	 */
	public function getSortOrder () {
		return self::SORT_DESC;
	}
}
