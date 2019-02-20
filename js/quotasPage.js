
document.getElementById("participantId").addEventListener("change", showAmountDue, false);
document.getElementById("quotaId").addEventListener("change", showAmountDue, false);

function showAmountDue() {

  var quotaId = document.getElementById("quotaId").value;
  var participantId = document.getElementById('participantId').value;

  var amountNode = document.getElementById("amountDue");
  if(amountNode){
    amountNode.remove();
  }
  if(quotaId && participantId) {
    amountNode = document.createElement("span");
    amountNode.setAttribute("id", "amountDue");
    var amountDue = amountsDue[participantId][quotaId];
    amountNode.appendChild(document.createTextNode("  Debe " + "Q" + amountDue));
    document.getElementById("amountToPay").after(amountNode);

    document.getElementById("amountToPay").setAttribute("max", amountDue);
  }

}

document.getElementById("quotaDate").valueAsDate = new Date();
