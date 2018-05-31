<?php
namespace Carrot3;

class FileRenderStorage implements RenderStorage {
	private $directory;

	public function __construct () {
		$this->directory = FileUtils::getDirectory('output');
	}

	public function getCache (Action $action):?Tuple {
		if ($file = $this->directory->getEntry($action->digest())) {
			return Tuple::create((new PHPSerializer)->decode($file->getContents()));
		}
		return null;
	}

	public function removeCache (Action $action) {
		if ($file = $this->directory->getEntry($action->digest())) {
			return $file->delete();
		}
	}

	public function cache (HTTPResponse $view) {
		$data = ['headers' => [], 'contents' => $view->getRenderer()->getContents()];
		foreach ($view->getHeaders() as $header) {
			if ($header->isVisible() && $header->isCacheable()) {
				$data['headers'][$header->getName()] = $header->getContents();
			}
		}
		$file = $this->directory->createEntry($view->getAction()->digest());
		$file->setContents((new PHPSerializer)->encode($data));
	}

	public function hasCache (Action $action):bool {
		return !!$this->directory->getEntry($action->digest());
	}

	public function clear () {
		$this->directory->clear();
	}
}
