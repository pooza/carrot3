<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net
 */

namespace Carrot3;

/**
 * サブネットワーク
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @link http://pear.php.net/package/Net_IPv4/ 参考
 */
class Network extends Host {
	protected $bitmask;
	protected $netmask;
	protected $broadcast;

	/**
	 * @access public
	 * @param string $address CIDR形式のIPv4アドレス
	 */
	public function __construct ($address) {
		if (!mb_ereg('^([.[:digit:]]+)/([[:digit:]]+)$', $address, $matches)) {
			throw new NetException($address . 'をパースできません。');
		} else if (!long2ip(ip2long($matches[1]))) {
			throw new NetException($address . 'をパースできません。');
		}
		$this->address = $matches[1];
		$this->bitmask = (int)$matches[2];

		$config = ConfigManager::getInstance()->compile('netmask');
		if (StringUtils::isBlank($this->netmask = $config['netmasks'][$this->bitmask])) {
			throw new NetException($address . 'をパースできません。');
		}

		$this->broadcast = long2ip(ip2long($this->address) |
			(ip2long($this->netmask) ^ ip2long('255.255.255.255'))
		);
	}

	/**
	 * CIDR形式IPv4アドレスを返す
	 *
	 * @access public
	 * @return string CIDR形式ネットワークアドレス
	 */
	public function getCIDR () {
		return $this->address . '/' . $this->bitmask;
	}

	/**
	 * ブロードキャストアドレスを返す
	 *
	 * @access public
	 * @return string ブロードキャストアドレス
	 */
	public function getBroadcastAddress () {
		return $this->broadcast;
	}

	/**
	 * ネットワーク内のノードか？
	 *
	 * @access public
	 * @param Host $host 対象ホスト
	 * @return boolean ネットワーク内ならTrue
	 */
	public function isContain (Host $host) {
		$network = self::ip2double($this->address);
		$broadcast = self::ip2double($this->broadcast);
		$address = self::ip2double($host->getAddress());
		return ($network <= $address) && ($address <= $broadcast);
	}
	static private function ip2double ($address) {
		return (double)(sprintf('%u', ip2long($address)));
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('ネットワーク "%s"', $this->getCIDR());
	}
}
