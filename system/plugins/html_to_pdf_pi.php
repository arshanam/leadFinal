<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Creates a PDF from valid HTML input
 * uses the dompdf library (http://code.google.com/p/dompdf/)
 *
 * @param string $html Valid HTML input
 * @param string $filename filename, if $stream is FALSE
 * @param bool $stream streams input to the browser
 */
function pdf_create($html, $filename, $stream=TRUE) {
    require_once("dompdf/dompdf_config.inc.php");
    spl_autoload_register('DOMPDF_autoload');

    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->render();
    if ($stream) {
        $dompdf->stream($filename . ".pdf");
    } else {
        $CI = & get_instance();
        $CI->load->helper('file');
        write_file($filename . ".pdf", $dompdf->output());
    }
}

/* End of file html_to_pdf.php */
/* Location: ./system/plugins/html_to_pdf.php */