<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media
 */

namespace Carrot3;

/**
 * 動画ファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MovieFile extends MediaFile implements ImageContainer {

	/**
	 * ファイルを解析
	 *
	 * @access protected
	 */
	protected function analyze () {
		parent::analyze();
		if (mb_ereg('frame rate: [^\\-]+ -> ([.[:digit:]]+)', $this->output, $matches)) {
			$this->attributes['frame_rate'] = (float)$matches[1];
		}
		if (mb_ereg(' ([[:digit:]]{2,4})x([[:digit:]]{2,4})', $this->output, $matches)) {
			$info = $this->getImageInfo('image');
			$this->attributes['width'] = (int)$info['width'];
			$this->attributes['height'] = (int)$info['height'];
			$this->attributes['pixel_size'] = $this['width'] . '×' . $this['height'];
			$this->attributes['aspect'] = $this['width'] / $this['height'];
		}
	}

	/**
	 * 削除
	 *
	 * @access public
	 */
	public function delete () {
		$this->removeImageCache('image');
		parent::delete();
	}

	/**
	 * 動画トラックを持つか？
	 *
	 * @access public
	 * @return bool 動画トラックを持つならTrue
	 */
	public function hasMovieTrack () {
		if (!$this->attributes->count()) {
			$this->analyze();
		}
		return ($this['width'] && $this['height']);
	}

	/**
	 * MPEG4変換して返す
	 *
	 * @access public
	 * @param MediaConvertor $convertor コンバータ
	 * @return MovieFile 変換後ファイル
	 */
	public function convert (MediaConvertor $convertor = null) {
		if (!$convertor) {
			$convertor = new MPEG4MediaConvertor;
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
		switch ($params['mode']) {
			case 'lity':
				return $this->createLityElement($params);
			default:
				return $this->createVideoElement($params);
		}
	}

	/**
	 * Lityへのリンク要素を返す
	 *
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 * @return DivisionElement 要素
	 */
	public function createLityElement (ParameterHolder $params) {
		$params = Tuple::create($params);
		if (!$params['width_movie']) {
			$params['width_movie'] = $params['width'];
		}
		if (!$params['height_movie']) {
			$params['height_movie'] = $params['height'];
		}

		$container = new DivisionElement;
		$anchor = $container->addElement(new LityAnchorElement);
		$id = Crypt::digest([Utils::getClass($this), $this->getID(), Numeric::getRandom()]);
		$anchor->setURL('#' . $id);
		if ($info = $params['thumbnail']) {
			$image = $anchor->addElement(new ImageElement);
			$image->setAttributes(Tuple::create($info));
		} else {
			$anchor->setBody($params['label']);
		}

		$paramsInner = clone $params;
		$paramsInner['mode'] = null;
		$inner = $container->addElement($this->createElement($paramsInner));
		$inner->setID($id);
		$inner->registerStyleClass('lity-hide');
		return $container;
	}

	/**
	 * video要素を返す
	 *
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 * @return VideoElement 要素
	 */
	public function createVideoElement (ParameterHolder $params) {
		$this->resizeByWidth($params, $useragent);
		$element = new VideoElement;
		$element->registerSource($this->createURL($params));
		$element->setAttribute('width', $params['width']);
		$element->setAttribute('height', $params['height']);
		return $element->wrap(new DivisionElement);
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
		return ($this->getMainType() == 'video');
	}

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 * @param string $size
	 */
	public function removeImageCache ($size) {
		if ($file = $this->getImageFile('image')) {
			$file->removeImageCache($size);
		}
	}

	/**
	 * 画像の情報を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @param int $pixel ピクセル数
	 * @param int $flags フラグのビット列
	 * @return Tuple 画像の情報
	 */
	public function getImageInfo ($size, int $pixel = 0, int $flags = 0) {
		if ($file = $this->getImageFile('image')) {
			$info = (new ImageManager)->getInfo($file, $size, $pixel, $flags);
			$info['alt'] = $this->getLabel();
			return $info;
		}
	}

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile ($size) {
		$dir = FileUtils::getDirectory('movie_file');
		if ($file = $dir->getEntry($this->getImageFileBaseName($size), 'ImageFile')) {
			return $file;
		}

		$file = new ImageFile($this->convert(new PNGMediaConvertor)->getPath());
		$file->setName($this->getImageFileBaseName($size));
		$file->moveTo($dir);
		return $file;
	}

	/**
	 * 画像ファイルベース名を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return string 画像ファイルベース名
	 */
	public function getImageFileBaseName ($size) {
		return $this->getID();
	}

	/**
	 * コンテナのラベルを返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string ラベル
	 */
	public function getLabel ($language = 'ja') {
		return $this->getBaseName();
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('動画ファイル "%s"', $this->getShortPath());
	}
}
