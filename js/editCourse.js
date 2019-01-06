(function($) {
$('#cancelCourse').click(function() {
  if(confirm('estas segur@ que quieres eliminar este curso?')){
    window.location = $(this).data('href');
  }
});
})(jQuery);
