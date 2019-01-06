(function($) {

  showAddStudentButton();

  function normalizeText(text) {
    return text.toLowerCase().replace(/ /g, '').replace(/á/g, 'a').replace(/é/g, 'e')
               .replace(/ó/g, 'o').replace(/í/g, 'i').replace(/ü/g, 'u').replace(/ñ/g, 'n');
  }
  
  async function searchNames() {
    var results = false;
    //change if we move away from spreading names across two td's
    var search = normalizeText($('#searchBox').val());
    $('.search-group .search-row').each(function() {
      var text = normalizeText($(this).text());
      if (text.indexOf(search) !== -1) {
        $(this).show();
        results=true;
      }
      else {
        $(this).hide();
      }
    });
    if(results){
      $('.search-head').show();
    }
    else {
      $('.search-head').hide();
      alert("busqueda sin resultados")
    }
  }

  function showAddStudentButton() {
    if($('#addParticipantTable input:checkbox:checked').length > 0) {
      $('.addParticipants').show();
    }
    else {
      $('.addParticipants').hide();
    }
  }

  $(".select-checkbox").click(showAddStudentButton);

  //fire search on click or enter but only if the user has submitted more than 3 leters (so the search doesn't  take forever)
  $('#search').click(searchNames);

  $(document).keypress(function(e) {
    if(e.which === 13 && document.activeElement.id === 'searchBox'){
      searchNames();
      e.preventDefault();
      return false;
    }
  });


})(jQuery)
