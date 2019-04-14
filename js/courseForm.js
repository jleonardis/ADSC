var startDateElem = document.getElementById('startDate');
var endDateElem = document.getElementById('endDate');

startDateElem.addEventListener("change", function() {
  endDateElem.setAttribute("min", startDateElem.value);
}, false);

endDateElem.addEventListener("change", function() {
  startDateElem.setAttribute("max", endDateElem.value);
}, false);

var programElem = document.getElementById('programId');
var divisionElem = document.getElementById('divisionId');
var defaultOption = new Option('--Elige un Eje--', '');



const updateDivisionsDropDown = function() {

  var programId = programElem.value;
  while(divisionElem.firstChild) {
    divisionElem.removeChild(divisionElem.firstChild);
  }

  if(programId === '') {
    divisionElem.appendChild(new Option('No hay programa seleccionado', ''));
    divisionElem.disabled = true;
  }
  else {
    var hasDivisions = false;
    divisions.forEach(function(elem) {
      if(elem['programId'] === programId) {
        hasDivisions = true;
        divisionElem.appendChild(new Option(elem['name'], elem['divisionId']));
      }
    });
    if(hasDivisions) {
      divisionElem.prepend(new Option('--Elige un Eje--', '', false, true));
      divisionElem.disabled = false;
    }
    else {
      divisionElem.appendChild(new Option('Este Programa no Tiene Ejes', ''));
      divisionElem.disabled = true;
    }
  }
}

programElem.addEventListener("change", updateDivisionsDropDown);

updateDivisionsDropDown();
