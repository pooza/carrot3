<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media
 */

namespace Carrot3;

/**
 * 楽曲ファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MusicFile extends MediaFile {

	/**
	 * MP3に変換して返す
	 *
	 * @access public
	 * @param MediaConvertor $convertor コンバータ
	 * @return MovieFile 変換後ファイル
	 */
	public function convert (MediaConvertor $convertor = null) {
		if (!$convertor) {
			$convertor = new MP3MediaConvertor;
		}
		return $convertor->execute($this);
	}

	/**
	 * 表示用のHTML要素を返す
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 * @param UserAgent $useragent 対象ブラウザ
	 * @return DivisionElement 要素
	 */
	public function createElement (iterable $params, UserAgent $useragent = null) {
		return $this->createAudioElement($params);
	}

	/**
	 * audio要素を返す
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 * @return AudioElement 要素
	 */
	public function createAudioElement (iterable $params) {
		$element = new AudioElement;
		$element->registerSource($this->createURL($params));
		return $element;
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate () {
		if (!parent::validate()) {
			return false;
		}
		return ($this->getMainType() == 'audio');
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('楽曲ファイル "%s"', $this->getShortPath());
	}
}
