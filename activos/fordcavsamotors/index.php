<?php
  include "lib/functions.php";
  include "lib/detectmobilebrowser.php";

  if(isMobileDevice()){
    $href = "_self";
  }else{
    $href= "_blank";
  }

?>  

<!DOCTYPE html>
<html lang="es">
<?php $title_site = get_title(); ?>
<?php $header = get_header(); ?>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <base href="/activos/<?php echo $title_site->id; ?>/">
  <link rel="shortcut icon" type="image/x-icon" href="/images/favicon.ico">
  <title><?php echo $title_site->title; ?></title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" crossorigin="anonymous">
  <link rel="stylesheet" href="style/fonts.css">
  <link rel="stylesheet" href="style/style.css"> 

  <meta property="og:title" content="<?php echo $title_site->title; ?>" />
  <meta property="og:description" content="<?php echo $header->title; ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?php echo $title_site->site_url; ?>" />
  <meta property="og:image" content="<?php echo $title_site->site_url . '/' . $title_site->opengraph; ?>" />

  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-65395108-51"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-65395108-51');
  </script>

  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-5K9GHKG');</script>
  <!-- End Google Tag Manager -->
</head>
<body data-spy="scroll" data-target="#menu-cars" data-offset="0">
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5K9GHKG"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
  <?php
    $whats = get_whats();
    $facebook = get_face();
  ?> 
  <ul class="socials">
    <li>
      <a href="<?php echo $facebook; ?>" target='<?php echo $href; ?>' rel="noopener noreferrer" id="facebook-click"><i class="fab fa-facebook-f"></i></a>
    </li>
    <li>
      <a href="<?php echo $whats; ?> " target='<?php echo $href; ?>' rel="noopener noreferrer" id="whatsapp-click"><i class="fab fa-whatsapp"></i></a>
    </li>
  </ul>

   <nav class="navbar fixed-top navbar-light bg-light">
      <div class="container">
        <a class="navbar-brand d-flex flex-row align-items-center" href="#" id="logo-click">
          <img src="images/CAVSA.png"  class="d-inline-block align-top" alt="" style="max-height: 84px;">
          <span class="name-distribuitor"><?php echo $title_site->title; ?></span>
        </a>
      </div>
  </nav>
 

  <div class="scroll-up" id="button-up">
  </div>

  <?php $asesor = get_asesor(); ?>

  <?php if (false): ?>
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
                <a  id="<?php echo $title_site->id; ?>-telphone1" href="tel:<?php echo $asesor->tel; ?>" target='<?php echo $href; ?>' class="btn btn-outline-primary"><i class="fas fa-phone"></i></a>
                <a id="<?php echo $title_site->id; ?>-whatsapp1" href="https://wa.me/+52<?php echo $asesor->whats; ?>?text=<?php echo $asesor->textw ?>" target='<?php echo $href; ?>' class="btn btn-success green"><i class="fab fa-whatsapp"></i></a>
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

  
  <section class="hero" style="background-image: url(images/background/<?php echo $header->imagen; ?>);">
    <div class="container">
      <div class="row justify-content-md-center">
        <div class="col-12 col-sm-12 col-md-12 col-lg-8  col-xl-10">
          <?php if ((bool)$header): ?>
            <h2 class="hero-title"><?php echo htmlspecialchars($header->title, ENT_QUOTES, 'UTF-8'); ?></h2>
          <?php endif ?>
        </div>
      </div>
    </div>
  </section>

  <?php $all_cars = get_summary_cars(); ?>
  <?php $all_promotions = get_summary_promotion(); ?>
  <section class="cars">
    <div class="container">
      <div class="row">
        <div class="col-12 col-sm-12 col-md-12">
          <h1 class="cars-title">Conoce las promociones en versiones 2025</h1>
        </div>
        <div class="col-12 col-12 col-sm-12 col-md-12 col-lg-4">
          <div class="sticky-top">
            <nav id="menu-cars" class="navbar">
              <ul class="nav nav-pills">
                <?php foreach ($all_cars as $car): ?>
                  <li class="nav-item d-block ">
                    <a class="nav-link" href="#<?php echo $car->id; ?>"  id="menu-<?php echo $car->id; ?>"><?php echo $car->name; ?></a>
                  </li>
                <?php endforeach ?>
              </ul>
            </nav>
          </div>
        </div>
        <div class="col-12 col-sm-12 col-md-12 col-lg-8">
          <div class="main-content-cars">

            <!-- Inicio de loop promociones -->
              <?php foreach ($all_promotions as $promotion): ?>
                <h4 id="<?php echo $promotion->id; ?>" class="title-car"><?php echo $promotion->name; ?></h4>
                <div class="car-content">
                  <div class="promo-car-image" style="background-image: url(images/cars/<?php echo $promotion->image; ?>);">
                  </div>
                </div>
              <?php endforeach ?>
            <!-- Fin del contenedor de promociones -->


            
            <!-- Inicio de loop contenedor de carros -->
            <?php foreach ($all_cars as $car): ?>
              <h4 id="<?php echo $car->id; ?>" class="title-car"><?php echo $car->name; ?></h4>
              <div class="car-content">
                <div class="promo-car-image" style="background-image: url(images/cars/<?php echo $car->image; ?>);">
                </div>
                <div class="description-car d-flex justify-content-center">
                    <?php if(strpos($car->id, 'promociones') !== false) { ?>
                  <div class="button-car button-promo">
                     <a 
                       id="<?php echo $title_site->id; ?>-whatsapp" 
                       href="<?php echo $whats; ?>"
                       class="btn btn-outline-primary" 
                       target='<?php echo $href; ?>'
                       rel="noopener noreferrer"
                    >
                      <i class="fab fa-whatsapp"></i>
                    </a>
                    <span>Envíanos Whatsapp</span>
                  </div>
                  <?php } else { ?>
                  <div class="button-car">
                    <a  id="cotiza-<?php echo $car->id; ?>" href="<?php echo $title_site->url . $car->cotiza; ?>" class="btn btn-outline-primary" target='<?php echo $href; ?>'>
                      <i class="fas fa-car"></i>
                    </a>
                    <span>Cotiza tu ford</span>
                  </div>
                  <div class="button-car">
                    <a id="manejo-<?php echo $car->id; ?>" href="<?php echo $title_site->url . $car->manejo; ?>" class="btn btn-outline-primary" target='<?php echo $href; ?>'>
                      <i class="fas fa-road"></i>
                    </a>
                    <span>Prueba de manejo</span>
                  </div>
                  <?php if ($car->id !== 'heritage' ) { ?>
                    <div class="button-car">
                      <a  id="more-<?php echo $car->id; ?>" href="<?php echo $title_site->url . $car->more; ?>" class="btn btn-outline-primary" target='<?php echo $href; ?>'>
                        <i class="fas fa-ellipsis-h"></i>
                      </a>
                      <span>Conoce más</span>
                    </div>
                  <?php } ?>
                </div>
                <div class="terms">
                  <p class="" id="<?php echo $car->id; ?>collap">
                    <?php echo $car->terms; ?>
                  </p>
                  <hr>
                </div>
                <?php } ?>
              </div>
            <?php endforeach ?>
            <!-- Fin del contenedor de carros -->
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="contact-form">
    <div class="container color-form">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-8 col-md-8">
          <h2 class="contact-title">Contáctame para más información</h2>
        </div>
      </div>
      <?php if ((bool)$asesor-> avalible): ?>
      <div class="row justify-content-center sale-contact">
        <div class="col-4 col-sm-1 col-md-1">
          <div class="sale-person">
            <img src="images/personas/<?php echo $asesor->picture; ?>" alt="asesor"  class="img-fluid rounded-circle">
          </div>
        </div>
        <div class="col-8 col-sm-3 col-md-3">
          <div class="sale-name">
            <span><?php echo htmlspecialchars($asesor->name, ENT_QUOTES, 'UTF-8'); ?></span>
            <span>tu asesor Ford</span>
          </div>
        </div>
        <div class="col-6 col-sm-2 col-md-2 space-30">
          <div class="sale-buttons">
            <a id="<?php echo $title_site->id; ?>-telphone" href="tel:<?php echo $asesor->tel; ?>" class="btn btn-outline-primary phone"><i class="fas fa-phone"></i></a>
          </div>
        </div>
        <div class="col-6 col-sm-2 col-md-2 space-30">
          <div class="sale-buttons">
            <a id="<?php echo $title_site->id; ?>-whatsapp" href=https://wa.me/+52<?php echo $asesor->whats; ?>?text=<?php echo $asesor->textw ?>" class="btn btn-success green"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>
      </div>
      <?php endif ?>
      <div class="row justify-content-center">
        
        <div class="col-12 col-sm-8 col-md-8">
          <p>Llena el formulario y  me comunicaré contigo a la brevedad</p>
          <form class="web-form" method="GET" action="backend/send-message.php">
            <div class="form-group">
              <label for="name">Nombre</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
              <label for="email">Correo</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
              <label for="name">Teléfono</label>
              <input type="text" class="form-control" id="telphone" name="telphone" required>
            </div>
            <button id="send-contact" type="submit" class="btn btn-primary d-block send">Enviar</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <?php
    $waze = get_waze();
    $mapurl = get_urlmap();
  ?>
  <section class="map">
    <div class="container">
      <div class="row">
        <div class="col-12 col-sm-5 col-md-5">
          <h2 class="map-title">También puedes visitarnos en nuestra sucursal, estamos listos para ayudarte</h2>
          <div class="description-map d-flex justify-content-start">
            <div class="button-location">
              <a  href="<?php echo $waze; ?>" class="btn btn-outline-primary" target='_blank'>
                <i class="fab fa-waze"></i>
              </a>
              <span>Waze</span>
            </div>
            <div class="button-location">
              <a  href="<?php echo $mapurl; ?>" class="btn btn-outline-primary" target='_blank'>
                <i class="fas fa-map-marker-alt"></i>
              </a>
              <span>Google Maps</span>
            </div>
          </div>
          <div class="d-none d-sm-block">
            <a class="navbar-brand d-flex flex-row align-items-center " href="#">
              <img src="images/CAVSA.png"  class="d-inline-block align-top" alt="" style="max-height: 84px;">
              <span class="name-distribuitor"><?php echo $title_site->title; ?></span>
            </a>
          </div>
        </div>
        <div class="col-12 col-sm-7 col-md-7">
        <?php 
          $map = get_map();
          echo $map;
        ?>
        
        </div>
        <div class="d-block d-sm-none col-12 last-logo">
          <a class="navbar-brand d-flex flex-row align-items-center" href="#">
            <img src="images/CAVSA.png"  class="d-inline-block align-top" alt="" style="max-height: 84px;">
            <span class="name-distribuitor"><?php echo $title_site->title; ?></span>
          </a>
        </div>
      </div>
    </div>
  </section>
  <footer>
    <?php 
      $terms = get_terms();
    ?>
    <div class="container">
      <a href="<?php  echo $terms;?>" target='<?php echo $href; ?>' id="terms-condition"> Términos y condiciones</a>
    </div>
  </footer>
  <iframe id="ifrm" style="display:none;" src="https://<?php echo $title_site->id; ?>.goodhumans.mx/"></iframe>


  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script src="js/main.js"></script>
  <script src="js/scroll.js"></script>
  <script>
    const iframe = document.getElementById('ifrm')
    if (!localStorage.getItem('mixpanelId') && window.location === window.parent.location) {
      localStorage.setItem('masterSite', 'true')
    }

    setTimeout(() => {
      if (localStorage.getItem('mixpanelId')) {
        mixpanel.identify(localStorage.getItem('mixpanelId'))
      }
    }, 10000)

    setTimeout(() => {
        if (window.location === window.parent.location) {
          const usedId = mixpanel.get_distinct_id().split(':')[1]
	  mixpanel.identify(usedId)
          iframe.contentWindow.postMessage(mixpanel.get_distinct_id(), "*")
        }
    }, 10000)

    window.addEventListener('message', function (e) {
	if (window.location !== window.parent.location &&
	      !localStorage.getItem('mixpanelId') &&
	      !localStorage.getItem('masterSite')
           ) {
	  localStorage.setItem('mixpanelId', e.data)
	}
    }, false)
  </script>
</body>
</html>
