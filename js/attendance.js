(function($){

  $('.remove-session').click(function() {
    if(confirm('estas segur@ que quieres eliminar esa sesión')) {
      window.location = $(this).data("href");
    }
  })
})(jQuery);
