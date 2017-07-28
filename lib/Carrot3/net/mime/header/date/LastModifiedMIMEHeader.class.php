<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header.date
 */

namespace Carrot3;

/**
 * Last-Modifiedヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LastModifiedMIMEHeader extends DateMIMEHeader {
	protected $name = 'Last-Modified';
}

