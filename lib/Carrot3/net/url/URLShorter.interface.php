<?php
namespace Carrot3;

interface URLShorter {
	public function getShortURL (HTTPRedirector $url);
}
