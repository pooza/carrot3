<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * 基本的なオブジェクト
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
trait BasicObject {

	/**
	 * @access public
	 * @param string $name プロパティ名
	 * @return mixed 各種オブジェクト
	 */
	public function __get ($name) {
		switch ($name) {
			case 'loader':
				return Loader::getInstance();
			case 'controller':
			case 'request':
			case 'database':
				return Utils::executeMethod(
					Loader::getInstance()->getClass($name),
					'getInstance'
				);
			case 'user':
				$class = $this->loader->getClass(BS_USER_CLASS);
				return $class::getInstance();
			case 'useragent':
				return Request::getInstance()->getUserAgent();
			case 'translator':
				return TranslateManager::getInstance();
		}
	}
}

