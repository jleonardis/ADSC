(function($) {

  function showSpecial(selector, displayType) {
    $(selector).css('display', displayType);
  }

  $(document).ready(function() {
    $(window).keydown(function(event){
      if(event.keyCode == 13) {
        event.preventDefault();
        return false;
      }
    });
  });

  })(jQuery);
