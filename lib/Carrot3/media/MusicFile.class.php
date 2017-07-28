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
	 * ファイルの内容から、メディアタイプを返す
	 *
	 * fileinfoだけでは認識できないメディアタイプがある。
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function analyzeType () {
		if (($type = parent::analyzeType()) == MIMEType::DEFAULT_TYPE) {
			if (!$this->attributes->count()) {
				$this->analyze();
			}
			foreach (['wma'] as $type) {
				if (StringUtils::isContain('Audio: ' . $type, $this->output)) {
					return MIMEType::getType($type);
				}
			}
		}
		return $type;
	}

	/**
	 * mp3に変換して返す
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
	 * @param ParameterHolder $params パラメータ配列
	 * @param UserAgent $useragent 対象ブラウザ
	 * @return DivisionElement 要素
	 */
	public function createElement (ParameterHolder $params, UserAgent $useragent = null) {
		return $this->createAudioElement($params);
	}

	/**
	 * audio要素を返す
	 *
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 * @return AudioElement 要素
	 */
	public function createAudioElement (ParameterHolder $params) {
		$element = new AudioElement;
		$element->registerSource($this->createURL($params));
		return $element;
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return boolean 出力可能ならTrue
	 */
	public function validate () {
		if (!parent::validate()) {
			return false;
		}
		return (MIMEUtils::getMainType($this->analyzeType()) == 'audio');
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('楽曲ファイル "%s"', $this->getShortPath());
	}

	/**
	 * 探す
	 *
	 * @access public
	 * @param mixed $file パラメータ配列、File、ファイルパス文字列
	 * @param string $class クラス名
	 * @return File ファイル
	 * @static
	 */
	static public function search ($file, $class = 'MusicFile') {
		return parent::search($file, $class);
	}
}

