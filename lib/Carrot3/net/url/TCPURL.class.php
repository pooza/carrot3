<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.url
 */

namespace Carrot3;

/**
 * TCPのURL
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TCPURL extends URL {

	/**
	 * @access protected
	 * @param mixed $contents URL
	 */
	protected function __construct ($contents) {
		parent::__construct($contents);
		$this['scheme'] = 'tcp';
	}
}
