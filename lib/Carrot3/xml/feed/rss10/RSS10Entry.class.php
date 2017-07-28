<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed.rss10
 */

namespace Carrot3;

/**
 * RSS1.0エントリー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RSS10Entry extends RSS09Entry {

	/**
	 * リンクを設定
	 *
	 * @access public
	 * @param HTTPRedirector $link リンク
	 */
	public function setLink (HTTPRedirector $link) {
		parent::setLink($link);
		if ($seq = $this->document->getItems()->getElement('rdf:Seq')) {
			$li = $seq->createElement('rdf:li');
			$li->setAttribute('rdf:resource', $link->getURL()->getContents());
		}
	}

	/**
	 * 日付を返す
	 *
	 * @access public
	 * @return Date 日付
	 */
	public function getDate () {
		if ($element = $this->getElement('dc:date')) {
			return Date::create($element->getBody());
		}
	}

	/**
	 * 日付を設定
	 *
	 * @access public
	 * @param Date $date 日付
	 */
	public function setDate (Date $date) {
		if (!$element = $this->getElement('dc:date')) {
			$element = $this->createElement('dc:date');
		}
		$element->setBody($date->format(\DateTime::W3C));
	}
}

