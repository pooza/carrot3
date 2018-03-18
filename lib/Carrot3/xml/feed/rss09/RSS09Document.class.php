<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed.rss09
 */

namespace Carrot3;

/**
 * RSS0.9x文書
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RSS09Document extends XMLDocument implements FeedDocument {
	protected $version = '0.9';

	/**
	 * @access public
	 * @param string $name 要素の名前
	 */
	public function __construct (string $name = null) {
		parent::__construct('rss');
		$this->setAttribute('version', $this->version);
		$this->setDate(Date::create());
		$this->getChannel()->createElement('generator', $this->controller->getName());
		$author = AuthorRole::getInstance();
		$this->setAuthor($author->getName('ja'), $author->getMailAddress('ja'));
	}

	/**
	 * エントリー要素の名前を返す
	 *
	 * @access public
	 * @return string
	 */
	public function getEntryElementName () {
		return 'item';
	}

	/**
	 * エントリー要素要素の格納先を返す
	 *
	 * @access public
	 * @return XMLElement
	 */
	public function getEntryRootElement () {
		return $this->getChannel();
	}

	/**
	 * 妥当な文書か？
	 *
	 * @access public
	 * @return bool 妥当な文書ならTrue
	 */
	public function validate () {
		return (parent::validate()
			&& $this->query('/rss/channel/title')
			&& $this->query('/rss/channel/description')
			&& $this->query('/rss/channel/link')
			&& $this->query('/rss/channel/language')
		);
	}

	/**
	 * チャンネル要素を返す
	 *
	 * @access public
	 * @return XMLElement チャンネル要素
	 */
	public function getChannel () {
		if (!$element = $this->getElement('channel')) {
			$element = $this->createElement('channel');
		}
		return $element;
	}

	/**
	 * タイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle () {
		if ($element = $this->getChannel()->getElement('title')) {
			return $element->getBody();
		}
	}

	/**
	 * タイトルを設定
	 *
	 * @access public
	 * @param string $title タイトル
	 */
	public function setTitle ($title) {
		if (!$element = $this->getChannel()->getElement('title')) {
			$element = $this->getChannel()->createElement('title');
		}
		$element->setBody($title);
	}

	/**
	 * ディスクリプションを設定
	 *
	 * @access public
	 * @param string $description ディスクリプション
	 */
	public function setDescription ($description) {
		if (!$element = $this->getChannel()->getElement('description')) {
			$element = $this->getChannel()->createElement('description');
		}
		$element->setBody($description);
	}

	/**
	 * リンクを返す
	 *
	 * @access public
	 * @return HTTPURL リンク
	 */
	public function getLink () {
		if ($element = $this->getChannel()->getElement('link')) {
			return URL::create($element->getBody());
		}
	}

	/**
	 * リンクを設定
	 *
	 * @access public
	 * @param HTTPRedirector $link リンク
	 */
	public function setLink (HTTPRedirector $link) {
		if (!$element = $this->getChannel()->getElement('link')) {
			$element = $this->getChannel()->createElement('link');
		}
		$element->setBody($link->getURL()->getContents());
	}

	/**
	 * オーサーを設定
	 *
	 * @access public
	 * @param string $name 名前
	 * @param MailAddress $email メールアドレス
	 */
	public function setAuthor (string $name, MailAddress $email = null) {
		if (!$element = $this->getChannel()->getElement('managingEditor')) {
			$element = $this->getChannel()->createElement('managingEditor');
		}
		if ($email) {
			$element->setBody(sprintf('%s (%s)', $email->getContents(), $name));
		} else {
			$element->setBody($name);
		}
	}

	/**
	 * 日付を返す
	 *
	 * @access public
	 * @return Date 日付
	 */
	public function getDate () {
		if ($element = $this->getChannel()->getElement('lastBuildDate')) {
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
		if (!$element = $this->getChannel()->getElement('lastBuildDate')) {
			$element = $this->getChannel()->createElement('lastBuildDate');
		}
		$element->setBody($date->format(\DateTime::RSS));
	}

	/**
	 * エントリーを生成して返す
	 *
	 * @access public
	 * @return FeedEntry エントリー
	 */
	public function createEntry () {
		return FeedUtils::createEntry($this);
	}

	/**
	 * エントリーのタイトルを配列で返す
	 *
	 * @access public
	 * @return Tuple
	 */
	public function getEntryTitles () {
		return FeedUtils::getEntryTitles($this);
	}
}
