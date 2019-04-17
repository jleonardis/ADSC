(function($){

  $('.remove-session').click(function() {
    if(confirm('estas segur@ que quieres eliminar esta sesión')) {
      return true;
    }
  });

  var checkboxes = $('.select-all-checkbox');

  checkboxes.change(function() {
    var inputs = $('.' + $(this).data('date'));
    if($(this)[0].checked) {
      $.each(inputs, function(ind, elem){
        elem.value = 'present';
      });
    } else {
      $.each(inputs, function(ind, elem){
        elem.value = 'absent';
      });
    }
  });

  Array.prototype.slice.call(checkboxes).forEach(function(elem){
    var inputs = Array.prototype.slice.call(document.getElementsByClassName(elem.dataset.date));
    elem.checked = inputs.every(x => x.value == 'present');
  });

})(jQuery);
