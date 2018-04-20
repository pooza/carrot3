<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage string.translate
 */

namespace Carrot3;

/**
 * 単語翻訳機能
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TranslateManager implements \IteratorAggregate {
	use BasicObject, Singleton;
	private $lang = 'ja';
	private $dictionaries;
	static private $langs;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->dictionaries = Tuple::create();
		$iterator = new DirectoryIterator(
			$this->getDirectory(),
			Directory::WITHOUT_DOTTED
		);
		foreach ($iterator as $dictionary) {
			$this->register($dictionary);
		}
		$this->setDictionaryPriority(
			$this->loader->getClass('DictionaryFile') . '.carrot',
			Tuple::POSITION_BOTTOM
		);
		$this->register(new ConstantHandler);
	}

	private function getDirectory () {
		return FileUtils::getDirectory('dictionaries');
	}

	/**
	 * 辞書を登録
	 *
	 * @access public
	 * @param Dictionary 辞書
	 * @param bool $priority 優先順位 (Tuple::POSITION_TOP|Tuple::POSITION_BOTTOM)
	 */
	public function register (Dictionary $dictionary, bool $priority = Tuple::POSITION_BOTTOM) {
		$name = StringUtils::toLower($dictionary->getDictionaryName());
		$this->dictionaries->setParameter($name, $dictionary, $priority);
	}

	/**
	 * 辞書の優先順位を設定
	 *
	 * @access public
	 * @param string $name 辞書の名前
	 * @param bool $priority 優先順位 (Tuple::POSITION_TOP|Tuple::POSITION_BOTTOM)
	 */
	public function setDictionaryPriority (string $name, bool $priority) {
		$name = StringUtils::toLower($name);
		if (!$dictionary = $this->dictionaries[$name]) {
			$message = new StringFormat('辞書 "%s" は登録されていません。');
			$message[] = $name;
			throw new TranslateException($message);
		}
		$this->dictionaries->removeParameter($name);
		$this->dictionaries->setParameter($name, $dictionary, $priority);
	}

	/**
	 * 単語を変換して返す
	 *
	 * @access public
	 * @param string $label 単語
	 * @param string $name 辞書の名前
	 * @param string $lang 言語
	 * @return string 訳語
	 */
	public function translate (string $label, string $name = null, ?string $lang = null):?string {
		if (StringUtils::isBlank($label)) {
			return null;
		}
		if (StringUtils::isBlank($lang)) {
			$lang = $this->getLanguage();
		}
		foreach ($this->createDictionaryNames($name) as $name) {
			if ($dictionary = $this->dictionaries[$name]) {
				foreach ($this->getWords($label) as $word) {
					$answer = $dictionary->translate($word, $lang);
					if ($answer !== null) {
						return $answer;
					}
				}
			}
		}
		if (BS_DEBUG) {
			$message = new StringFormat('"%s"の訳語が見つかりません。');
			$message[] = $label;
			throw new TranslateException($message);
		} else {
			return $label;
		}
	}

	private function getWords ($string) {
		return Tuple::create([
			$string,
			StringUtils::underscorize($string),
			StringUtils::pascalize($string),
			StringUtils::underscorize(Utils::getShortClass($string)),
			StringUtils::pascalize(Utils::getShortClass($string)),
		]);
	}

	private function createDictionaryNames (?string $name) {
		$names = Tuple::create();
		$names[] = $name;
		$names[] = $this->loader->getClass('DictionaryFile') . '.' . $name;
		$names->merge($this->dictionaries->getKeys());
		$names->uniquize();
		return $names;
	}

	/**
	 * 単語を変換して返す
	 *
	 * translateのエイリアス
	 *
	 * @access public
	 * @param string $string 単語
	 * @param string $name 辞書の名前
	 * @param string $lang 言語
	 * @return string 訳語
	 * @final
	 */
	final public function execute ($string, string $name = null, ?string $lang = null) {
		return $this->translate($string, $name, $lang);
	}

	/**
	 * 言語コードを返す
	 *
	 * @access public
	 * @return string 言語コード
	 */
	public function getLanguage () {
		return $this->lang;
	}

	/**
	 * 言語コードを設定
	 *
	 * @access public
	 * @param string $lang 言語コード
	 */
	public function setLanguage (?string $lang) {
		$lang = StringUtils::toLower($lang);
		if (!self::getLanguageNames()->isContain($lang)) {
			$message = new StringFormat('言語コード"%s"が正しくありません。');
			$message[] = $lang;
			throw new TranslateException($message);
		}
		$this->lang = $lang;
	}

	/**
	 * ハッシュを返す
	 *
	 * @access public
	 * @param iterable $words 見出し語の配列
	 * @param string $lang 言語
	 * @return Tuple ハッシュ
	 */
	public function getHash (iterable $words, ?string $lang = 'ja') {
		$hash = Tuple::create();
		foreach ($words as $word) {
			$hash[$word] = $this->execute($word, $lang);
		}
		return $hash;
	}

	/**
	 * @access public
	 * @return Iterator イテレータ
	 */
	public function getIterator () {
		return $this->dictionaries->getIterator();
	}

	/**
	 * 言語キー配列を出力
	 *
	 * @access public
	 * @return Tuple 言語キー配列
	 * @static
	 */
	static public function getLanguageNames () {
		return self::getLanguages()->createFlipped();
	}

	/**
	 * 言語配列を返す
	 *
	 * @access public
	 * @return Tuple 言語配列
	 * @static
	 */
	static public function getLanguages () {
		if (!self::$langs) {
			self::$langs = self::getInstance()->getHash(
				Tuple::explode(',', BS_LANGUAGES), 'en'
			);
		}
		return self::$langs;
	}
}
