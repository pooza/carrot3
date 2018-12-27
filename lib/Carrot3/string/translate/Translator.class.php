<?php
namespace Carrot3;

class Translator implements \IteratorAggregate {
	use BasicObject, Singleton;
	private $lang = 'ja';
	private $dictionaries;
	static private $langs;

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

	private function getDirectory ():Directory {
		return FileUtils::getDirectory('dictionaries');
	}

	public function register (Dictionary $dictionary, bool $priority = Tuple::POSITION_BOTTOM) {
		$name = StringUtils::toLower($dictionary->getDictionaryName());
		$this->dictionaries->setParameter($name, $dictionary, $priority);
	}

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

	private function getWords ($string):Tuple {
		return Tuple::create([
			$string,
			StringUtils::underscorize($string),
			StringUtils::pascalize($string),
			StringUtils::underscorize(Utils::getShortClass($string)),
			StringUtils::pascalize(Utils::getShortClass($string)),
		]);
	}

	private function createDictionaryNames (?string $name):Tuple {
		$names = Tuple::create();
		if (!StringUtils::isBlank($name)) {
			$names[] = $name;
		}
		$names[] = $this->loader->getClass('DictionaryFile') . '.' . $name;
		$names->merge($this->dictionaries->getKeys());
		$names->uniquize();
		return $names;
	}

	public function getLanguage ():string {
		return $this->lang;
	}

	public function setLanguage (string $lang) {
		$lang = StringUtils::toLower($lang);
		if (!Tuple::create(BS_LANGUAGES)->isContain($lang)) {
			$message = new StringFormat('言語コード"%s"が正しくありません。');
			$message[] = $lang;
			throw new TranslateException($message);
		}
		$this->lang = $lang;
	}

	public function createTuple (iterable $words, ?string $lang = 'ja') {
		$values = Tuple::create();
		foreach ($words as $word) {
			$values[$word] = $this->translate($word, $lang);
		}
		return $values;
	}

	public function getIterator () {
		return $this->dictionaries->getIterator();
	}
}
