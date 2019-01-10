<?php
namespace Carrot3;

interface ImageRenderer extends Renderer {
	public function getGDHandle ();

	public function getWidth ():int;

	public function getHeight ():int;
}
