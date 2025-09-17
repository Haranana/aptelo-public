<?php

class etykiety extends label{

  function template($x, $y, $dataPrint) {

    $x += $this->labelMargin;
    $y += $this->labelMargin;

    $aff_border = 0;
    $ref_font = max($this->labelWidth, $this->labelHeight);
    $des_font = 0.3* min($this->labelWidth, $this->labelHeight);

    $szerokosc = $this->labelWidth - ( $this->labelMargin * 2 );

    $this->setX($x);
    $this->setY($y, false);

    $this->SetFont("dejavusans", "B", $des_font);
    $this->setX($x);
    $this->Cell($szerokosc, 0, $dataPrint['wiersz1'], $aff_border, 1, 'L', 0, '', 1);
    $this->SetFont("dejavusans", "", $des_font);
    $this->setX($x);
    $this->Cell($szerokosc, 0,$dataPrint['wiersz2'],$aff_border,1,'L', 0, '', 1);
    $this->setX($x);
    $this->Cell($szerokosc, 0,$dataPrint['wiersz3'],$aff_border,1,'L', 0, '', 1);
    $this->setX($x);
    $this->Cell($szerokosc, 0,$dataPrint['wiersz4'],$aff_border,1,'L', 0, '', 1);
    $this->setX($x);
    $this->Cell($szerokosc, 0,$dataPrint['wiersz5'],$aff_border,1,'L',0);

  }

}

?>