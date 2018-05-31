<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image.renderer
 */

namespace Carrot3;

/**
 * 画像レンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface ImageRenderer extends Renderer {

	/**
	 * GD画像リソースを返す
	 *
	 * @access public
	 * @return resource GD画像リソース
	 */
	public function getGDHandle ();

	/**
	 * 幅を返す
	 *
	 * @access public
	 * @return int 幅
	 */
	public function getWidth ():int;

	/**
	 * 高さを返す
	 *
	 * @access public
	 * @return int 高さ
	 */
	public function getHeight ():int;
}
