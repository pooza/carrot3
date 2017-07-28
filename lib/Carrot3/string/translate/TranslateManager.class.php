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
	private $language = 'ja';
	private $dictionaries;
	static private $languages;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->dictionaries = Tuple::create();
		foreach ($this->getDirectory() as $dictionary) {
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
	 * @param boolean $priority 優先順位 (Tuple::POSITION_TOP|Tuple::POSITION_BOTTOM)
	 */
	public function register (Dictionary $dictionary, $priority = Tuple::POSITION_BOTTOM) {
		$name = StringUtils::toLower($dictionary->getDictionaryName());
		$this->dictionaries->setParameter($name, $dictionary, $priority);
	}

	/**
	 * 辞書の優先順位を設定
	 *
	 * @access public
	 * @param string $name 辞書の名前
	 * @param boolean $priority 優先順位 (Tuple::POSITION_TOP|Tuple::POSITION_BOTTOM)
	 */
	public function setDictionaryPriority ($name, $priority) {
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
	 * @param string $string 単語
	 * @param string $name 辞書の名前
	 * @param string $language 言語
	 * @return string 訳語
	 */
	public function translate ($string, $name = null, $language = null) {
		if (StringUtils::isBlank($string)) {
			return null;
		}
		if (StringUtils::isBlank($language)) {
			$language = $this->getLanguage();
		}
		foreach ($this->createDictionaryNames($name) as $name) {
			if ($dictionary = $this->dictionaries[$name]) {
				foreach ($this->getWords($string) as $word) {
					$answer = $dictionary->translate($word, $language);
					if ($answer !== null) {
						return $answer;
					}
				}
			}
		}
		if (BS_DEBUG) {
			$message = new StringFormat('"%s"の訳語が見つかりません。');
			$message[] = $string;
			throw new TranslateException($message);
		} else {
			return $string;
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

	private function createDictionaryNames ($name) {
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
	 * @param string $language 言語
	 * @return string 訳語
	 * @final
	 */
	final public function execute ($string, $name = null, $language = null) {
		return $this->translate($string, $name, $language);
	}

	/**
	 * 言語コードを返す
	 *
	 * @access public
	 * @return string 言語コード
	 */
	public function getLanguage () {
		return $this->language;
	}

	/**
	 * 言語コードを設定
	 *
	 * @access public
	 * @param string $language 言語コード
	 */
	public function setLanguage ($language) {
		$language = StringUtils::toLower($language);
		if (!self::getLanguageNames()->isContain($language)) {
			$message = new StringFormat('言語コード"%s"が正しくありません。');
			$message[] = $language;
			throw new TranslateException($message);
		}
		$this->language = $language;
	}

	/**
	 * ハッシュを返す
	 *
	 * @access public
	 * @param string[] $words 見出し語の配列
	 * @param string $language 言語
	 * @return Tuple ハッシュ
	 */
	public function getHash ($words, $language = 'ja') {
		$hash = Tuple::create();
		foreach ($words as $word) {
			$hash[$word] = $this->execute($word, $language);
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
		if (!self::$languages) {
			self::$languages = self::getInstance()->getHash(
				Tuple::explode(',', BS_LANGUAGES), 'en'
			);
		}
		return self::$languages;
	}
}

