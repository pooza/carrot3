<?php
namespace Carrot3;

/**
 * レンダーマネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface RenderStorage {
	public function getCache (Action $action):?Tuple;
	public function removeCache (Action $action);
	public function cache (HTTPResponse $view);
	public function hasCache (Action $action):bool;
	public function clear ();
}
