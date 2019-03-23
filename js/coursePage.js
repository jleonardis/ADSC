(function($) {
  
  $('.participant-row').click(function() {
    window.open($(this).data("href"), "Participante");
  });

  $('.remove-participant').click(function() {
    if(confirm('estas segur@ que quieres quitar este participante del curso?')){
      window.location = $(this).data("href");
    }
    return false;
  });

  $('.reactivate-participant').click(function() {
    if(confirm('estas segur@ que quieres reagregar este participante al curso?')){
      window.location = $(this).data("href");
    }
    return false;
  });
})(jQuery);
