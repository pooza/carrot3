<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed.rss20
 */

namespace Carrot3;

/**
 * RSS2.0文書
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RSS20Document extends RSS09Document {
	protected $version = '2.0';

	/**
	 * 妥当な文書か？
	 *
	 * @access public
	 * @return bool 妥当な文書ならTrue
	 */
	public function validate ():bool {
		return (XMLDocument::validate()
			&& $this->query('/rss/channel/title')
			&& $this->query('/rss/channel/description')
			&& $this->query('/rss/channel/link')
		);
	}
}
