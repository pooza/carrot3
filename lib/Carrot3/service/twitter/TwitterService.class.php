<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.twitter
 */

namespace Carrot3;

/**
 * Twitterクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TwitterService extends CurlHTTP {
	protected $consumerKey;
	protected $consumerSecret;
	protected $accessToken;
	protected $accessTokenSecret;
	protected $bearerToken;
	protected $oauth;
	protected $serializeHandler;
	const DEFAULT_HOST = 'api.twitter.com';

	/**
	 * @access public
	 * @param Host $host ホスト
	 * @param integer $port ポート
	 */
	public function __construct (Host $host = null, $port = null) {
		if (!$host) {
			$host = new Host(self::DEFAULT_HOST);
			$port = NetworkService::getPort('https');
		}
		parent::__construct($host, $port);
	}

	/**
	 * コンシューマキーを返す
	 *
	 * @access public
	 * @return string コンシューマキー
	 */
	public function getConsumerKey () {
		if (!$this->consumerKey) {
			$this->consumerKey = BS_SERVICE_TWITTER_CONSUMER_KEY;
		}
		return $this->consumerKey;
	}

	/**
	 * コンシューマキーを設定
	 *
	 * @access public
	 * @param string $value コンシューマキー
	 */
	public function setConsumerKey ($value) {
		$this->consumerKey = $value;
	}

	/**
	 * コンシューマシークレットを返す
	 *
	 * @access public
	 * @return string コンシューマシークレット
	 */
	public function getConsumerSecret () {
		if (!$this->consumerSecret) {
			$this->consumerSecret = BS_SERVICE_TWITTER_CONSUMER_SECRET;
		}
		return $this->consumerSecret;
	}

	/**
	 * コンシューマシークレットを設定
	 *
	 * @access public
	 * @param string $value コンシューマシークレット
	 */
	public function setConsumerSecret ($value) {
		$this->consumerSecret = $value;
	}

	/**
	 * アクセストークンを返す
	 *
	 * @access public
	 * @return string アクセストークン
	 */
	public function getAccessToken () {
		if (!$this->accessToken) {
			$this->accessToken = BS_SERVICE_TWITTER_ACCESS_TOKEN;
		}
		return $this->accessToken;
	}

	/**
	 * アクセストークンを設定
	 *
	 * @access public
	 * @param string $value アクセストークン
	 */
	public function setAccessToken ($value) {
		$this->accessToken = $value;
	}

	/**
	 * アクセストークンシークレットを返す
	 *
	 * @access public
	 * @return string アクセストークンシークレット
	 */
	public function getAccessTokenSecret () {
		if (!$this->accessTokenSecret) {
			$this->accessTokenSecret = BS_SERVICE_TWITTER_ACCESS_TOKEN_SECRET;
		}
		return $this->accessTokenSecret;
	}

	/**
	 * アクセストークンシークレットを設定
	 *
	 * @access public
	 * @param string $value アクセストークンシークレット
	 */
	public function setAccessTokenSecret ($value) {
		$this->accessTokenSecret = $value;
	}

	protected function createCredential () {
		$values = Tuple::create();
		$values[] = $this->getConsumerKey();
		$values[] = $this->getConsumerSecret();
		return MIMEUtils::encodeBase64($values->join(':'));
	}

	protected function getBearerToken () {
		$key = Tuple::create([
			$this->getConsumerKey(),
			$this->getConsumerSecret(),
			__CLASS__,
			__FUNCTION__,
		])->join(':');
		$date = Date::create();
		$date['minute'] = '-' . BS_SERVICE_TWITTER_MINUTES;
		if (!$value = $this->getSerializeHandler()->getAttribute($key, $date)) {
			$request = new HTTPRequest;
			$request->setMethod('POST');
			$request->setURL($this->createRequestURL('/oauth2/token'));
			$request->setHeader('Authorization', 'Basic ' . $this->createCredential());
			$request->setRenderer(new WWWFormRenderer);
			$request->getRenderer()['grant_type'] = 'client_credentials';
			$this->setAttribute('post', true);
			$this->setAttribute('postfields', $request->getRenderer()->getContents());
			$response = $this->send($request);
			$this->log($response);

			$json = new JSONRenderer;
			$json->setContents($response->getRenderer()->getContents());
			$value = $json->getResult()['access_token'];
			$this->getSerializeHandler()->setAttribute($key, $value);
		}
		return $value;
	}

	protected function createSignatureKey () {
		return Tuple::create([
			URL::encode($this->getConsumerSecret()),
			URL::encode($this->getAccessTokenSecret()),
		])->join('&');
	}

	protected function createSignatureData (HTTPRedirector $url, WWWFormRenderer $params) {
		return Tuple::create([
			'POST',
			URL::encode($url->getContents()),
			URL::encode(str_replace(
				['+' , '%7E'],
				['%20' , '~'],
				$params->getContents()
			)),
		])->join('&');
	}

	protected function createOAuth ($path, ParameterHolder $params) {
		$url = $this->createRequestURL($path);
		$params = Tuple::create($params);
		$params['oauth_token'] = $this->getAccessToken();
		$params['oauth_consumer_key'] = $this->getConsumerKey();
		$params['oauth_signature_method'] = 'HMAC-SHA1';
		$params['oauth_timestamp'] = Date::create()->getTimestamp();
		$params['oauth_nonce'] = Date::create()->format('YmdHis') . Numeric::getRandom(1000, 9999);
		$params['oauth_version'] = '1.0';
		$params->sort();

		$oauth = new WWWFormRenderer;
		$oauth->setParameters($params);
		$oauth['oauth_signature'] = MIMEUtils::encodeBase64(hash_hmac(
			'sha1',
			$this->createSignatureData($url, $oauth),
			$this->createSignatureKey(),
			true
		));
		return $oauth;
	}

	/**
	 * タイムラインを返す
	 *
	 * @access public
	 * @param string $account アカウント
	 * @param integer $count ツイート数
	 * @return Tuple タイムライン
	 */
	public function getTimeline ($account, $count = 10) {
		$key = Tuple::create([
			$account,
			$count,
			__CLASS__,
			__FUNCTION__,
		])->join(':');
		$date = Date::create();
		$date['minute'] = '-' . BS_SERVICE_TWITTER_MINUTES;
		if (!$timeline = $this->getSerializeHandler()->getAttribute($key, $date)) {
			$timeline = Tuple::create();
			$url = $this->createRequestURL('/1.1/statuses/user_timeline.json');
			$url->setParameter('screen_name', $account);
			$url->setParameter('count', $count);
			$response = $this->sendGET($url->getFullPath());

			$json = new JSONRenderer;
			$json->setContents($response->getRenderer()->getContents());
			foreach ($json->getResult() as $entry) {
				$urls = self::createTweetURLs($entry['id_str'], $account);
				$timeline[] = Tuple::create([
					'id' => $entry['id_str'],
					'from_user' => $entry['user']['screen_name'],
					'text' => $entry['text'],
					'created_at' => $entry['created_at'],
					'url0' => $urls['url0'],
					'url_mobile' => $urls['url_mobile'],
					'profile_image_url' => $entry['user']['profile_image_url_https'],
				]);
			}
			$this->getSerializeHandler()->setAttribute($key, $timeline);
		}
		return $timeline;
	}

	/**
	 * ツイートを検索して返す
	 *
	 * @access public
	 * @param string $keyword キーワード
	 * @param integer $count ツイート数
	 * @return Tuple ツイート
	 */
	public function searchTweets ($keyword, $count = 10) {
		$key = Tuple::create([
			$keyword,
			$count,
			__CLASS__,
			__FUNCTION__,
		])->join(':');
		$date = Date::create();
		$date['minute'] = '-' . BS_SERVICE_TWITTER_MINUTES;
		if (!$timeline = $this->getSerializeHandler()->getAttribute($key, $date)) {
			$timeline = Tuple::create();
			$url = $this->createRequestURL('/1.1/search/tweets.json');
			$url->setParameter('q', $keyword);
			$url->setParameter('count', $count);
			$response = $this->sendGET($url->getFullPath());

			$json = new JSONRenderer;
			$json->setContents($response->getRenderer()->getContents());
			foreach ($json->getResult()['statuses'] as $entry) {
				$urls = self::createTweetURLs($entry['id'], $entry['user']['screen_name']);
				$timeline[] = Tuple::create([
					'id' => $entry['id'],
					'from_user' => $entry['user']['screen_name'],
					'text' => $entry['text'],
					'created_at' => $entry['created_at'],
					'url0' => $urls['url0'],
					'url_mobile' => $urls['url_mobile'],
					'profile_image_url' => $entry['user']['profile_image_url_https'],
				]);
			}
			$this->getSerializeHandler()->setAttribute($key, $timeline);
		}
		return $timeline;
	}

	/**
	 * プロフィールを返す
	 *
	 * @access public
	 * @param string $account アカウント
	 * @return Tuple プロフィール
	 */
	public function getProfile ($account) {
		$key = Tuple::create([
			$account,
			__CLASS__,
			__FUNCTION__,
		])->join(':');
		$date = Date::create();
		$date['minute'] = '-' . BS_SERVICE_TWITTER_MINUTES;
		if (!$profile = $this->getSerializeHandler()->getAttribute($key, $date)) {
			$url = $this->createRequestURL('/1.1/users/show.json');
			$url->setParameter('screen_name', $account);
			$response = $this->sendGET($url->getFullPath());
			$json = new JSONRenderer;
			$json->setContents($response->getRenderer()->getContents());
			$profile = $json->getResult();
			$this->getSerializeHandler()->setAttribute($key, $profile);
		}
		return $profile;
	}

	/**
	 * GETリクエスト
	 *
	 * @access public
	 * @param string $path パス
	 * @param ParameterHolder $params パラメータの配列
	 * @return HTTPResponse レスポンス
	 */
	public function sendGET ($path = '/', ParameterHolder $params = null) {
		$this->setAttribute('httpget', true);
		$request = $this->createRequest();
		$request->setHeader('Authorization', 'Bearer ' . $this->getBearerToken());
		$request->setMethod('GET');
		$request->setURL($this->createRequestURL($path));
		if ($params) {
			$request->getURL()->setParameters($params);
		}
		return $this->send($request);
	}

	/**
	 * POSTリクエスト
	 *
	 * @access public
	 * @param string $path パス
	 * @param Renderer $renderer レンダラー
	 * @param File $file 添付ファイル
	 * @return HTTPResponse レスポンス
	 * @todo ファイル添付が未実装。
	 */
	public function sendPOST ($path = '/', Renderer $renderer = null, File $file = null) {
		$request = $this->createRequest();
		if (!($renderer instanceof WWWFormRenderer)) {
			throw new TwitterException('WWWFormRendererではありません。');
		}
		$oauth = $this->createOAuth($path, $renderer);
		$oauth->setSeparator(', ');
		$request = $this->createRequest();
		$request->setHeader('Authorization', 'OAuth ' . $oauth->getContents());
		$request->setMethod('POST');
		$request->setURL($this->createRequestURL($path));
		$request->setRenderer($renderer);
		$this->setAttribute('post', true);
		$this->setAttribute('postfields', $request->getRenderer()->getContents());
		return $this->send($request);
	}

	protected function getSerializeHandler () {
		if (!$this->serializeHandler) {
			$this->serializeHandler = new SerializeHandler;
		}
		return $this->serializeHandler;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Twitterサービス "%s"', $this->getName());
	}

	/**
	 * ツイートのURLを返す
	 *
	 * @access public
	 * @param string $id ツイートID
	 * @param string $account アカウント名
	 * @param UserAgent $useragent 対象ブラウザ
	 * @return HTTPURL URL
	 * @static
	 */
	static public function createTweetURL ($id, $account, UserAgent $useragent = null) {
		$url = URL::create();
		$url['scheme'] = 'https';
		if (!$useragent) {
			$useragent = $this->request->getUserAgent();
		}
		if ($useragent->isMobile()) {
			$url['host'] = 'mobile.twitter.com';
		} else {
			$url['host'] = 'twitter.com';
		}
		$url['path'] = '/' . $account . '/status/' . $id;
		return $url;
	}

	/**
	 * ツイートのURLをまとめて返す
	 *
	 * @access public
	 * @param string $id ツイートID
	 * @param string $account アカウント名
	 * @param string $prefix 要素名のプリフィックス
	 * @return Tuple URL文字列の配列
	 * @static
	 */
	static public function createTweetURLs ($id, $account, $prefix = 'url') {
		$urls = Tuple::create();
		$useragents = Tuple::create([
			null => UserAgent::create(UserAgent::DEFAULT_NAME),
			'_mobile' => UserAgent::create(DocomoUserAgent::DEFAULT_NAME),
		]);
		foreach ($useragents as $suffix => $useragent) {
			$url = self::createTweetURL($id, $account, $useragent);
			$urls[$prefix . $suffix] = $url->getContents();
		}
		return $urls;
	}
}

