(function($) {
$('#cancelCourse').click(function() {
  if(confirm('estas segur@ que quieres eliminar este curso?')){
    window.location = $(this).data('href');
  }
});

$('#removeAssignment').click(function() {
  if(confirm('estas segur@ que quieres eliminar esta tarea?')){
    window.location = $(this).data('href');
  }
});
})(jQuery);
