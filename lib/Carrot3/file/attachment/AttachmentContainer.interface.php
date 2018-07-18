<?php
namespace Carrot3;

interface AttachmentContainer {
	public function getAttachment (string $name):?File;
	public function getAttachmentFileName (string $name);
}
