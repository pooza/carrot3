<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml
 */

namespace Carrot3;

/**
 * XML要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class XMLElement implements \IteratorAggregate {
	use BasicObject;
	protected $contents;
	protected $body;
	protected $name;
	protected $attributes;
	protected $elements;
	protected $raw = false;
	protected $empty = false;

	/**
	 * @access public
	 * @param string $name 要素の名前
	 */
	public function __construct ($name = null) {
		$this->attributes = Tuple::create();
		$this->elements = Tuple::create();
		if (!StringUtils::isBlank($name)) {
			$this->setName($name);
		}
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return string 属性値
	 */
	public function getAttribute ($name) {
		return $this->attributes[$name];
	}

	/**
	 * 属性を全て返す
	 *
	 * @access public
	 * @return Tuple 属性値
	 */
	public function getAttributes () {
		return $this->attributes;
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性名
	 * @param mixed $value 属性値
	 */
	public function setAttribute ($name, $value) {
		$value = trim($value);
		$value = StringUtils::convertEncoding($value, 'utf-8');
		$this->attributes[$name] = $value;
		$this->contents = null;
	}

	/**
	 * 属性を削除
	 *
	 * @access public
	 * @param string $name 属性名
	 */
	public function removeAttribute ($name) {
		$this->attributes->removeParameter($name);
		$this->contents = null;
	}

	/**
	 * 属性をまとめて設定
	 *
	 * @access public
	 * @param string[] $values 属性の配列
	 */
	public function setAttributes ($values) {
		foreach ($values as $key => $value) {
			$this->setAttribute($key, $value);
		}
	}

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @return string 名前
	 */
	public function getName () {
		return $this->name;
	}

	/**
	 * 名前を設定
	 *
	 * @access public
	 * @param string $name 名前
	 */
	public function setName ($name) {
		$this->name = $name;
		$this->contents = null;
	}

	/**
	 * 本文を返す
	 *
	 * @access public
	 * @return string 本文
	 */
	public function getBody () {
		return $this->body;
	}

	/**
	 * 本文を設定
	 *
	 * @access public
	 * @param string $body 本文
	 */
	public function setBody ($body = null) {
		if (Numeric::isZero($body)) {
			$this->body = 0;
		} else if (StringUtils::isBlank($body)) {
			$this->body = null;
		} else {
			if ($body instanceof StringFormat) {
				$body = $body->getContents();
			}
			$body = trim($body);
			$body = StringUtils::convertEncoding($body, 'utf-8');
			$this->body = $body;
		}
		$this->contents = null;
	}

	/**
	 * 指定した名前に一致する要素を返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return XMLElement 名前に一致する最初の要素
	 */
	public function getElement ($name) {
		foreach ($this->getElements() as $child) {
			if ($child->getName() == $name) {
				return $child;
			}
		}
	}

	/**
	 * 子要素を全て返す
	 *
	 * @access public
	 * @return Tuple 子要素全て
	 */
	public function getElements () {
		return $this->elements;
	}

	/**
	 * 空要素か？
	 *
	 * @access public
	 * @return boolean 空要素ならTrue
	 */
	public function isEmptyElement () {
		return $this->empty;
	}

	/**
	 * 空要素かを設定
	 *
	 * @access public
	 * @param boolean $flag 空要素ならTrue
	 */
	public function setEmptyElement ($flag) {
		$this->empty = $flag;
	}

	/**
	 * 子要素を追加
	 *
	 * @access public
	 * @param XMLElement $element 要素
	 * @return XMLElement 追加した要素
	 */
	public function addElement (XMLElement $element) {
		$this->elements[] = $element;
		$this->contents = null;
		return $element;
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
		$element = $this->addElement(new XMLElement($name));
		$element->setBody($body);
		return $element;
	}

	/**
	 * 要素を検索して返す
	 *
	 * @access public
	 * @param string $path 絶対ロケーションパス
	 * @return XMLElement 最初にマッチした要素
	 */
	public function query ($path) {
		$path = StringUtils::explode('/', $path);
		$path->shift();
		if (!$path->count() || ($path->shift() != $this->getName())) {
			return;
		}

		$element = $this;
		foreach ($path as $name) {
			if (!$element = $element->getElement($name)) {
				return;
			}
		}
		return $element;
	}

	/**
	 * ネームスペースを返す
	 *
	 * @access public
	 * @return string ネームスペース
	 */
	public function getNamespace () {
		return $this->getAttribute('xmlns');
	}

	/**
	 * ネームスペースを設定
	 *
	 * @access public
	 * @param string $namespace ネームスペース
	 */
	public function setNamespace ($namespace) {
		$this->setAttribute('xmlns', $namespace);
	}

	/**
	 * 内容をXMLで返す
	 *
	 * @access public
	 * @return string XML要素
	 */
	public function getContents () {
		if (!$this->contents) {
			$this->contents = '<' . $this->getName();
			foreach ($this->attributes as $key => $value) {
				if (!StringUtils::isBlank($value)) {
					$this->contents .= sprintf(' %s="%s"', $key, StringUtils::sanitize($value));
				}
			}

			if ($this->isEmptyElement()) {
				return $this->contents .= ' />';
			}

			$this->contents .= '>';
			foreach ($this->elements as $element) {
				$this->contents .= $element->getContents();
			}
			if ($this->raw) {
				$this->contents .= $this->getBody();
			} else {
				$this->contents .= StringUtils::sanitize($this->getBody());
			}
			$this->contents .= '</' . $this->getName() . '>';
		}
		return $this->contents;
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

		$xml = new DOMDocument('1.0', 'utf-8');
		try {
			$xml->loadXML($contents);
		} catch (\Exception $e) {
			$message = new StringFormat('パースエラーです。 (%s)');
			$message[] = StringUtils::stripTags($e->getMessage());
			throw new XMLException($message);
		}

		$stack = Tuple::create();
		$reader = new XMLReader;
		$reader->xml($contents);
		while ($reader->read()) {
			switch ($reader->nodeType) {
				case XMLReader::ELEMENT:
					if ($stack->count()) {
						$element = $stack->getIterator()->getLast()->createElement($reader->name);
					} else {
						$element = $this;
						$this->setName($reader->name);
					}
					if (!$reader->isEmptyElement) {
						$stack[] = $element;
					}
					while ($reader->moveToNextAttribute()) {
						$element->setAttribute($reader->name, $reader->value);
					}
					break;
				case XMLReader::END_ELEMENT:
					$stack->pop();
					break;
				case XMLReader::TEXT:
					$stack->getIterator()->getLast()->setBody($reader->value);
					break;
			}
		}
	}

	/**
	 * 上位のタグでくくって返す
	 *
	 * @access public
	 * @param XMLElement $parent 上位の要素
	 * @return XMLElement 上位の要素
	 */
	public function wrap (XMLElement $parent) {
		$parent->addElement($this);
		return $parent;
	}

	/**
	 * RAWモードを返す
	 *
	 * @access public
	 * @return boolean RAWモード
	 */
	public function isRawMode () {
		return $this->raw;
	}

	/**
	 * RAWモードを設定
	 *
	 * RAWモード時は、本文のHTMLエスケープを行わない
	 *
	 * @access public
	 * @param boolean $mode RAWモード
	 */
	public function setRawMode ($mode) {
		$this->raw = !!$mode;
		$this->body = null;
		$this->contents = null;
	}

	/**
	 * @access public
	 * @return Iterator イテレータ
	 */
	public function getIterator () {
		return new Iterator($this->elements);
	}
}
