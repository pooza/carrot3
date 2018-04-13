<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * Content-Typeヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ContentTypeMIMEHeader extends MIMEHeader {
	protected $name = 'Content-Type';

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if ($contents instanceof Renderer) {
			$contents = self::getContentType($contents);
		} else {
			$contents = $contents;
		}
		parent::setContents($contents);
	}

	/**
	 * ヘッダの内容からパラメータを抜き出す
	 *
	 * @access protected
	 */
	protected function parse () {
		parent::parse();
		if ($this['boundary'] && $this->getPart()) {
			$this->getPart()->setBoundary($this['boundary']);
		}
		$type = StringUtils::explode('/', $this[0]);
		$this['main_type'] = $type[0];
		$this['sub_type'] = $type[1];
		$this['type'] = $type[0] . '/' . $type[1];
	}

	/**
	 * HTML文書か？
	 *
	 * @access public
	 * @return bool HTML文書ならTrue
	 */
	public function isHTML ():bool {
		return StringUtils::isContain('html', $this['sub_type']);
	}

	/**
	 * レンダラーの完全なタイプを返す
	 *
	 * @access public
	 * @param Renderer $renderer 対象レンダラー
	 * @return string メディアタイプ
	 * @static
	 */
	static public function getContentType (Renderer $renderer) {
		if ($renderer instanceof TextRenderer) {
			if (StringUtils::isBlank($charset = mb_preferred_mime_name($renderer->getEncoding()))) {
				throw new MIMEException('利用できないエンコード名です。');
			}
			return sprintf('%s; charset=%s', $renderer->getType(), $charset);
		}
		return $renderer->getType();
	}
}
