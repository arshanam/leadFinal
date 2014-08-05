

<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


function create_pdf($html_data, $file_name = "", $code ='') {
	
		
        require 'MPDF57/mpdf.php';
        $mypdf = new mPDF();
		
		$mypdf->allow_charset_conversion = false;
		$mypdf->ignore_invalid_utf8 = true;
		$mypdf->setAutoTopMargin = true;	
		$mypdf->setAutoBottomMargin = true;	
		$mypdf->SetHTMLHeader('<img src="/var/www/html/newsupercoder/webroot/images/sc-logo-small.jpg"/>','BLANK');
		$footertext=str_replace("-"," ",$file_name)." Details";
		
		$mypdf->SetHTMLFooter('
<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 8pt; color: #000000; font-weight: bold; font-style: italic;"><tr>
<td width="50%"><span style="font-size: 11px; font-style: italic;">'.$footertext.'</span></td>

<td width="50%" style="text-align: right; ">CPT © 2013 American Medical Association. All rights reserved.</td>
</tr></table>
','BLANK');
		$stylesheet = file_get_contents('/var/www/html/newsupercoder/webroot/css/mpdf.create.css');
		$mypdf->WriteHTML($stylesheet,1); 
		$mypdf->WriteHTML($html_data,2);
		
		
		$mypdf->Output($file_name . '.pdf', 'D');
		
		}

	function lcdcustomprint_pdf($html_data, $file_name = "", $footertext ='') {
	
		
        require 'MPDF57/mpdf.php';
        $mypdf = new mPDF();
		
		$mypdf->allow_charset_conversion = false;
		$mypdf->ignore_invalid_utf8 = true;
		$mypdf->setAutoTopMargin = true;	
		$mypdf->setAutoBottomMargin = true;	
		$mypdf->SetHTMLHeader('<img src="/var/www/html/newsupercoder/webroot/images/sc-logo-small.jpg"/>','BLANK');
		 $str="<div class='footertext'><span class='wraptext ftleft'>".$footertext."</span>";
		
		//$mypdf->SetHTMLFooter($str."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='wraptext ftright'>                  CPT © 2013 American Medical Association. All rights reserved.</span></div>",'BLANK');
		
		$mypdf->SetHTMLFooter('
<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 8pt; color: #000000; font-weight: bold; font-style: italic;"><tr>
<td width="50%"><span style="font-size: 11px; font-style: italic;">'.$footertext.'</span></td>

<td width="50%" style="text-align: right; ">CPT © 2013 American Medical Association. All rights reserved.</td>
</tr></table>
','BLANK');
		
		$stylesheet = file_get_contents('/var/www/html/newsupercoder/webroot/css/mpdf.create.css');
		$mypdf->WriteHTML($stylesheet,1); 
		$mypdf->WriteHTML($html_data,2);
		
		
		$mypdf->Output($file_name . '.pdf', 'D');
		
		}

	function guideline_pdf($html_data, $file_name = "", $footertext ='') {
	
		
        require 'MPDF57/mpdf.php';
        $mypdf = new mPDF();
		
		$mypdf->allow_charset_conversion = false;
		$mypdf->ignore_invalid_utf8 = true;
		$mypdf->setAutoTopMargin = true;	
		$mypdf->setAutoBottomMargin = true;	
		$mypdf->SetHTMLHeader('<img src="/var/www/html/uataapc/webroot/audit/images/logo.jpg"/>','BLANK');
		 $str="<div class='footertext'><span class='wraptext ftleft'>".$footertext."</span>";
		
		//$mypdf->SetHTMLFooter($str."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='wraptext ftright'>                  CPT © 2013 American Medical Association. All rights reserved.</span></div>",'BLANK');
		
		$mypdf->SetHTMLFooter('
<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 8pt; color: #000000; font-weight: bold; font-style: italic;"><tr>
<td width="50%"><span style="font-size: 11px; font-style: italic;">'.$footertext.'</span></td>


</tr></table>
','BLANK');
		
		$stylesheet = file_get_contents('/var/www/html/uataapc/webroot/audit/css/style.css');
		$mypdf->WriteHTML($stylesheet,1); 
		$mypdf->WriteHTML($html_data,2);
		
		
		$mypdf->Output($file_name . '.pdf', 'D');
		
		}
	

?>