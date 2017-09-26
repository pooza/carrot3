<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html
 */

namespace Carrot3;

/**
 * HTMLの要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTMLElement extends XMLElement {
	protected $tag;
	protected $useragent;
	protected $styles;
	protected $styleClasses;
	protected $raw = true;

	/**
	 * @access public
	 * @param string $name 要素の名前
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function __construct ($name = null, UserAgent $useragent = null) {
		if (StringUtils::isBlank($name)) {
			$name = $this->getTag();
		}

		$this->styles = new CSSSelector;
		$this->styleClasses = Tuple::create();
		parent::__construct($name);

		if (!$useragent) {
			$useragent = $this->request->getUserAgent();
		}
		$this->setUserAgent($useragent);
	}

	/**
	 * タグ名を返す
	 *
	 * @access public
	 * @return string タグ名
	 */
	public function getTag () {
		if (!$this->tag) {
			if (mb_ereg('\\\\([[:alnum:]]+)Element$', Utils::getClass($this), $matches)) {
				$this->tag = StringUtils::toLower($matches[1]);
			}
		}
		return $this->tag;
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
		$this->contents = null;
	}

	/**
	 * IDを返す
	 *
	 * @access public
	 * @return string ID
	 */
	public function getID () {
		return $this->attributes['id'];
	}

	/**
	 * IDを設定
	 *
	 * @access public
	 * @param string $id ID
	 */
	public function setID ($id) {
		if (StringUtils::isBlank($id)) {
			return;
		}
		$this->attributes['id'] = $id;
		$this->contents = null;
	}

	/**
	 * スタイルを返す
	 *
	 * @access public
	 * @param string $name スタイル名
	 * @return string スタイル値
	 */
	public function getStyle ($name) {
		return $this->styles[$name];
	}

	/**
	 * スタイルを設定
	 *
	 * @access public
	 * @param string $name スタイル名
	 * @param string $value スタイル値
	 */
	public function setStyle ($name, $value) {
		if (StringUtils::isBlank($value)) {
			$this->styles->removeParameter($name);
		} else {
			$this->styles[$name] = $value;
		}
		$this->contents = null;
	}

	/**
	 * スタイルを全て返す
	 *
	 * @access public
	 * @return CSSSelector スタイル
	 */
	public function getStyles () {
		return $this->styles;
	}

	/**
	 * スタイルを置き換え
	 *
	 * @access public
	 * @param mixed $styles スタイル
	 */
	public function setStyles ($styles) {
		if ($styles instanceof ParameterHolder) {
			$this->styles = new CSSSelector($styles);
		} else {
			$this->styles->clear();
			$this->styles->setContents($styles);
		}
		$this->contents = null;
	}

	/**
	 * CSSクラスを登録
	 *
	 * @access public
	 * @param mixed $classes クラス名、又はその配列
	 */
	public function registerStyleClass ($classes) {
		if (!is_array($classes) && !($classes instanceof ParameterHolder)) {
			$classes = mb_split('(,| +)', $classes);
		}
		foreach ($classes as $class) {
			$this->styleClasses->push($class);
		}
		$this->styleClasses->uniquize();
		$this->styleClasses->trim();
		$this->contents = null;
	}

	/**
	 * コンテナの配置を設定して返す
	 *
	 * @access protected
	 * @param string $value 配置
	 * @return DivisionElement ラッパー要素
	 */
	public function setAlignment ($value) {
		$wrapper = $this->createWrapper();
		if ($value == 'center') {
			$wrapper->setStyle('width', '100%');
		}
		$wrapper->registerStyleClass($value);
		return $wrapper;
	}

	/**
	 * コンテナのキャプションを設定
	 *
	 * @access public
	 * @param string $value キャプション
	 * @return DivisionElement ラッパー要素
	 */
	public function setCaption ($value) {
		$wrapper = $this->createWrapper();
		if (!StringUtils::isBlank($value)) {
			$element = $wrapper->addElement(new DivisionElement);
			$element->registerStyleClass('caption');
			$element->setBody($value);
		}
		return $wrapper;
	}

	/**
	 * div要素のラッパーを返す
	 *
	 * @access protected
	 * @return DivisionElement ラッパー要素
	 */
	protected function createWrapper () {
		return $this->wrap(new DivisionElement);
	}

	/**
	 * 内容をXMLで返す
	 *
	 * @access public
	 * @return string XML要素
	 */
	public function getContents () {
		if ($this->styles->count()) {
			$this->attributes['style'] = $this->styles->getContents();
		} else {
			$this->attributes->removeParameter('style');
		}
		if ($this->styleClasses->count()) {
			$this->attributes['class'] = $this->styleClasses->join(' ');
		} else {
			$this->attributes->removeParameter('class');
		}

		if (StringUtils::isBlank($this->contents)) {
			$this->createContents();
		}
		return parent::getContents();
	}

	/**
	 * XMLをパースして要素と属性を抽出
	 *
	 * @access public
	 * @param string $contents XML文書
	 */
	public function setContents ($contents) {
		$this->attributes->clear();
		$this->elements->clear();
		$this->body = null;
		$this->contents = $contents;
	}

	/**
	 * 出力を生成
	 *
	 * @access protected
	 */
	protected function createContents () {
		$this->contents = '<' . $this->getName();
		foreach ($this->attributes as $key => $value) {
			if (!StringUtils::isBlank($value)) {
				if ($key == $value) {
					$this->contents .= sprintf(' %s', $key);
				} else {
					$this->contents .= sprintf(' %s="%s"', $key, StringUtils::sanitize($value));
				}
			}
		}
		$this->contents .= '>';

		if (!$this->isEmptyElement()) {
			foreach ($this->elements as $element) {
				$this->contents .= $element->getContents();
			}
			$this->contents .= $this->getBody();
			$this->contents .= '</' . $this->getName() . '>';
		}
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性名
	 * @param mixed $value 属性値
	 */
	public function setAttribute ($name, $value) {
		switch ($name) {
			case 'id':
			case 'container_id':
				return $this->setID($value);
			case 'styles':
			case 'style':
				return $this->setStyles($value);
			case 'class':
			case 'style_class':
				return $this->registerStyleClass($value);
		}
		parent::setAttribute($name, $value);
	}

	/**
	 * 子要素を生成して返す
	 *
	 * @access public
	 * @param string $name 要素名
	 * @param string $body 要素の本文
	 * @return XMLElement 要素
	 */
	public function createElement ($name, $body = null) {
		$element = $this->addElement(new HTMLElement($name, $this->getUserAgent()));
		$element->setBody($body);
		return $element;
	}

	/**
	 * 上位のタグでくくって返す
	 *
	 * @access public
	 * @param XMLElement $parent 上位の要素
	 * @return XMLElement 上位の要素
	 */
	public function wrap (XMLElement $parent) {
		$parent = parent::wrap($parent);
		$parent->setUserAgent($this->getUserAgent());
		return $parent;
	}
}

