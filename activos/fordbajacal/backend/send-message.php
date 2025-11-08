<?php
include "../lib/functions.php";
date_default_timezone_set('America/Mexico_City');

if(isset($_GET['email']) && isset($_GET['name']) && isset($_GET['telphone'] ) ){

	$to = getEmails('../json/ford.json');
	$from = $_GET['email'];
	$name = $_GET['name'];
	$phone = $_GET['telphone'];

	$subject = "Cliente prospecto proveniente del Ford Ebook";

	$html = "<html>
						<body style='margin:0; padding:0;'>
						<h1>Ford - Ebook </h1>
						<h2>".$subject."</h2>
								<strong>Fecha de contacto:</strong> ". date("d/m/Y H:i") ."<br>
								<strong>Nombre:</strong> " . $name . "<br>
								<strong>Telefono:</strong> " . $phone . "<br>
								<strong>E-mail:</strong> " . $from . "<br>";
	$html.= "</body></html>";

	$headers  = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
	$headers .= "From:" . $from . "\r\n";
	$headers .= "X-Mailer: PHP/" . phpversion()."\r\n";

	if (mail($to, $subject, $html, $headers)){
		header('Location: /contacto-exitoso.php');
		echo '<script>window.location = "/contacto-exitoso.php" </script>';
	}else{
		header('Location: /contacto-fallido.php');
		echo '<script>window.location = "/contacto-fallido.php" </script>';
	};

}else{
	
	header('Location: /index.php');
	echo '<script>window.location = "/index.php" </script>';
}
?>