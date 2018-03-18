<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html.anchor
 */

namespace Carrot3;

/**
 * 画像へのリンク
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class ImageAnchorElement extends AnchorElement {

	/**
	 * リンク対象画像を設定
	 *
	 * @access public
	 * @param ImageContainer $record 対象レコード
	 * @param string $size サイズ名
	 * @param int $pixel ピクセル数
	 * @param int $flags フラグのビット列
	 *   self::WIDTH_FIXED 幅固定
	 * @return URL URL
	 */
	public function setImage (ImageContainer $record, string $size, ?int $pixel = null, int $flags = 0) {
		$images = $this->getUserAgent()->createImageManager($flags);
		$this->setURL($images->createURL($record, $size, $pixel));
	}
}
