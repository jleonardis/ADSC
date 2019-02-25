(function($) {
$('.delete-button').click(function() {
  if(confirm('estas segur@ que quieres eliminar este elemento?')){
    window.location = $(this).data('href');
  }
});

})(jQuery);
