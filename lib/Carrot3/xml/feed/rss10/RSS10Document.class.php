<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed.rss10
 */

namespace Carrot3;

/**
 * RSS1.0文書
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RSS10Document extends RSS09Document {
	protected $version = '1.0';

	/**
	 * @access public
	 * @param string $name 要素の名前
	 */
	public function __construct ($name = null) {
		parent::__construct('rdf:RDF');
		$this->setNamespace('http://purl.org/rss/' . $this->version . '/');
		$this->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
		$this->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
		$this->setDate(Date::create());
		$this->setAuthor(AuthorRole::getInstance()->getName('ja'));
	}

	/**
	 * エントリー要素要素の格納先を返す
	 *
	 * @access public
	 * @return XMLElement
	 */
	public function getEntryRootElement () {
		return $this;
	}

	/**
	 * 妥当な文書か？
	 *
	 * @access public
	 * @return boolean 妥当な文書ならTrue
	 */
	public function validate () {
		return (XMLDocument::validate()
			&& $this->query('/rss/channel/title')
			&& $this->query('/rss/channel/description')
			&& $this->query('/rss/channel/link')
			&& $this->query('/rss/channel/items')
		);
	}

	/**
	 * items要素を返す
	 *
	 * @access public
	 * @return XMLElement items要素
	 */
	public function getItems () {
		if (!$element = $this->getChannel()->getElement('items')) {
			$element = $this->getChannel()->createElement('items');
			$element->createElement('rdf:Seq');
		}
		return $element;
	}

	/**
	 * チャンネルのURLを設定
	 *
	 * @access public
	 * @param HTTPRedirector $url URL
	 */
	public function setChannelURL (HTTPRedirector $url) {
		$this->getChannel()->setAttribute('rdf:about', $url->getContents());
	}

	/**
	 * オーサーを設定
	 *
	 * @access public
	 * @param string $name 名前
	 * @param MailAddress $email メールアドレス
	 */
	public function setAuthor ($name, MailAddress $email = null) {
		if (!$element = $this->getChannel()->getElement('dc:creator')) {
			$element = $this->getChannel()->createElement('dc:creator');
		}
		$element->setBody($name);
	}

	/**
	 * 日付を返す
	 *
	 * @access public
	 * @return Date 日付
	 */
	public function getDate () {
		if ($element = $this->getChannel()->getElement('dc:date')) {
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
		if (!$element = $this->getChannel()->getElement('dc:date')) {
			$element = $this->getChannel()->createElement('dc:date');
		}
		$element->setBody($date->format(\DateTime::W3C));
	}

	/**
	 * エントリーを生成して返す
	 *
	 * @access public
	 * @return FeedEntry エントリー
	 */
	public function createEntry () {
		$entry = FeedUtils::createEntry($this);
		if ($creator = $this->getChannel()->getElement('dc:creator')) {
			$entry->addElement($creator);
		}
		return $entry;
	}
}
