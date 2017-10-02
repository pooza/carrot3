<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * 絵文字
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Pictogram implements Assignable, ImageContainer {
	use BasicObject;
	private $id;
	private $name;
	private $codes;
	private $names;
	private $imagefile;
	private $element;
	private $imageinfo;
	private $url;
	static private $instances;

	/**
	 * @access private
	 * @name integer $id 絵文字コード
	 */
	private function __construct ($id) {
		$this->id = $id;
		$config = ConfigManager::getInstance()->compile('pictogram');
		$this->codes = Tuple::create($config['codes'][$this->getName()]);
	}

	/**
	 * フライウェイトインスタンスを返す
	 *
	 * @access public
	 * @name string $name 絵文字コード又は絵文字名
	 * @return Pictogram 絵文字
	 * @static
	 */
	static public function create ($name) {
		if (!self::$instances) {
			self::$instances = Tuple::create();
		}

		if (StringUtils::isBlank($id = self::getPictogramCode($name))) {
			$message = new StringFormat('絵文字 "%s" が見つかりません。');
			$message[] = $name;
			throw new Exception($message);
		}
		if (!self::$instances[$id]) {
			self::$instances[$id] = new self($id);
		}
		return self::$instances[$id];
	}

	/**
	 * 絵文字の名前を返す
	 *
	 * DoCoMoの公式名
	 *
	 * @access public
	 * @return string 名前
	 */
	public function getName () {
		return $this->getNames()->getIterator()->getFirst();
	}

	/**
	 * 絵文字の呼称を全て返す
	 *
	 * @access public
	 * @return Tuple 全ての呼称
	 */
	public function getNames () {
		if (!$this->names) {
			$this->names = Tuple::create();
			$config = ConfigManager::getInstance()->compile('pictogram');
			$this->names->merge($config['names'][$this->getID()]);
		}
		return $this->names;
	}

	/**
	 * 絵文字コードを返す
	 *
	 * @access public
	 * @return string 絵文字コード
	 */
	public function getID () {
		return $this->id;
	}

	/**
	 * 絵文字コードを返す
	 *
	 * getIDのエイリアス
	 *
	 * @access public
	 * @return string 絵文字コード
	 * @final
	 */
	final public function getCode () {
		return $this->getID();
	}

	/**
	 * ユーザーのブラウザに適切な絵文字表記を返す
	 *
	 * ケータイに対しては数値文字参照、PCに対してはimg要素
	 *
	 * @access public
	 * @return string 絵文字表記
	 */
	public function getContents () {
		if ($this->request['without_pictogram_emulate']) {
			$useragent = $this->request->getUserAgent();
		} else {
			$useragent = $this->request->getRealUserAgent();
		}
		if ($useragent->isMobile()) {
			return $this->getNumericReference();
		} else {
			$images = $useragent->createImageManager();
			return $images->createElement($this->getImageInfo('image'))->getContents();
		}
	}

	/**
	 * 数値文字参照を返す
	 *
	 * @access public
	 * @return string 数値文字参照
	 */
	public function getNumericReference () {
		$carrier = 'Docomo';
		if ($this->request->isMobile()) {
			$carrier = $this->request->getUserAgent()->getCarrier();
		}
		if (StringUtils::isBlank($code = $this->codes[$carrier])) {
			$code = $this->codes['Docomo'];
		}
		return '&#' . $code . ';';
	}

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 * @param string $size
	 */
	public function removeImageCache ($size) {
	}

	/**
	 * 画像の情報を返す
	 *
	 * @access public
	 * @param string $size ダミー
	 * @param integer $pixel ダミー
	 * @param integer $flags ダミー
	 * @return Tuple 画像の情報
	 */
	public function getImageInfo ($size, $pixel = null, $flags = 0) {
		if (!$this->imageinfo) {
			$this->imageinfo = Tuple::create();
			$image = $this->getImageFile('image')->getEngine();
			$this->imageinfo['url'] = $this->getURL()->getContents();
			$this->imageinfo['width'] = $image->getWidth();
			$this->imageinfo['height'] = $image->getHeight();
			$this->imageinfo['alt'] = $this->getName();
			$this->imageinfo['type'] = $image->getType();
		}
		return $this->imageinfo;
	}

	/**
	 * 画像のURLを返す
	 *
	 * @access public
	 * @return URL URL
	 */
	public function getURL () {
		if (!$this->url) {
			$this->url = FileUtils::createURL(
				'pictogram',
				$this->getImageFile('image')->getName()
			);
		}
		return $this->url;
	}

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile ($size) {
		if (!$this->imagefile) {
			$dir = FileUtils::getDirectory('pictogram');
			$this->imagefile = $dir->getEntry($this->getImageFileBaseName($size), 'ImageFile');
		}
		return $this->imagefile;
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
	 * ラベルを返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string ラベル
	 */
	public function getLabel ($language = 'ja') {
		return $this->getName();
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return $this->getContents();
	}

	/**
	 * 絵文字コードを返す
	 *
	 * @access public
	 * @param mixed $name 絵文字名、又は絵文字コード
	 * @return integer 絵文字コード
	 * @static
	 */
	static public function getPictogramCode ($name) {
		$config = ConfigManager::getInstance()->compile('pictogram');
		if (is_numeric($name) && isset($config['names'][$name])) {
			return $name;
		} else if (isset($config['codes'][$name]['Docomo'])) {
			return $config['codes'][$name]['Docomo'];
		}
	}

	/**
	 * 絵文字を全て返す
	 *
	 * @access public
	 * @return Tuple 絵文字
	 * @static
	 */
	static public function getPictograms () {
		$config = ConfigManager::getInstance()->compile('pictogram');
		$pictograms = Tuple::create();
		foreach ($config['codes'] as $name => $entry) {
			$pictograms[$name] = self::getInstance($entry[BSMobileCarrier::DEFAULT_CARRIER]);
		}
		return $pictograms;
	}

	/**
	 * 絵文字名を全て返す
	 *
	 * @access public
	 * @return Tuple 絵文字名
	 * @static
	 */
	static public function getPictogramNames () {
		return Tuple::create(
			ConfigManager::getInstance()->compile('pictogram')['codes']
		)->getKeys();
	}

	/**
	 * 絵文字の画像ファイルを全て返す
	 *
	 * @access public
	 * @return Tuple 絵文字名
	 * @static
	 */
	static public function getPictogramImageInfos () {
		$key = __CLASS__ . '.' . __FUNCTION__;
		if (!$this->controller->getAttribute($key)) {
			$urls = Tuple::create();
			foreach (self::getPictograms() as $pictogram) {
				foreach ($pictogram->getNames() as $name) {
					$urls[$name] = Tuple::create();
					$urls[$name]['name'] = $name;
					$urls[$name]['image'] = $pictogram->getImageInfo('image');
				}
			}
			$this->controller->setAttribute($key, $urls);
		}
		return $this->controller->getAttribute($key);
	}
}

