<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ExcelExporterTest extends Test {
	public function execute () {
		$this->assert('__construct', $exporter = new ExcelExporter);
		$exporter->addRecord(Tuple::create([
			'name' => 'pooza',
			'point' => 100,
		]));
		$exporter->addRecord(Tuple::create([
			'name' => 'ビーショック',
			'point' => 900,
		]));
		$this->assert('getType', $exporter->getType() == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$this->assert('getContents', !StringUtils::isBlank($exporter->getContents()));
		$exporter->getFile()->delete();
	}
}
