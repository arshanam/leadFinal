<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class quizpdf_lib
{
   function quizpdf_lib()
   {
	   require_once(BASEPATH.'libraries/pdf/fpdf'.EXT);
	   require_once(BASEPATH.'libraries/pdf/fpdi'.EXT);
   }
}
?>  