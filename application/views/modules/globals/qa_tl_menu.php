<?php
//$active_menu
?>
<ul>
<li><a <?php if($active_menu=='DASHBOARD') {?> style="color:#0099FF;" <?php } ?> href="<?php echo current_base_url();?>lead_qa_teamlead/qa_teamlead_choice/DASHBOARD">Dashboard</a></li>
<li><a <?php if($active_menu=='QA') {?> style="color:#0099FF;" <?php } ?> href="<?php echo current_base_url();?>lead_qa_teamlead/qa_teamlead_choice/QA">Quality Analyst</a></li>
</ul>
