<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * Google sitemap.xml サイトマップ文書
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @link http://www.google.com/support/webmasters/bin/answer.py?hl=ja&answer=183668
 */
class SiteMapXMLDocument extends XMLDocument {

	/**
	 * @access public
	 * @param string $name 要素の名前
	 */
	public function __construct ($name = null) {
		parent::__construct('urlset');
		$this->setDirty(true);
		$this->setNamespace('http://www.sitemaps.org/schemas/sitemap/0.9');
	}

	/**
	 * 登録を加える
	 *
	 * @access public
	 * @param HTTPRedirector $url 対象URL
	 * @param ParameterHolder $params パラメータ配列
	 * @return XMLElement 追加されたurl要素
	 */
	public function register (HTTPRedirector $url, ParameterHolder $params = null) {
		$element = $this->addElement(new XMLElement('url'));
		$element->createElement('loc', $url->getURL()->getContents());
		if ($params) {
			foreach ($params as $key => $value) {
				if (!StringUtils::isBlank($value)) {
					if ($key == 'lastmod') {
						$date = Date::create($value);
						$value = $date->format(\DateTime::W3C);
					}
					$element->createElement($key, $value);
				}
			}
		}
		return $element;
	}
}

