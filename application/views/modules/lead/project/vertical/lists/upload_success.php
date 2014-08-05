<html>
<head>
	<title>Upload Success</title>
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo static_files_url(); ?>/js/jquery.colorbox.js" type="text/javascript"></script>
	<script>
	parent.$.colorbox.close();
	</script>
</head>
<body>
	<?php echo $arr; ?>
	<h3>Your file was successfully uploaded!</h3>
</body>
</html>