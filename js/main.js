(function($) {

  function showSpecial(selector, displayType) {
    $(selector).css('display', displayType);
  }


  $('#back-button').click(function() {
    $('#main-form').submit();
  });

  $('.participant-row').click(function() {
    window.open($(this).data("href"), "Participante");
  });


  })(jQuery);
