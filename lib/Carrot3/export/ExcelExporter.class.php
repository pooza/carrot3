<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage export
 */

namespace Carrot3;

/**
 * Excelレンダラー
 *
 * Excel2007（xlsx）形式に対応。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @link https://phpexcel.codeplex.com/
 */
class ExcelExporter implements Exporter, Renderer {
	private $file;
	private $workbook;
	private $row = 1;
	private $freezed = false;

	/**
	 * @access public
	 */
	public function __construct () {
		require_once BS_LIB_DIR . '/PHPExcel/PHPExcel.php';
		$this->workbook = new \PHPExcel;
	}

	/**
	 * 一時ファイルを返す
	 *
	 * @access public
	 * @return File 一時ファイル
	 */
	public function getFile () {
		if (!$this->file) {
			$this->file = FileUtils::createTemporaryFile('.xlsx');
		}
		return $this->file;
	}

	/**
	 * レコードを追加
	 *
	 * @access public
	 * @param Tuple $record レコード
	 */
	public function addRecord (Tuple $record) {
		$col = 0;
		$sheet = $this->workbook->getActiveSheet();
		foreach ($record as $key => $value) {
			$cell = $sheet->getCellByColumnAndRow($col, $this->row);
			$cell->setValueExplicit($value);
			$cell->getStyle()->getAlignment()->setWrapText(true);
			$col ++;
		}
		$this->row ++;
	}

	private function save () {
		$writer = \PHPExcel_IOFactory::createWriter($this->workbook, 'Excel2007');
		$writer->save($this->getFile()->getPath());
	}

	/**
	 * タイトル行を設定
	 *
	 * @access public
	 * @param Tuple $row タイトル行
	 */
	public function setHeader (Tuple $row) {
		if (!$this->freezed) {
			$this->addRecord($row);
			$this->workbook->getActiveSheet()->freezePaneByColumnAndRow(0, 2);
			$this->freezed = true;
		}
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string CSVデータの内容
	 */
	public function getContents () {
		$this->save();
		return $this->getFile()->getContents();
	}

	/**
	 * 現在の行番号を返す
	 *
	 * @access public
	 * @return integer Excelでの行番号
	 */
	public function getRowNumber () {
		return $this->row;
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		return MIMEType::getType('xlsx');
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return integer サイズ
	 */
	public function getSize () {
		$this->save();
		return $this->getFile()->getSize();
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate () {
		$this->save();
		return true;
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError () {
		return null;
	}
}
