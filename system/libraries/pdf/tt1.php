<?php
require_once('fpdf.php');
require_once('fpdi.php');

// initiate FPDI
$pdf =& new FPDI();
// add a page
$pdf->AddPage();
// set the sourcefile
$pdf->setSourceFile('certificate.pdf');
// import page 1
$tplIdx = $pdf->importPage(1);
// use the imported page and place it at point 10,10 with a width of 100 mm
$pdf->useTemplate($tplIdx, 10, 10, 100);

// now write some text above the imported page
$pdf->SetFont('Arial');
$pdf->SetTextColor(255,0,0);
$pdf->SetXY(45, 40);
$pdf->Write(0, "shilpi mogha");

$pdf->Output('newpdf.pdf', 'D');