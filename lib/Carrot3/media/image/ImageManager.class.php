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

	/**
	 * @access public
	 * @param mixed $flags フラグのビット列、又は配列
	 */
	public function __construct (int $flags = 0) {
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
	public function getUserAgent ():UserAgent {
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
		$this->flags = $this->createBits($flags);
	}

	/**
	 * ビット列を生成して返す
	 *
	 * @access public
	 * @param mixed $arg フラグのビット列、又は配列
	 * @return int
	 */
	public function createBits ($arg):?int {
		if (is_numeric($arg)) {
			return (int)$arg;
		} else if (is_string($arg)) {
			$bits = 0;
			foreach (StringUtils::explode(',', $arg) as $flag) {
				if ($bit = (new ConstantHandler)['Carrot3\\ImageManager::' . $flag]) {
					$bits |= $bit;
				} else {
					$message = new StringFormat('Carrot3\\ImageManager::%sが未定義です。');
					$message[] = $flag;
					throw new ImageException($message);
				}
			}
			return $bits;
		}
		return null;
	}

	/**
	 * 画像のタイプを返す
	 *
	 * @access public
	 * @return string タイプ
	 */
	public function getType ():string {
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
	 * @return HTTPURL URL
	 */
	public function createURL (ImageContainer $record, string $size, ?int $pixel = null, int $flags = 0):?HTTPURL {
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
	public function removeEntry (ImageContainer $record, string $size) {
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
	 *   self::WIDTH_FIXED 幅固定
	 * @return Tuple 画像の情報
	 */
	public function getInfo (ImageContainer $record, string $size, ?int $pixel = null, int $flags = 0) {
		$flags |= $this->flags;
		if (!$file = $this->getFile($record, $size, $pixel, $flags)) {
			return;
		}
		try {
			$image = $file->getRenderer();
		} catch (\Throwable $e) {
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

	protected function getFile (ImageContainer $record, string $size, $pixel, int $flags = 0) {
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
			if ($flags & self::WIDTH_FIXED) {
				$image->resizeWidth($pixel);
			} else {
				$image->resizeSquare($pixel);
			}
		} else if ($width = $this->getDefaultWidth()) {
			$image->resizeWidth($width);
		}
		return $image;
	}

	protected function createEntryName (ImageContainer $record, string $size) {
		return Crypt::digest([
			Utils::getClass($record),
			$record->getID(),
			$size,
		]);
	}

	protected function getEntryDirectory (ImageContainer $record, string $size) {
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
	 * @param iterable $info getInfoで取得した画像情報
	 * @return XMLElement img要素
	 */
	public function createElement (iterable $info) {
		$element = new ImageElement(null, $this->getUserAgent());
		$element->setAttributes($info);
		return $element;
	}

	/**
	 * パラメータ配列から画像コンテナを返す
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 * @return ImageContainer 画像コンテナ
	 */
	public function search (iterable $params) {
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
