<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent.mobile
 */

namespace Carrot3;

/**
 * モバイルユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class MobileUserAgent extends UserAgent {
	private $carrier;
	const DEFAULT_NAME = 'DoCoMo/2.0 (c500;)';

	/**
	 * ビューを初期化
	 *
	 * @access public
	 * @param SmartyView 対象ビュー
	 * @return boolean 成功時にTrue
	 */
	public function initializeView (SmartyView $view) {
		parent::initializeView($view);
		$view->getRenderer()->addModifier('pictogram');
		$view->getRenderer()->addOutputFilter('mobile');
		$view->getRenderer()->addOutputFilter('encoding');
		return true;
	}

	/**
	 * セッションハンドラを生成して返す
	 *
	 * @access public
	 * @return SessionHandler
	 */
	public function createSession () {
		if (!!$this->hasSupport('cookie')) {
			return new SessionHandler;
		} else {
			return new MobileSessionHandler;
		}
	}

	/**
	 * クエリーパラメータを返す
	 *
	 * @access public
	 * @return WWWFormRenderer
	 */
	public function getQuery () {
		$query = parent::getQuery();
		if (!$this->hasSupport('cookie')) {
			$query[$this->request->getSession()->getName()] = $session->getID();
		}
		return $query;
	}

	/**
	 * ケータイ環境か？
	 *
	 * @access public
	 * @return boolean ケータイ環境ならTrue
	 */
	public function isMobile () {
		return true;
	}

	/**
	 * キャリア名を返す
	 *
	 * @access public
	 * @return string キャリア名
	 */
	public function getCarrier () {
		if (mb_ereg('^\\\\([[:alnum:]]+)UserAgent$', Utils::getShortClass($this), $matches)) {
			return $matches[1];
		}
	}

	/**
	 * 規定の画像形式を返す
	 *
	 * @access public
	 * @return string 規定の画像形式
	 */
	public function getDefaultImageType () {
		return 'image/png';
	}

	/**
	 * 規定のエンコードを返す
	 *
	 * @access public
	 * @return string 規定のエンコード
	 */
	public function getDefaultEncoding () {
		return 'sjis-win';
	}

	/**
	 * 画面情報を返す
	 *
	 * @access public
	 * @return Tuple 画面情報
	 */
	public function getDisplayInfo () {
		return Tuple::create([
			'width' => BS_IMAGE_MOBILE_SIZE_QVGA_WIDTH,
			'height' => BS_IMAGE_MOBILE_SIZE_QVGA_HEIGHT,
		]);
	}

	/**
	 * ムービー表示用のHTML要素を返す
	 *
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 * @param UserAgent $useragent 対象ブラウザ
	 * @return DivisionElement 要素
	 */
	public function createMovieElement (ParameterHolder $params) {
		$container = new DivisionElement;
		$anchor = $container->addElement(new AnchorElement);
		$anchor->setURL($params['url']);
		$anchor->setBody($params['label']);
		return $container;
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
				Utils::getClass($this),
				$this->getDisplayInfo()['width'],
				(int)$this->hasSupport('cookie'),
			]);
		}
		return $this->digest;
	}
}

