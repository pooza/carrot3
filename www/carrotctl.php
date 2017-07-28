<?php
/**
 * Webアプリケーション用 エントリーポイント
 *
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

define('BS_WWW_DIR', dirname(__FILE__));
define('BS_ROOT_DIR', dirname(dirname(__FILE__)));
require_once(BS_ROOT_DIR . '/lib/Carrot3/carrot.php');
