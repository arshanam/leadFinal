
	<script src="<?php echo static_files_url(); ?>/js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo static_files_url(); ?>/js/jquery.validate.js" type="text/javascript"></script>
  	<script>
  	$(function() {

    // Setup form validation on the #register-form element
    $("#form11").validate({

        // Specify the validation rules

        rules: {
            email: {
            	required: true
            },


            password: {
                required: true,
                minlength: 5
            }

        },

        // Specify the validation error messages
        messages: {
            email: "<br/><br/>Required<BR/>",

            password: {
                required: "<br/>Please provide a password<BR/>",
                minlength: "<br/><br/>Your password must be at least 5 characters long<BR/>"
            }
        },

        submitHandler: function(form) {
			//welcome();
            form.submit();
        }
    });

  });

  </script>



<section>
<div id="container" style="width:1000px">

	<div id="header" style="background-color:#FFA5A5;">
		<h1 style="margin-bottom:0;">Login Portal</h1>
	</div>

	<div id="content" style="background-color:#EEEEEE;height:auto;width:auto;">
		<br/><br/>
		<form name="form11" id="form11" method="POST" novalidate="false" >
			<table style="width:300px">
				<tr>
					<td>Email</td>
					<td><input type="text"  id="email"  name="email" placeholder="email" /></td>
				</tr>

				<tr>
					<td>Password</td>
					<td><input type="password" placeholder="********" id="password" name="password" /></td>
				</tr>
				<tr>
					<td><p style="color:blue;">
						<input type="checkbox" name="rememberme" id="rememberme" value="value">
						<label for="rememberme">Remember</label>
						</p>
					</td>
					<td>
						<input type="submit" value="Submit" name="fsubmit" >
					</td>
				</tr>
				<tr></tr>
			</table>
		</form>
	</div>
</div>

</section>
