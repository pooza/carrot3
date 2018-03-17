<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image
 */

namespace Carrot3;

/**
 * 画像マネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ImageManager {
	use BasicObject;
	protected $useragent;
	protected $type;
	protected $flags = 0;
	protected $backgroundColor;
	protected $directory;
	static protected $renderers;
	const WIDTH_FIXED = 2;
	const HEIGHT_FIXED = 4;
	const WITHOUT_SQUARE = 8;

	/**
	 * @access public
	 * @param mixed $flags フラグのビット列、又は配列
	 */
	public function __construct ($flags = null) {
		$this->directory = FileUtils::getDirectory('image_cache');
		$this->setFlags($flags);
		$this->setUserAgent($this->request->getUserAgent());
	}

	/**
	 * 対象UserAgentを返す
	 *
	 * @access public
	 * @return UserAgent 対象UserAgent
	 */
	public function getUserAgent () {
		return $this->useragent;
	}

	/**
	 * 対象UserAgentを設定
	 *
	 * @access public
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function setUserAgent (UserAgent $useragent) {
		$this->useragent = $useragent;
	}

	/**
	 * 背景色を返す
	 *
	 * @access public
	 * @return Color 背景色
	 */
	public function getBackgroundColor () {
		if (!$this->backgroundColor) {
			$this->backgroundColor = new Color(BS_IMAGE_THUMBNAIL_BGCOLOR);
		}
		return $this->backgroundColor;
	}

	/**
	 * 背景色を設定
	 *
	 * @access public
	 * @param Color $color 背景色
	 */
	public function setBackgroundColor (Color $color) {
		$this->backgroundColor = $color;
	}

	/**
	 * 規定の最大幅を返す
	 *
	 * @access public
	 * @return int 規定の最大幅
	 */
	public function getDefaultWidth () {
		return $this->getUserAgent()->getDisplayInfo()['width'];
	}

	/**
	 * 規定フラグを返す
	 *
	 * @access public
	 * @return int フラグのビット列
	 */
	public function getFlags () {
		return $this->flags;
	}

	/**
	 * 規定のフラグを設定
	 *
	 * @access public
	 * @param mixed $flags フラグのビット列、又は配列
	 */
	public function setFlags ($flags) {
		if (StringUtils::isBlank($flags)) {
			return;
		} else if (is_numeric($flags)) {
			$this->setFlag($flags);
		} else {
			if (is_string($flags)) {
				$flags = StringUtils::explode(',', $flags);
			}
			foreach ($flags as $flag) {
				$this->setFlag($flag);
			}
		}
	}

	protected function setFlag ($flag) {
		if (!is_numeric($flag)) {
			$constants = new ConstantHandler;
			$value = StringUtils::toUpper($flag);
			if (StringUtils::isBlank($flag = $constants['Carrot3\\ImageManager::' . $value])) {
				$message = new StringFormat('Carrot3\\ImageManager::%sが未定義です。');
				$message[] = $value;
				throw new ImageException($message);
			}
		}
		$this->flags |= $flag;
	}

	/**
	 * 画像のタイプを返す
	 *
	 * @access public
	 * @return string タイプ
	 */
	public function getType () {
		return $this->getUserAgent()->getDefaultImageType();
	}

	/**
	 * サムネイルのURLを返す
	 *
	 * @access public
	 * @param ImageContainer $record 対象レコード
	 * @param string $size サイズ名
	 * @param int $pixel ピクセル数
	 * @param int $flags フラグのビット列
	 *   self::WIDTH_FIXED 幅固定
	 *   self::HEIGHT_FIXED 高さ固定
	 *   self::WITHOUT_SQUARE 正方形に整形しない
	 * @return URL URL
	 */
	public function createURL (ImageContainer $record, $size, int $pixel = 0, int $flags = 0) {
		if (!$file = $this->getFile($record, $size, $pixel, $flags)) {
			return null;
		}

		$flags |= $this->flags;
		$url = FileUtils::createURL('image_cache');
		$url['path'] .= $this->createEntryName($record, $size) . '/' . $file->getName();
		return $url;
	}

	/**
	 * サムネイルを削除する
	 *
	 * @access public
	 * @param ImageContainer $record 対象レコード
	 * @param string $size サイズ名
	 */
	public function removeEntry (ImageContainer $record, $size) {
		if ($dir = $this->getEntryDirectory($record, $size)) {
			$dir->delete();
		}
	}

	/**
	 * 画像の情報を返す
	 *
	 * @access public
	 * @param ImageContainer $record 対象レコード
	 * @param string $size サイズ名
	 * @param int $pixel ピクセル数
	 * @param int $flags フラグのビット列
	 *   self::WITHOUT_BWORSER_CACHE クエリー末尾に乱数を加え、ブラウザキャッシュを無効にする
	 *   self::WIDTH_FIXED 幅固定
	 *   self::HEIGHT_FIXED 高さ固定
	 *   self::WITHOUT_SQUARE 正方形に整形しない
	 * @return Tuple 画像の情報
	 */
	public function getInfo (ImageContainer $record, $size, int $pixel = 0, int $flags = 0) {
		$flags |= $this->flags;
		if (!$file = $this->getFile($record, $size, $pixel, $flags)) {
			return;
		}
		try {
			$image = $file->getRenderer();
		} catch (\Exception $e) {
			$file->delete();
			LogManager::getInstance()->put($file . 'を削除しました。' . $e->getMessage());
			return;
		}

		$info = Tuple::create();
		if ($url = $this->createURL($record, $size, $pixel, $flags)) {
			$info['url'] = $url->getContents();
		}
		$info['width'] = $image->getWidth();
		$info['height'] = $image->getHeight();
		$info['alt'] = $record->getLabel();
		$info['type'] = $image->getType();
		$info['pixel_size'] = $info['width'] . '×' . $info['height'];
		return $info;
	}

	protected function getFile (ImageContainer $record, $size, $pixel, int $flags = 0) {
		if (!$source = $record->getImageFile($size)) {
			return null;
		}
		$flags |= $this->flags;
		$dir = $this->getEntryDirectory($record, $size);
		$name = $this->createFileName($record->getImageFile($size), $pixel, $flags);
		if (!$file = $dir->getEntry($name, 'ImageFile')) {
			$dir = $this->getEntryDirectory($record, $size);
			$name = $this->createFileName($record->getImageFile($size), $pixel, $flags);
			if (!$file = $dir->getEntry($name, 'ImageFile')) {
				$file = $dir->createEntry($name, 'ImageFile');
			}
			$file->setRenderer($this->convert($record, $pixel, $source, $flags));
			$file->save();
		}
		return $file;
	}

	protected function createFileName (ImageFile $file, $pixel, int $flags = 0) {
		return Crypt::digest([
			$file->getID(),
			$pixel,
			$this->flags | $flags,
		]);
	}

	protected function convert (ImageContainer $record, $pixel, $contents, int $flags = 0) {
		$params = ImageManager::getRendererEntries()['default'];
		$class = $this->loader->getClass($params['class']);
		$image = new $class($params);
		$image->setBackgroundColor($this->getBackgroundColor());
		$image->setImage($contents);
		$flags |= $this->flags;
		$image->setType($this->getType());

		if ($pixel) {
			if ($flags & self::WITHOUT_SQUARE) {
				if ($image->getAspect() < 1) {
					$image->resizeHeight($pixel);
				} else {
					$image->resizeWidth($pixel);
				}
			} else if ($flags & self::WIDTH_FIXED) {
				$image->resizeWidth($pixel);
			} else if ($flags & self::HEIGHT_FIXED) {
				$image->resizeHeight($pixel);
			} else {
				$image->resizeSquare($pixel);
			}
		} else if ($width = $this->getDefaultWidth()) {
			$image->resizeWidth($width);
		}
		return $image;
	}

	protected function createEntryName (ImageContainer $record, $size) {
		return Crypt::digest([
			Utils::getClass($record),
			$record->getID(),
			$size,
		]);
	}

	protected function getEntryDirectory (ImageContainer $record, $size) {
		$name = $this->createEntryName($record, $size);
		if (!$dir = $this->directory->getEntry($name)) {
			$dir = $this->directory->createDirectory($name);
		}
		$dir->setDefaultSuffix(Image::getSuffixes()[$this->getType()]);
		return $dir;
	}

	/**
	 * 画像情報から、HTMLのimg要素を返す
	 *
	 * @access public
	 * @param Tuple $info getInfoで取得した画像情報
	 * @return XMLElement img要素
	 */
	public function createElement (Tuple $info) {
		$element = new ImageElement(null, $this->getUserAgent());
		$element->setAttributes($info);
		return $element;
	}

	/**
	 * パラメータ配列から画像コンテナを返す
	 *
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 * @return ImageContainer 画像コンテナ
	 */
	public function search (ParameterHolder $params) {
		$params = Tuple::create($params);
		if (!StringUtils::isBlank($params['src'])) {
			return (new MediaFileFinder)->execute($params);
		}

		$container = (new RecordFinder($params))->execute();
		if (!$container && ($class = $params['class'])) {
			$class = $this->loader->getClass($class);
			if (is_subclass_of($class, 'Record')) {
				$container = TableHandler::create($class)->getRecord($params['id']);
			} else {
				$container = new $class($params['id']);
			}
		}
		if ($container && ($container instanceof ImageContainer)) {
			return $container;
		}
	}

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 */
	public function clear () {
		$this->directory->clear();
	}

	/**
	 * レンダラーのエントリーを全て返す
	 *
	 * @access public
	 * @return Tuple レンダラーのエントリー
	 * @static
	 */
	static public function getRendererEntries () {
		if (!self::$renderers) {
			self::$renderers = Tuple::create();
			foreach (new ConstantHandler('IMAGE_RENDERERS') as $key => $value) {
				$key = StringUtils::toLower(
					StringUtils::explode('_', str_replace('BS_IMAGE_RENDERERS_', '', $key))
				);
				if (!self::$renderers->hasParameter($key[0])) {
					self::$renderers[$key[0]] = Tuple::create();
				}
				self::$renderers[$key[0]][$key[1]] = $value;
			}
		}
		return self::$renderers;
	}
}
