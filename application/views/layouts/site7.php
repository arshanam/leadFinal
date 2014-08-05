<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="<?php echo static_files_url(); ?>js/sorttable.js" type="text/javascript"></script>
<script src="<?php echo static_files_url(); ?>js/jquery.simplePagination.js" type="text/javascript"></script>
<script src="<?php echo static_files_url(); ?>js/jquery.js" type="text/javascript"></script>
<script src="<?php echo static_files_url(); ?>js/jquery.validate.js" type="text/javascript"></script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>ELI LMS</title>
<link href="<?php echo static_files_url(); ?>css/style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo static_files_url(); ?>css/simplePagination.css" rel="stylesheet" type="text/css" />
<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>

</head>
<body>
<div class="container">
<header>
<?php echo get_view('modules/globals/logout'); ?>
</header>
<nav>
<?php echo get_view('modules/globals/intern_menu'); ?>
</nav>

<section>
<?php echo $body; ?>
</section>

<footer>Copyright © 2014. All Rights Reserved. EliResearch </footer>

</div>


</body>
</html>
