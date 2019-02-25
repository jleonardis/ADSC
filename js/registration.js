(function($) {
  console.log(names);
  hideAdminOrCoord('#isCoordinator', '.administrator-info');
  hideAdminOrCoord('#isAdministrator', '.administrator-info');
  showUserInfo();

    $('.names').change(function() {
      var firstName = $('#firstName').val();
      var lastName = $('#lastName').val();
      var nickname = $('#nickname').val();
      var parFirstName;
      var parLastName;
      var parNickName;

      if(nickname && lastName && firstName) {
        names.forEach(function(elem) {
          parFirstName = elem['firstName'];
          parLastName = elem['lastName'];
          parNickname = elem['nickname'];

          if(parNickname && parLastName && parFirstName &&
          elem['lastName'].toLowerCase() === lastName.toLowerCase() &&
          elem['firstName'].toLowerCase() === firstName.toLowerCase() &&
          elem['nickname'].toLowerCase() === nickname.toLowerCase()) {
            if(window.confirm('hay otro participante con ese nombre. Quisieras ver su perfil?')){
              window.open('/participantPage.php?participantId=' + elem['participantId']);
            }

          }
        })
      }
    })

    $('#dpi').change(function() {
      //trim all white space
      var dpi = $('#dpi').val().replace(/[^\d[a-z]/gi, '');

      if(dpi !== null) {
        names.forEach(function(elem) {
          if(elem['dpi'] === dpi) {
            if(window.confirm('hay otro participante con ese numero de DPI. Quisieras ver su perfil?')){
              window.open('/participantPage.php?participantId=' + elem['participantId']);
            }
          }
        });
        var dpiCheckbox = document.getElementById('noDPI');
        if(!dpiCheckbox || !(dpiCheckBox.checked)){
          if(!(/^\d{15}$/.test(dpi))) {
            alert("ese DPI no es valido");
            document.getElementById('dpi').value = '';
          }
        }

      }
    })


  $('#isCoordinator').change(function() {
    hideAdminOrCoord('#isCoordinator', '.administrator-info');
  });


  $('#isAdministrator').change(function() {
    hideAdminOrCoord('#isAdministrator', '.coordinator-info');
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
      $('.user-info input').prop('required', true)
    }
    else {
      $('.user-info').hide();
      $('.user-info input').prop('required', false)
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

  $('.password-box').change(function() {
    if($('#password-repeat').val() !== '' && $('#password-repeat').val() !== $('#password').val()){
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
