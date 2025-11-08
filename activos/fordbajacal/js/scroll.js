const links = document.querySelectorAll("#menu-cars ul li a");
for (const link of links) {
  link.addEventListener("click", clickHandler);
}

function clickHandler(e) {
  e.preventDefault();
  const href = this.getAttribute("href");
  document.querySelector(href).scrollIntoView({
    behavior: "smooth"
  });
}


var btn = $('#button-up');

$(window).scroll(function() {
  if ($(window).scrollTop() > 300) {
    btn.addClass('show');
  } else {
    btn.removeClass('show');
  }
});

btn.on('click', function(e) {
  e.preventDefault();
  document.body.scrollIntoView({behavior: 'smooth', block: 'start'});

});

