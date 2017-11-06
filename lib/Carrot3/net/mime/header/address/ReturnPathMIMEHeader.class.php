<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header.address
 */

namespace Carrot3;

/**
 * Return-Pathヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ReturnPathMIMEHeader extends AddressMIMEHeader {
	protected $name = 'Return-Path';

	/**
	 * ヘッダの内容からパラメータを抜き出す
	 *
	 * @access protected
	 */
	protected function parse () {
		MIMEHeader::parse();
		if (mb_ereg('^<?([^>]*)>?$', $this->contents, $matches)) {
			$this->email = MailAddress::create($matches[1]);
		}
	}
}
