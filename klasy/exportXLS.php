<?php
/*	
	Version 	: 1.01
	Author 		: Leenix
	Website		: http://www.leenix.co.uk
*/

class ExportXLS {

	private $filename;
	private $headerArray;
	private $bodyArray;
	private $rowNo = 0;

	function __construct($filename) { 
		$this->filename = $filename;
	}

	public function addHeader($header) {

		if(is_array($header)) {
			$this->headerArray[] = $header;
		}
		else
		{
			$this->headerArray[][0] = $header;
		}
	}

	public function addRow($row) {

		if(is_array($row)) {

			if(is_array($row[0])) {
				foreach($row as $key=>$array) {
					$this->bodyArray[] = $array;
				}
			}
			else
			{
				$this->bodyArray[] = $row;
			}			
		}
		else
		{
			$this->bodyArray[][0] = $row;
		}
	
	}
	
	public function returnSheet() {

		return $this->buildXLS();
	}

	public function sendFile() {

		$xls = $this->buildXLS();

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=".$this->filename);
		header("Content-Transfer-Encoding: binary");

		echo $xls;

		exit;
	}

	private function buildXLS() {

		$xls = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);

		if(is_array($this->headerArray)) {
			$xls .= $this->build($this->headerArray);
		}

		if(is_array($this->bodyArray)) {
			$xls .= $this->build($this->bodyArray);
		}

		$xls .= pack("ss", 0x0A, 0x00);

		return $xls;
	}

	private function build($array) {
    
        $build = '';

		foreach ($array as $key=>$row) {
			$colNo = 0;
			foreach ($row as $key2=>$field) {
				if (is_numeric($field)) {
					$build .= $this->numFormat($this->rowNo, $colNo, $field);
				}
				else
				{
					$build .= $this->textFormat($this->rowNo, $colNo, $field);
				}

				$colNo++;
			}
			$this->rowNo++;
		}

		return $build;
	}

	private function textFormat($row, $col, $data) {

		//$data = utf8_decode($data);
        $data = iconv("UTF-8", "ISO-8859-2", $data); 
        $data = iconv('ISO-8859-2', 'WINDOWS-1250',$data); 
		$length = strlen((string)$data);
		$field = pack("ssssss", 0x204, 8 + $length, $row, $col, 0x0, $length);
		$field .= $data;

		return $field; 
	}
		

	private function numFormat($row, $col, $data) {

    		$field = pack("sssss", 0x203, 14, $row, $col, 0x0);
    		$field .= pack("d", $data); 
		
		return $field; 
	}
}
?>