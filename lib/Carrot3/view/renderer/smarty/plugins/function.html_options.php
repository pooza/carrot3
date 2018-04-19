<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * options関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_html_options ($params, &$smarty) {
    $params = C\Tuple::create($params);
    $select = new C\HTMLElement('select');
    $select->setID($params['id']);
    $select->setAttribute('name', $params['name']);
    if ($params['options']) {
        foreach ($params['options'] as $key => $value) {
            $option = $select->createElement('option');
            $option->setAttribute('value', $key);
            $option->setAttribute('label', $value);
        }
    } else if ($params['values'] && $params['output']) {
        foreach ($params['values'] as $index => $value) {
            $option = $select->createElement('option');
            $option->setAttribute('value', $value);
            $option->setAttribute('label', $params['output'][$index]);
        }
    }

    foreach ($select->getElements() as $option) {
        $option->setBody($select->getAttribute('label'));
        if ($params['selected'] == $option->getAttribute('label')) {
            $option->setAttribute('selected', 'selected');
        }
    }

    if (C\StringUtils::isBlank($select->getAttribute('name'))) {
        $options = C\Tuple::create();
        foreach ($select->getElements() as $option) {
            $options[] = $option->getContents();
        }
        return $options->join('');
    } else {
        return $select->getContents();
    }
}