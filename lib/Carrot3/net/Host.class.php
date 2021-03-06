<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net
 */

namespace Carrot3;

/**
 * ホストコンピュータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Host implements Assignable, ImageContainer {
	use BasicObject;
	protected $name;
	protected $hostname;
	protected $address;
	protected $domain;

	/**
	 * @access public
	 * @param string $address ホスト名又はIPv4アドレス
	 */
	public function __construct ($address) {
		// アドレスが列挙されていたり、ポートが付記されていたら、取り除く。
		$address = mb_split('[:,]', $address)[0];

		if (StringUtils::isBlank($address)) {
			throw new NetException('ホスト名又はIPv4アドレスが空欄です。');
		} else if (mb_ereg('^[.[:digit:]]+$', $address)) {
			if (!long2ip(ip2long($address))) {
				throw new NetException($address . 'は正しいIPv4アドレスではありません。');
			}
			$this->address = $address;
			$this->name = $address;
		} else {
			$this->name = $address;
		}
	}

	/**
	 * IPv4アドレスを返す
	 *
	 * @access public
	 * @return string IPv4アドレス
	 */
	public function getAddress () {
		if (!$this->address) {
			$this->address = gethostbyname($this->name);
		}
		return $this->address;
	}

	/**
	 * ホスト名を返す
	 *
	 * コンストラクタに渡した時の名前。IPv4アドレスの場合あり。
	 *
	 * @access public
	 * @return string FQDNホスト名又はIPv4アドレス
	 */
	public function getName ():?string {
		return $this->name;
	}

	/**
	 * コンストラクタに渡した名前がIPv4アドレスならば、逆引きして返す
	 *
	 * @access public
	 * @return string FQDNホスト名
	 */
	public function resolveReverse () {
		if (StringUtils::isBlank($this->hostname)) {
			if (StringUtils::isBlank($this->address)) {
				$this->hostname = $this->name;
			} else if (mb_ereg('^([0-9]{1,3}\.){3}[0-9]{1,3}$', $this->name)) {
				$this->hostname = gethostbyaddr($this->name);
			}
		}
		return $this->hostname;
	}

	/**
	 * 親ドメインを返す
	 *
	 * @access public
	 * @return string 親ドメイン
	 */
	public function getDomain () {
		if (!$this->domain) {
			$name = StringUtils::explode('.', $this->getName());
			$name->shift();
			$this->domain = $name->join('.');
		}
		return $this->domain;
	}

	/**
	 * 異なるホストか？
	 *
	 * @access public
	 * @param Host $host 対象ホスト
	 * @return bool 異なるホストならTrue
	 */
	public function isForeign (Host $host = null):bool {
		if (!$host) {
			$host = $this->controller->getHost();
		}
		return ($this->getName() != $host->getName());
	}

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 * @param string $size
	 */
	public function removeImageCache (string $size) {
		if ($file = $this->getImageFile('image')) {
			$file->removeImageCache($size);
		}
	}

	/**
	 * 画像の情報を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @param int $pixel ピクセル数
	 * @param int $flags フラグのビット列
	 * @return Tuple 画像の情報
	 */
	public function getImageInfo (string $size, ?int $pixel = null, int $flags = 0) {
		if ($file = $this->getImageFile($size)) {
			$info = (new ImageManager)->getInfo($file, $size, $pixel, $flags);
			$info['alt'] = $this->getName();
			return $info;
		}
	}

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile (string $size):?ImageFile {
		return (new GoogleFaviconsService)->getImageFile($this);
	}

	/**
	 * コンテナのラベルを返す
	 *
	 * @access public
	 * @param string $lang 言語
	 * @return string ラベル
	 */
	public function getLabel (?string $lang = 'ja'):?string {
		return $this->getID();
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return get_object_vars($this);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return $this->getName();
	}
}
