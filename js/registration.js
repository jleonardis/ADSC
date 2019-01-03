(function($) {

  hideAdminOrCoord('#isCoordinator', '.administrator-info');
  hideAdminOrCoord('#isAdministrator', '.administrator-info');
  showUserInfo();

    $('.names').change(function() {
      var firstName = $('#firstName').val();
      var lastName = $('#lastName').val();
      var nickname = $('#nickname').val();

      if(nickName && lastName && firstName) {
        names.forEach(function(elem) {
          if(elem['lastName'].toLowerCase() === lastName.toLowerCase() &&
          elem['firstName'].toLowerCase() === firstName.toLowerCase() &&
          elem['nickname'].toLowerCase() === nickname.toLowerCase()) {
            confirm() // come back when we have INTERNET
            window.open('/participantPage.php?participantId=' + elem['participantId']);

          }
        })
      }
    })

    $('#dpi').change(function() {
      //trim all white space
      var dpi = $('#dpi').val();

      if(dpi) {
        names.forEach(function(elem) {
          if(elem['dpi'] === dpi) {
            confirm() // come back when we have INTERNET
            window.open('/participantPage.php?participantId=' + elem['participantId']);

          }
        })
      }
    })


  $('#isCoordinator').change(function() {
    hideAdminOrCoord('#isCoordinator', '.administrator-info');
  });


  $('#isAdministrator').change(function() {
    hideAdminOrCoord('#isAdministrator', '.administrator-info');
  });

  $('.role-select').change(showUserInfo);

  function hideAdminOrCoord(selfId, otherClass) {
    if($(selfId).prop('checked')) {
      $(otherClass).attr('disabled', true)
                              .css('opacity', .5);
      if(selfId === '#isCoordinator') {
        $('.program-list').show();
      }
    }
    else {
      $(otherClass).attr('disabled', false)
                              .css('opacity', 1);
      if(selfId === '#isCoordinator') {
        $('.program-list').hide();
      }
    }
  }

  function showUserInfo() {
    if($('.role-select:checked').length) {
      $('.user-info').show();
    }
    else {
      $('.user-info').hide();
    }
  }

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
  });

})(jQuery)
