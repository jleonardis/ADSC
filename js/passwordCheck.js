(function($) {
  $('.password-box').change(function() {
    if($('#password-repeat').val() !== '' && $('#password-repeat').val() !== $('#password').val()){
      $('#password-warning').show();
    }
    else {
      $('#password-warning').hide();
    }
  });

  $('.submit-form').submit(function() {
    if($('#password-repeat').val() !== $('#password').val()){
      alert("Contraseñas no coinciden");
      return false;
    }
  });
})(jQuery);
