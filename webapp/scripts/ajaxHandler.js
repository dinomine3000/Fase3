var xmlHttp;

function GetXmlHttpObject() {
  try {
    return new ActiveXObject("Msxml2.XMLHTTP");
  } catch(e) {}
  try {
    return new ActiveXObject("Microsoft.XMLHTTP");
  } catch(e) {}
  try {
    return new XMLHttpRequest();
  } catch(e) {}
  alert("XMLHttpRequest not supported");
  return null;
}

function updateSecondaryCategories() {
  var primarySelect = document.getElementById("primaryCategory");
  var secondarySelect = document.getElementById("secondaryCategory");
  var selectedPrimary = primarySelect.value;

  // Clear current options except the placeholder
  secondarySelect.innerHTML = '<option value="">-- Select Secondary Category --</option>';

  if (selectedPrimary === "") {
    return;
  }

  xmlHttp = GetXmlHttpObject();
  if (xmlHttp == null) {
    return;
  }

  // Encode parameter to handle spaces or special characters safely
  var url = "getSecondaryCategories.php?primary=" + encodeURIComponent(selectedPrimary);

  xmlHttp.onreadystatechange = function() {
    if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
      // Expecting a JSON array string back from the server
      var subcategories = JSON.parse(xmlHttp.responseText);
      
      for (var i = 0; i < subcategories.length; i++) {
        var opt = document.createElement("option");
        opt.value = subcategories[i];
        opt.textContent = subcategories[i];
        secondarySelect.appendChild(opt);
      }
    }
  };

  xmlHttp.open("GET", url, true);
  xmlHttp.send(null);
}

function searchUsers(query) {
  const suggestionsContainer = document.getElementById('autocomplete-suggestions');
  const currentValue = query.trim();

  // Clear current options except the placeholder
  suggestionsContainer.innerHTML = '';

  if (query.length < 3) {
    return;
  }

  xmlHttp = GetXmlHttpObject();
  if (xmlHttp == null) {
    return;
  }

  // Encode parameter to handle spaces or special characters safely
  var url = "processSearchUsers.php?user=" + encodeURIComponent(currentValue);

  xmlHttp.onreadystatechange = function() {
    if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
      // Expecting a JSON array string back from the server
      var users = JSON.parse(xmlHttp.responseText);
      
      for (var i = 0; i < users.length; i++) {
        var opt = document.createElement("option");
        var name = users[i]["name"];
        var link = document.createElement('a');
        link.href = `profile.php?user=${encodeURIComponent(name)}`;
        link.textContent = name;
        
        suggestionsContainer.appendChild(link);
      }
    }
  };

  xmlHttp.open("GET", url, true);
  xmlHttp.send(null);
}