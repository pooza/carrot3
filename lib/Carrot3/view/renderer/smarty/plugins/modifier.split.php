<?php
use \Carrot3 as C;

function smarty_modifier_split ($value, $delimiter, $replaceTo = "\n") {
	return str_replace($delimiter, $replaceTo, $value);
}
