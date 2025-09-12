<?php
require './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('./');
$dotenv->load();

require_once(dirname(__FILE__).'/functions.php');
$inputFileName = 'zip_file';

if(isset($_FILES[$inputFileName]["name"])) {
  header('Content-Type: application/json');
  $functions = new functions();
  $process = $functions->uploadSites();
  echo json_encode($process);
  die();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Ford ebook backend</title>
  <link rel="stylesheet" type="text/css" href="progress_style.css">
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js"></script>
  <script type="text/javascript" src="upload_progress.js"></script>
</head>

<body>
<form enctype="multipart/form-data" method="post" action="#" id="loadFileForm">
  <label>Elige el archivo a subir: <input type="file" name="<?php echo $inputFileName ?>" /></label>
  <br />
  <input type="submit" name="submit" value="Upload" onclick='upload_image();' />
</form>
<div class='progress' id="progress_div">
<div class='bar' id='bar'></div>
<div class='percent' id='percent'>0%</div>
</div>
<div id='results'></div>
</body>
</html>
