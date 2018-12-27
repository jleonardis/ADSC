(function($) {

  function showSpecial(selector, displayType) {
    $(selector).css('display', displayType);
  }

  $(window).keydown(function(event){
    if(event.keyCode == 13 && document.activeElement.id !== 'searchBox') {
      event.preventDefault();
      return false;
    }
  });

  $('.participant-row').click(function() {
    window.open($(this).data("href"), "Participante");
  });

  })(jQuery);
