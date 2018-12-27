(function($) {

  // $('.program-list').hide();
  // $("#isCoordinator").change(function() {
  //   if($(this).prop("checked")){
  //     showSpecial('.program-list', 'inline-block');
  //     $('.administrator-info').attr('disabled', true)
  //                             .css("opacity", .5);
  //   }
  //   else {
  //     $('.program-list').hide();
  //     $('.administrator-info').attr('disabled', false)
  //                             .css("opacity", 1);
  //   }
  // });
  //
  // $('#isAdministrator').change(function() {
  //   if($(this).prop("checked")){
  //     $('.coordinator-info').attr('disabled', true)
  //                             .css("opacity", .5);
  //   }
  //   else {
  //     $('.coordinator-info').attr('disabled', false)
  //                             .css("opacity", 1);
  //   }
  // });

  $('#isCoordinator').change(function() {
    if($(this).prop('checked')) {
      $('.administrator-info').attr('disabled', true)
                              .css('opacity', .5);
    }
    else {
      $('.administrator-info').attr('disabled', false)
                              .css('opacity', 1);
    }
  });

  $('#isAdministrator').change(function() {
    if($(this).prop('checked')) {
      $('.coordinator-info').attr('disabled', true)
                              .css('opacity', .5);
    }
    else {
      $('.coordinator-info').attr('disabled', false)
                              .css('opacity', 1);
    }
  });

  $('#password-repeat').change(function() {
    if($(this).val() !== $('#password').val()){
      $('#password-warning').show();
    }
    else {
      $('#password-warning').hide();
    }
  });

  $('#registration-form').submit(function() {
    if($('#password-repeat').val() !== $('#password').val()){
      alert("Contraseñas no coinciden");
      return false;
    }
    if(!$('#isAdministrator').prop('checked') && !$('#isCoordinator').prop('checked') && !$('#isTeacher').prop('checked')) {
      alert("cual será su papel?");
      return false;
    }
  });

  $('#username').change(function() {
    usernames.forEach(function(elem) {
      console.log(elem);
      if(elem.username === $('#username').val()) {
        $('#username').val('');
        alert('usuario ya existe');
        document.isActive = document.getElementById('username');
      }
    });
  });

})(jQuery)
