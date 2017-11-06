<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage controller
 */

namespace Carrot3;

/**
 * Webコントローラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class WebController extends Controller {

	/**
	 * 検索対象ディレクトリを返す
	 *
	 * @access public
	 * @return Tuple ディレクトリの配列
	 */
	public function getSearchDirectories () {
		if (!$this->searchDirectories) {
			$this->searchDirectories = Tuple::create();
			foreach (['images', 'carrotlib', 'www', 'root'] as $name) {
				$this->searchDirectories[] = FileUtils::getDirectory($name);
			}
		}
		return $this->searchDirectories;
	}
}
