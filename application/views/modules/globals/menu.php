<?php
//$active_menu
?>
<ul>
<li><a <?php if($active_menu=='USER') {?> style="color:#0099FF;" <?php } ?> href="<?php echo current_base_url();?>lead_superadmin/superadmin_choice/USER">User</a></li>
<li><a <?php if($active_menu=='PERMISSION') {?> style="color:#0099FF;" <?php } ?> href="<?php echo current_base_url();?>lead_superadmin/superadmin_choice/PERMISSION" >Permission</a></li>
<li><a <?php if($active_menu=='ROLE') {?> style="color:#0099FF;" <?php } ?> href="<?php echo current_base_url();?>lead_superadmin/superadmin_choice/ROLE">Role</a></li>
<li><a <?php if($active_menu=='DEPARTMENT') {?> style="color:#0099FF;" <?php } ?> href="<?php echo current_base_url();?>lead_superadmin/superadmin_choice/DEPARTMENT">Department</a></li>
<li><a <?php if($active_menu=='VERTICAL') {?> style="color:#0099FF;" <?php } ?> href="<?php echo current_base_url();?>lead_superadmin/verticals/VERTICAL">Verticals</a></li>
</ul>
