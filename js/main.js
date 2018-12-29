(function($) {

  function showSpecial(selector, displayType) {
    $(selector).css('display', displayType);
  }

  $('.participant-row').click(function() {
    window.open($(this).data("href"), "Participante");
  });

  })(jQuery);
