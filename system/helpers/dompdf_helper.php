<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
function pdf_create_order_receipt($html, $filename='', $stream=TRUE, $orientation='', $code='')
{
    require_once("dompdf/dompdf_config.inc.php");
    require_once("dompdf/include/dompdf.cls.php");
	
    
    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
	if(isset($orientation) && $orientation!='')
	{
		$dompdf->set_paper('a4', $orientation);
	}
    $dompdf->render();
    if ($stream) {
        $dompdf->stream($filename.".pdf");
    } else {
        return $dompdf->output();
    }
}
function pdf_create($html, $filename='', $stream=TRUE, $orientation='', $code='')
{
    require_once("dompdf/dompdf_config.inc.php");
    require_once("dompdf/include/dompdf.cls.php");
	require_once("dompdf/include/canvas.cls.php");
	require_once("dompdf/include/cpdf_adapter.cls.php");
	require_once("dompdf/include/canvas_factory.cls.php");
	require_once("dompdf/include/font_metrics.cls.php");
	 
    $dompdf = new DOMPDF();
	
	$dompdf->load_html($html);
	$dompdf->render();
    
    if(isset($orientation) && $orientation!='')
	{
		$dompdf->set_paper('a4', $orientation);
	}
	
	$canvas = $dompdf->get_canvas();
	$font = Font_Metrics::get_font("Arial", "normal");
	$canvas->page_text(72, 18, $code , $font, 14, array(0,0,0));
	
	$canvas->page_text(180, 740, "© Copyright 2013-2014 www.supercoder.com" , $font, 10, array(0,0,0));
	$canvas->page_text(150, 760, "CPT © 2013 American Medical Association. All rights reserved." , $font, 10, array(0,0,0));
	
	
    if ($stream) {
        $dompdf->stream($filename.".pdf");
    } else {
        return $dompdf->output();
    }
}
function pdf_create_srubber($html, $filename='', $stream=TRUE, $orientation='') 
{
    require_once("dompdf/dompdf_config.inc.php");
    require_once("dompdf/include/dompdf.cls.php");
	
    
    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
	if(isset($orientation) && $orientation!='')
	{
		$dompdf->set_paper('a4', $orientation);
	}
    $dompdf->render();
    if ($stream) {
        $dompdf->stream($filename.".pdf");
    } else {
        return $dompdf->output();
    }
}
?>