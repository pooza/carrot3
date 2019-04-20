<?php
namespace Carrot3;

class ScriptSet extends DocumentSet {
	public function getDocumentClass () {
		return 'JavaScriptFile';
	}

	public function update () {
		parent::update();
		RenderManager::getInstance()->clear();
	}

	protected function getDirectoryName () {
		return 'js';
	}

	public function createElement () {
		$element = new ScriptElement;
		$element->setAttribute('src', $this->getURL()->getContents());
		return $element;
	}

	public function __toString () {
		return sprintf('JavaScriptセット "%s"', $this->getName());
	}
}
