<script type="text/javascript">
		<!--
		function login() {
			$("#loginErrors").hide();
			var valid = false;
			var username = $("#txtUsername").val();
			var password = $("#txtPassword").val();
			if ( (username.length<6) || (password.length<6) ) {
				$("input#txtUsername").focus();
				$("#loginErrors").html("<p> <strong>Error:</strong> Usuario y contraseña deben contener al menos 6 carácteres</p>");
				$("#loginErrors").show();
				return false;
			}

			var pass = $().crypt({method:"md5",source:password});
			var dataString = 'username='+ username + '&password=' + pass;
			$.ajax({
				type: "POST",
	    		url: "<?php print BASE_URL?>index.php/login",
	    		data: dataString,
	    		dataType: "xml",
	    		success: function(xml) {
		    		$(xml).find("reply").each(function() {
	    				var value=$(this).find("value").text();
	    				if (value!==undefined) {
							value = jQuery.trim(value);
	    				}
	    				var message=$(this).find("message").text();
	    				if (message!==undefined) {
	    					message = jQuery.trim(message);
	    				}

	    				if (value!=200) {
	    					$("#loginErrors").html(message);
	    					$("#loginErrors").show();
	    				}
	    				else {
		    				$("#loginBox").html("");
		    				window.location.reload();
	    				}
		    		});
	    		},
	    		error: function() {
	    			$("#loginErrors").html("<p> <strong>Error:</strong> Ha ocurrido un error inesperado. Inténtelo de nuevo más tarde</p>");
					$("#loginErrors").show();
	    		}
	  		});
	  		return false;
		}
		//-->
		</script>
		<form action="" method="post" id="loginBox">
			<fieldset>
				<legend></legend>
				<ol>
					<li id="loginErrors"> </li>
					<li>
						<label for="txtUsername">Usuario:</label>
						<input class="input" type="text" title="Introduzca su usuario" maxlength=""	 size="" name="txtUsername" id="txtUsername" value="" />
					</li>

					<li>
						<label for="txtPassword">Contraseña:</label>
						<input class="input" type="password" title="Introduzca su contraseña" maxlength="" size="" name="txtPassword" id="txtPassword" value="" />
					</li>

					<li>
						<input type="button" name="enviar" class="loginBoxButton" value="Enviar" onclick="return login();" />
					</li>
				</ol>
			</fieldset>
		</form>