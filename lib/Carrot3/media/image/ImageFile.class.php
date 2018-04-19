<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image
 */

namespace Carrot3;

/**
 * 画像ファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ImageFile extends MediaFile implements ImageContainer {
	protected $renderer;
	protected $rendererClass;
	protected $rendererParameters;

	/**
	 * @access public
	 * @param string $path パス
	 * @param string $class レンダラーのクラス名
	 */
	public function __construct ($path, $class = null) {
		if (StringUtils::isBlank($class)) {
			$class = ImageManager::getRendererEntries()['default'];
		}
		if (is_iterable($class)) {
			$params = Tuple::create($class);
			$class = $params['class'];
			$this->rendererParameters = $params;
		}
		$this->rendererClass = $this->loader->getClass($class);
		parent::__construct($path);
	}

	/**
	 * @access public
	 * @param string $method メソッド名
	 * @param mixed $values 引数
	 */
	public function __call ($method, $values) {
		return Utils::executeMethod($this->getRenderer(), $method, $values);
	}

	/**
	 * リネーム
	 *
	 * @access public
	 * @param string $name 新しい名前
	 */
	public function rename (string $name) {
		$name .= Image::getSuffixes()[$this->getRenderer()->getType()];
		parent::rename($name);
	}

	/**
	 * ファイルを解析
	 *
	 * @access protected
	 */
	protected function analyze () {
		try {
			File::analyze();
			$this->attributes['type'] = $this->getRenderer()->getType();
			$this->attributes['width'] = (int)$this->getRenderer()->getWidth();
			$this->attributes['height'] = (int)$this->getRenderer()->getHeight();
			$this->attributes['pixel_size'] = $this['width'] . '×' . $this['height'];
		} catch (\Exception $e) {
			$this->attributes['error'] = $e->getMessage();
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
	 * レンダラーを返す
	 *
	 * @access public
	 * @return ImageRenderer レンダラー
	 */
	public function getRenderer () {
		if (!$this->renderer) {
			if (!$this->isExists() || !$this->getSize()) {
				throw new ImageException($this . 'の形式が不明です。');
			}
			$params = Tuple::create($this->rendererParameters);
			$params['file'] = $this;
			$this->renderer = new $this->rendererClass($params);
		}
		return $this->renderer;
	}

	/**
	 * レンダラーを設定
	 *
	 * @access public
	 * @param ImageRenderer $renderer レンダラー
	 */
	public function setRenderer (ImageRenderer $renderer) {
		$this->renderer = $renderer;
		$this->rendererClass = Utils::getClass($renderer);
		$this->attributes = null;
	}

	/**
	 * 保存
	 *
	 * @access public
	 */
	public function save () {
		if ($this->isExists() && !$this->isWritable()) {
			throw new FileException($this . 'に書き込むことができません。');
		}

		$this->removeImageCache('image');
		$this->setContents($this->getRenderer()->getContents());
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
		$params = Tuple::create($params);
		$this->resizeByWidth($params, $useragent);

		$element = new ImageElement;
		$element->setURL($this->createURL($params));
		$element->registerStyleClass($params['style_class']);
		$element->setAttribute('width', $this['width']);
		$element->setAttribute('height', $this['height']);
		$element->setAttributes($params);
		return $element;
	}

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 * @param string $size
	 */
	public function removeImageCache (string $size) {
		(new ImageManager)->removeEntry($this, $size);
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
	public function getImageInfo (string $size, ?int $pixel = null, int $flags = 0) {
		return (new ImageManager)->getInfo($this, $size, $pixel, $flags);
	}

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile (string $size):?ImageFile {
		return $this;
	}

	/**
	 * 画像ファイルを設定
	 *
	 * @access public
	 * @param string $name 画像名
	 * @param ImageFile $file 画像ファイル
	 */
	public function setImageFile (string $name, ImageFile $file) {
		$this->getRenderer()->setImage($file);
		$this->save();
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate ():bool {
		if (!parent::validate()) {
			return false;
		}
		return ($this->getMainType() == 'image');
	}

	/**
	 * ラベルを返す
	 *
	 * @access public
	 * @param string $lang 言語
	 * @return string ラベル
	 */
	public function getLabel (?string $lang = 'ja'):?string {
		try {
			return TranslateManager::getInstance()->execute(
				$this->getBaseName(),
				'user_image',
				$lang
			);
		} catch (TranslateException $e) {
			return $this->getBaseName();
		}
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('画像ファイル "%s"', $this->getShortPath());
	}
}
