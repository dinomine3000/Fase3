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

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function searchWiki(query, basePath, callback) {
  if (query.trim().length < 2) { callback(null); return; }
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      try { callback(JSON.parse(xhr.responseText)); } catch(e) { callback(null); }
    }
  };
  xhr.open('GET', basePath + 'processSearchWiki.php?q=' + encodeURIComponent(query.trim()), true);
  xhr.send(null);
}

function searchAllHeader(query, containerId, basePath) {
  var container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '';
  container.classList.remove('has-results');
  if (query.trim().length < 2) return;

  var wikiDone = false, usersDone = false;
  var wikiData = null, usersData = null;

  function render() {
    if (!wikiDone || !usersDone) return;
    var html = '', total = 0;

    if (wikiData && wikiData.categories && wikiData.categories.length) {
      html += '<div class="suggest-group-label">Topics</div>';
      wikiData.categories.slice(0, 2).forEach(function(c) {
        html += '<a class="suggest-item" href="' + basePath + 'viewPage.php?primaryCategory=' + encodeURIComponent(c.name) + '">'
              + '<span class="suggest-item-type">Topic</span>'
              + '<span class="suggest-item-name">' + escHtml(c.name) + '</span>'
              + '</a>';
        total++;
      });
    }

    if (wikiData && wikiData.subcategories && wikiData.subcategories.length) {
      html += '<div class="suggest-group-label">Subtopics</div>';
      wikiData.subcategories.slice(0, 2).forEach(function(s) {
        html += '<a class="suggest-item" href="' + basePath + 'viewPage.php?primaryCategory=' + encodeURIComponent(s.primaryCategory) + '&secondaryCategory=' + encodeURIComponent(s.name) + '">'
              + '<span class="suggest-item-type">Subtopic</span>'
              + '<span class="suggest-item-name">' + escHtml(s.name) + '</span>'
              + '<span class="suggest-item-meta">' + escHtml(s.primaryCategory) + '</span>'
              + '</a>';
        total++;
      });
    }

    if (wikiData && wikiData.pages && wikiData.pages.length) {
      html += '<div class="suggest-group-label">Pages</div>';
      wikiData.pages.slice(0, 2).forEach(function(p) {
        html += '<a class="suggest-item" href="' + basePath + 'viewPage.php?pageTitle=' + encodeURIComponent(p.pageTitle) + '">'
              + '<span class="suggest-item-type">Page</span>'
              + '<span class="suggest-item-name">' + escHtml(p.pageTitle) + '</span>'
              + '</a>';
        total++;
      });
    }

    if (usersData && usersData.length) {
      html += '<div class="suggest-group-label">Users</div>';
      usersData.slice(0, 2).forEach(function(u) {
        html += '<a class="suggest-item" href="' + basePath + 'profile.php?user=' + encodeURIComponent(u.name) + '">'
              + '<span class="suggest-item-type">User</span>'
              + '<span class="suggest-item-name">' + escHtml(u.name) + '</span>'
              + '</a>';
        total++;
      });
    }

    if (total > 0) {
      container.innerHTML = html;
      container.classList.add('has-results');
    }
  }

  searchWiki(query, basePath, function(data) {
    wikiData = data; wikiDone = true; render();
  });

  var userXhr = new XMLHttpRequest();
  userXhr.onreadystatechange = function() {
    if (userXhr.readyState === 4 && userXhr.status === 200) {
      try { usersData = JSON.parse(userXhr.responseText); } catch(e) { usersData = null; }
      usersDone = true; render();
    }
  };
  userXhr.open('GET', basePath + 'processSearchUsers.php?user=' + encodeURIComponent(query.trim()), true);
  userXhr.send(null);
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