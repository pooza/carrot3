<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.twitter
 */

namespace Carrot3;

/**
 * Twitterアカウント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TwitterAccount implements ImageContainer, HTTPRedirector {
	use HTTPRedirectorMethods;
	protected $name;
	protected $url;
	private $service;

	/**
	 * @access public
	 * @param mixed $name スクリーンネーム
	 */
	public function __construct ($name) {
		$this->service = new TwitterService;
		$this->name = $name;
	}

	/**
	 * Twitterサービスを返す
	 *
	 * @access public
	 * @return TwitterService Twitterサービス
	 */
	public function getService () {
		return $this->service;
	}

	/**
	 * つぶやく
	 *
	 * @access public
	 * @param mixed $message メッセージ
	 * @return JSONRenderer 結果文書
	 */
	public function tweet ($message) {
		if ($message instanceof MessageContainer) {
			$message = $message->getMessage();
		}
		$query = new WWWFormRenderer;
		$query['status'] = $message;
		$response = $this->getService()->sendPOST('/1.1/statuses/update.json', $query);
		$json = new JSONRenderer;
		$json->setContents($response->getRenderer()->getContents());
		return $json;
	}

	/**
	 * ダイレクトメッセージを送る
	 *
	 * @access public
	 * @param mixed $message メッセージ
	 * @param TwitterAccount $account 宛先アカウント
	 * @return JSONRenderer 結果文書
	 */
	public function sendDirectMessage ($message, TwitterAccount $account) {
		if ($message instanceof MessageContainer) {
			$message = $message->getMessage();
		}
		$query = new WWWFormRenderer;
		$query['screen_name'] = $account->getName();
		$query['text'] = $message;
		$response = $this->getService()->sendPOST('/1.1/direct_messages/new.json', $query);
		$json = new JSONRenderer;
		$json->setContents($response->getRenderer()->getContents());
		return $json;
	}

	/**
	 * ダイレクトメッセージを送る
	 *
	 * @access public
	 * @param string $message メッセージ
	 * @param TwitterAccount $account 宛先アカウント
	 * @return JSONRenderer 結果文書
	 * @final
	 */
	final public function sendDM ($message, TwitterAccount $account) {
		return $this->sendDirectMessage($message, $account);
	}

	/**
	 * タイムラインを返す
	 *
	 * @access public
	 * @param int $count ツイート数
	 * @return Tuple タイムライン
	 */
	public function getTimeline (int $count = 10) {
		return $this->service->getTimeline($this->name, $count);
	}

	/**
	 * プロフィールを返す
	 *
	 * @access public
	 * @return Tuple プロフィール
	 */
	public function getProfile () {
		return $this->service->getProfile($this->name);
	}

	/**
	 * プロフィールアイコン画像を返す
	 *
	 * @access public
	 * @return Image プロフィールアイコン画像
	 */
	public function getIcon () {
		try {
			$url = URL::create($this->getProfile()['profile_image_url_https']);
			$image = new Image;
			$image->setImage($url->fetch());
			$image->setType(MIMEType::getType('png'));
			return $image;
		} catch (HTTPException $e) {
			return null;
		} catch (ImageException $e) {
			return null;
		}
	}

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 * @param string $size
	 */
	public function removeImageCache ($size) {
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
	public function getImageInfo ($size, ?int $pixel = null, int $flags = 0) {
		if ($file = $this->getImageFile()) {
			$info = (new ImageManager)->getInfo($file, $size, $pixel, $flags);
			$info['alt'] = $this->getLabel();
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
	public function getImageFile ($size) {
		$dir = FileUtils::getDirectory('twitter_account');
		if ($file = $dir->getEntry($this->getImageFileBaseName($size), 'ImageFile')) {
			$date = Date::create();
			$date['minute'] = '-' + BS_SERVICE_TWITTER_MINUTES;
			if (!$file->getUpdateDate()->isPast($date)) {
				return $file;
			}
			$file->delete();
		}

		if (!$icon = $this->getIcon()) {
			return null;
		}
		$file = FileUtils::createTemporaryFile('.png', 'ImageFile');
		$file->setEngine($icon);
		$file->save();
		$file->setName($this->getImageFileBaseName($size));
		$file->moveTo($dir);
		return $file;
	}

	/**
	 * 画像ファイルベース名を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return string 画像ファイルベース名
	 */
	public function getImageFileBaseName ($size) {
		return sprintf('%010d_%s', $this->getID(), $size);
	}

	/**
	 * アカウントIDを返す
	 *
	 * @access public
	 * @return int ID
	 */
	public function getID () {
		return (int)$this->getProfile()['id'];
	}

	/**
	 * スクリーン名を返す
	 *
	 * @access public
	 * @return string スクリーン名
	 */
	public function getName () {
		return $this->getProfile()['screen_name'];
	}

	/**
	 * コンテナのラベルを返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string ラベル
	 */
	public function getLabel ($language = 'ja') {
		return $this->getName();
	}

	/**
	 * リダイレクト対象
	 *
	 * @access public
	 * @return URL
	 */
	public function getURL () {
		if (!$this->url) {
			$this->url = URL::create();
			$this->url['scheme'] = 'https';
			$this->url['host'] = 'twitter.com';
			$this->url['path'] = '/' . $this->name;
		}
		return $this->url;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Twitterアカウント "%s"', $this->name);
	}
}
