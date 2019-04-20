<?php
namespace Carrot3;

class StyleSet extends DocumentSet {
	private $selectors;

	public function getDocumentClass () {
		return 'CSSFile';
	}

	public function update () {
		parent::update();
		RenderManager::getInstance()->clear();
	}

	protected function getDirectoryName () {
		return 'css';
	}

	public function getSelectors () {
		if (!$this->selectors) {
			$this->selectors = new CSSSParser($this->getContents());
		}
		return $this->selectors;
	}

	public function createElement () {
		$element = new HTMLElement('link');
		$element->setEmptyElement(true);
		$element->setAttribute('rel', 'stylesheet');
		$element->setAttribute('href', $this->getURL()->getContents());
		return $element;
	}

	public function __toString () {
		return sprintf('スタイルセット "%s"', $this->getName());
	}
}
