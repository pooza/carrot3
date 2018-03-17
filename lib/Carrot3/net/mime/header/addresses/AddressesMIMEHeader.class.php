<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header.addresses
 */

namespace Carrot3;

/**
 * 複数のメールアドレスを格納する抽象ヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class AddressesMIMEHeader extends MIMEHeader {
	private $addresses;

	/**
	 * 実体を返す
	 *
	 * @access public
	 * @return MailAddress 実体
	 */
	public function getEntity () {
		if (!$this->addresses) {
			$this->addresses = Tuple::create();
		}
		return $this->addresses;
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		$this->addresses = Tuple::create();
		$this->appendContents($contents);
	}

	/**
	 * 内容を追加
	 *
	 * @access public
	 * @param string $contents 内容
	 */
	public function appendContents ($contents) {
		$addresses = $this->getEntity();
		if ($contents instanceof MailAddress) {
			$addresses[] = $contents;
		} else if (is_iterable($contents)) {
			foreach ($contents as $address) {
				if ($address instanceof MailAddress) {
					$addresses[] = $address;
				} else {
					$addresses[] = MailAddress::create($address);
				}
			}
		} else {
			$contents = MIMEUtils::decode($contents);
			foreach (mb_split('[;,]', $contents) as $address) {
				if ($address = MailAddress::create($address)) {
					$addresses[] = $address;
				}
			}
		}

		$contents = Tuple::create();
		foreach ($addresses as $address) {
			$contents[] = $address->format();
		}
		$this->contents = $contents->join(', ');
	}
}
