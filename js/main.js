(function($) {

  function showSpecial(selector, displayType) {
    $(selector).css('display', displayType);
  }

  $('#back-button').click(function() {
    $('#main-form').submit();
  });

  })(jQuery);
