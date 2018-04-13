<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent
 */

namespace Carrot3;

/**
 * ユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class UserAgent extends ParameterHolder {
	use BasicObject;
	protected $supports;
	protected $type;
	protected $digest;
	const ACCESSOR = 'ua';
	const DEFAULT_NAME = 'Mozilla/5.0';

	/**
	 * @access protected
	 * @param string $name ユーザーエージェント名
	 */
	protected function __construct (?string $name = null) {
		$this->supports = Tuple::create();
		$this['name'] = $name;
		$this['type'] = $this->getType();
		$this['is_mobile'] = $this->isMobile();
		$this['is_smartphone'] = $this->isSmartPhone();
		$this['is_tablet'] = $this->isTablet();
		$this['is_legacy'] = $this->isLegacy();
		$this['display'] = $this->getDisplayInfo();

		$classes = [Utils::getClass($this)] + Utils::getParentClasses($this);
		foreach ($classes as $class) {
			if (mb_ereg('^\\\\([[:alnum:]]+)UserAgent$', $class, $matches)) {
				$this['is_' . StringUtils::underscorize($matches[1])] = true;
			}
		}
	}

	/**
	 * インスタンスを生成して返す
	 *
	 * @access public
	 * @param string $name UserAgent名
	 * @param string $type タイプ名
	 * @return UserAgent インスタンス
	 * @static
	 */
	static public function create (?string $name, $type = null) {
		if (!$type) {
			$type = self::getDefaultType($name);
		}
		$class = Loader::getInstance()->getClass($type . 'UserAgent');
		return new $class($name);
	}

	/**
	 * 規定タイプ名を返す
	 *
	 * @access public
	 * @param string $name UserAgent名
	 * @return string タイプ名
	 * @static
	 */
	static public function getDefaultType (string $name) {
		foreach (self::getTypes() as $type) {
			$class = Loader::getInstance()->getClass($type . 'UserAgent');
			$instance = new $class;
			if (mb_ereg($instance->getPattern(), $name)) {
				return $type;
			}
		}
		return 'Default';
	}

	/**
	 * レガシー環境/旧機種か？
	 *
	 * @access public
	 * @return bool レガシーならばTrue
	 */
	public function isLegacy ():bool {
		return false;
	}

	/**
	 * レガシー環境/旧機種か？
	 *
	 * isLecagyのエイリアス
	 *
	 * @access public
	 * @return bool レガシーならばTrue
	 * @final
	 */
	final public function isDenied ():bool {
		return $this->isLegacy();
	}

	/**
	 * ビューを初期化
	 *
	 * @access public
	 * @param SmartyView 対象ビュー
	 * @return bool 成功時にTrue
	 */
	public function initializeView (SmartyView $view) {
		$view->getRenderer()->setUserAgent($this);
		$view->addModifier('sanitize');
		$view->addOutputFilter('trim');
		$view->addOutputFilter('strip_comment');
		$view->setAttributes($this->request->getAttributes());
		$view->setAttribute('module', $view->getModule());
		$view->setAttribute('action', $view->getAction());
		$view->setAttribute('errors', $this->request->getErrors());
		$view->setAttribute('params', $this->request->getParameters());
		$view->setAttribute('real_useragent', $this->request->getRealUserAgent());
		$view->setAttribute('credentials', $this->user->getCredentials());
		$view->setAttribute('client_host', $this->request->getHost());
		$view->setAttribute('server_host', $this->controller->getHost());
		$view->setAttribute('is_tls', $this->request->isTLS());
		$view->setAttribute('session',$this->request->getSession());
		return true;
	}

	/**
	 * セッションハンドラを生成して返す
	 *
	 * @access public
	 * @return SessionHandler
	 */
	public function createSession () {
		return new SessionHandler;
	}

	/**
	 * クエリーパラメータを返す
	 *
	 * @access public
	 * @return WWWFormRenderer
	 */
	public function getQuery () {
		$query = new WWWFormRenderer;
		if (BS_DEBUG || $this->user->isAdministrator()) {
			$query[self::ACCESSOR] = $this->request[self::ACCESSOR];
		}
		return $query;
	}

	/**
	 * ユーザーエージェント名を返す
	 *
	 * @access public
	 * @return string ユーザーエージェント名
	 */
	public function getName () {
		return $this['name'];
	}

	/**
	 * ユーザーエージェント名を設定
	 *
	 * @access public
	 * @param string $name ユーザーエージェント名
	 */
	public function setName (?string $name) {
		$this['name'] = $name;
	}

	/**
	 * サポートされているか？
	 *
	 * @access public
	 * @param string $name サポート名
	 * @return bool サポートがあるならTrue
	 */
	public function hasSupport (string $name):bool {
		return !!$this->supports[$name];
	}

	/**
	 * 再生可能か？
	 *
	 * @access public
	 * @param MediaFile $file
	 * @return bool 再生できるならTrue
	 */
	public function isPlayable (MediaFile $file):bool {
		$types = Tuple::create([
			'audio/mpeg',
			'audio/aac',
			'audio/mp4',
			'video/mp4',
		]);
		return (!$this->isLegacy() && $types->isContain($file->analyzeType()));
	}

	/**
	 * ケータイ環境か？
	 *
	 * @access public
	 * @return bool ケータイ環境ならTrue
	 */
	public function isMobile ():bool {
		return false;
	}

	/**
	 * スマートフォンか？
	 *
	 * @access public
	 * @return bool スマートフォンならTrue
	 */
	public function isSmartPhone ():bool {
		return false;
	}

	/**
	 * タブレット型か？
	 *
	 * @access public
	 * @return bool タブレット型ならTrue
	 */
	public function isTablet ():bool {
		return false;
	}

	/**
	 * バージョンを返す
	 *
	 * @access public
	 * @return string バージョン
	 */
	public function getVersion () {
		return $this['version'];
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest () {
		if (!$this->digest) {
			$this->digest = Crypt::digest([
				__CLASS__,
				(int)$this->hasSupport('html5_video'),
				(int)$this->hasSupport('html5_audio'),
				(int)$this->hasSupport('touch'),
				(int)$this->isMobile(),
				(int)$this->isSmartPhone(),
				(int)$this->isTablet(),
			]);
		}
		return $this->digest;
	}

	/**
	 * ダウンロード用にエンコードされたファイル名を返す
	 *
	 * @access public
	 * @param string $name ファイル名
	 * @return string エンコード済みファイル名
	 */
	public function encodeFileName (string $name) {
		$name = MIMEUtils::encode($name);
		return addslashes($name);
	}

	/**
	 * 画像マネージャを生成して返す
	 *
	 * @access public
	 * @param int $flags フラグのビット列
	 * @return ImageManager 画像マネージャ
	 */
	public function createImageManager (int $flags = 0) {
		$images = new ImageManager($flags);
		$images->setUserAgent($this);
		return $images;
	}

	/**
	 * 画面情報を返す
	 *
	 * @access public
	 * @return Tuple 画面情報
	 */
	public function getDisplayInfo () {
		return Tuple::create();
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 * @abstract
	 */
	abstract public function getPattern ();

	/**
	 * タイプを返す
	 *
	 * @access public
	 * @return string タイプ
	 */
	public function getType () {
		if (!$this->type) {
			mb_ereg('\\\\([[:alnum:]]+)UserAgent$', Utils::getClass($this), $matches);
			$this->type = $matches[1];
		}
		return $this->type;
	}

	/**
	 * 規定の画像形式を返す
	 *
	 * @access public
	 * @return string 規定の画像形式
	 */
	public function getDefaultImageType () {
		return BS_IMAGE_THUMBNAIL_TYPE;
	}

	/**
	 * 規定のエンコードを返す
	 *
	 * @access public
	 * @return string 規定のエンコード
	 */
	public function getDefaultEncoding () {
		return 'utf8';
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		$values = Tuple::create($this);
		$values['supports'] = $this->supports;
		return $values;
	}

	static private function getTypes () {
		return Tuple::create(
			ConfigManager::getInstance()->compile('useragent')['classes']
		);
	}
}
