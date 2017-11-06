<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed
 */

namespace Carrot3;

/**
 * フィードユーティリティ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FeedUtils {
	const IGNORE_TITLE_PATTERN = '^(PR|AD):';

	/**
	 * @access private
	 */
	private function __construct() {
	}

	/**
	 * エントリーのタイトルを配列で返す
	 *
	 * @access public
	 * @param FeedDocument $feed 対象フィード
	 * @return Tuple
	 * @static
	 */
	static public function getEntryTitles (FeedDocument $feed) {
		$titles = Tuple::create();
		foreach ($feed->getEntryRootElement() as $entry) {
			if ($entry->getName() != $feed->getEntryElementName()) {
				continue;
			}
			if (mb_ereg(self::IGNORE_TITLE_PATTERN, $title = $entry->getTitle())) {
				continue;
			}
			$titles[] = Tuple::create([
				'title' => $title,
				'date' => $entry->getDate(),
				'link' => $entry->getLink(),
			]);
		}
		return $titles;
	}

	/**
	 * エントリーを生成して返す
	 *
	 * @access public
	 * @param FeedDocument フィード
	 * @return FeedEntry エントリー
	 * @static
	 */
	static public function createEntry (FeedDocument $feed) {
		$class = Loader::getInstance()->getClass(
			str_replace('Document', 'Entry', Utils::getClass($feed))
		);
		$entry = $feed->getEntryRootElement()->addElement(new $class);
		$entry->setDocument($feed);
		return $entry;
	}
}
