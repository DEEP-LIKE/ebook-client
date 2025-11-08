<?php
  include "lib/functions.php";
  
?>

<!DOCTYPE html>
<html lang="es">
<?php $title_site = get_title(); ?>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="shortcut icon" type="image/x-icon" href="/images/favicon.ico">
  <title><?php echo $title_site->title; ?></title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
  <link rel="stylesheet" href="style/fonts.css">
  <link rel="stylesheet" href="style/style.css">
</head>
<body data-spy="scroll" data-target="#menu-cars" data-offset="0">
  <div class="container">
    <nav class="navbar navbar-light ">
      <a class="navbar-brand d-flex flex-row align-items-center" href="/">
        <img src="images/logo.png"  class="d-inline-block align-top" alt="">
        <span class="name-distribuitor"><?php echo $title_site->title; ?></span>
      </a>
    </nav>
  </div>

  <div class="scroll-up" id="button-up">
  </div>

  <?php $asesor = get_asesor(); ?>

  <?php if ((bool)$asesor -> avalible): ?>
    <section class="asesor">
      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-7 col-md-7">
            <div class="d-block d-sm-none image-mobile">
              <img src="images/personas/<?php echo $asesor->picture; ?>" alt="<?php echo htmlspecialchars($asesor->name, ENT_QUOTES, 'UTF-8'); ?>"   class="img-fluid">
            </div>
            <div class="main">
              <div class="im">soy</div>
              <div class="name"><?php echo htmlspecialchars($asesor->name, ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="your">Tu asesor Ford</div>
              <div class="description">
                <p>Contáctame, estoy para apoyarte en esta emocionante decisión. </p>
                <a  id="<?php echo $title_site->id; ?>-telphone1" href="tel:<?php echo $asesor->tel; ?>" target="_blank" class="btn btn-outline-primary"><i class="fas fa-phone"></i></a>
                <a id="<?php echo $title_site->id; ?>-whatsapp1" href="https://wa.me/+52<?php echo $asesor->whats; ?>?text=<?php echo $asesor->textw ?>" target="_blank" class="btn btn-success green"><i class="fab fa-whatsapp"></i></a>
              </div>
            </div>
          </div>
          <div class="d-none d-sm-block col-sm-5 col-md-5">
            <img src="images/personas/<?php echo $asesor->picture; ?>" alt="<?php echo htmlspecialchars($asesor->name, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid">
          </div>
        </div>
      </div>
    </section>
  <?php endif ?>

  <?php $header = get_header(); ?>
  <section class="hero" style="background-image: url(images/background/<?php echo $header->imagen; ?>);">
    <div class="container">
      <div class="row">
        <div class="col-12 col-sm-12 col-md-12 bg-danger p-5">
          <?php if ((bool)$header): ?>
            <h2 class="hero-title"><?php echo htmlspecialchars($header->fallido, ENT_QUOTES, 'UTF-8'); ?></h2>
          <?php endif ?>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <a href="https://www.ford.mx/">Términos y condiciones en ford.mx</a>
  </footer>



  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script src="js/main.js"></script>
  <script src="js/scroll.js"></script>
</body>
</html>