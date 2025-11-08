
  $(document).ready(function () {
    $("form").submit(function (event) {
      event.preventDefault();
      $(".send" ).addClass( "onclic");
      $(".send").text("");
      $(".send").prop("disabled", true);

      var formData = {
        name: $("#name").val(),
        email: $("#email").val(),
        telphone: $("#telphone").val()
      };

      if(ValidateEmail(formData.email)){
        if(formData.name != "" && formData.email != "" && formData.telphone != ""){
          this.submit();
        }else{
          $(".send" ).removeClass( "onclic");
          $(".send").text("Enviar");
          $(".send").prop("disabled", false);
          showAlert('danger', 'Ups, Parece que faltan datos, favor de llenar todos los campos.');
          return false;
        }
      } 
    });

    

    function showAlert(type, message, duration) {
        if (!message) return false;
        if (!type) type = 'info';
        $("<div class='alert alert-message alert-" +
            type +
            " data-alert alert-dismissible'>" +
            "<button class='close alert-link' data-dismiss='alert'>&times;</button>" +
            message + " </div>").hide().appendTo('body').fadeIn(300);
        if (duration === undefined) {
            duration = 5000;
        }
        if (duration !== false) {
            $(".alert-message").delay(duration).fadeOut(500, function() {
                $(this).remove();
            });
        }
    }

    function ValidateEmail(input) {
      var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
      if (input.match(validRegex)) {
        return true;
      } else {
        return false;
      }
    
    }



  });
