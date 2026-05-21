function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}

/* ── INIT ── */
const params = new URLSearchParams(location.search);
const isEdit = params.has('id');

if (isEdit) {
  document.getElementById('editorHeading').textContent = 'Edit Article';
  document.title = 'Edit Article — Portal Wiki';
}

/* Populate category select */
const catSelect    = document.getElementById('fieldCategory');
const subcatSelect = document.getElementById('fieldSubcategory');

categories.forEach(c => {
  const opt = document.createElement('option');
  opt.value = c.id;
  opt.textContent = c.name;
  catSelect.appendChild(opt);
});

function populateSubcats(catId, selectedId) {
  subcatSelect.innerHTML = '';
  const matches = subcategories.filter(s => s.parent_id === catId);
  if (!catId || matches.length === 0) {
    subcatSelect.disabled = true;
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = catId ? 'No subcategories…' : 'Select category first…';
    subcatSelect.appendChild(placeholder);
    return;
  }
  subcatSelect.disabled = false;
  const blank = document.createElement('option');
  blank.value = '';
  blank.textContent = 'Select subcategory…';
  subcatSelect.appendChild(blank);
  matches.forEach(s => {
    const opt = document.createElement('option');
    opt.value = s.id;
    opt.textContent = s.name;
    subcatSelect.appendChild(opt);
  });
  if (selectedId) subcatSelect.value = selectedId;
}

catSelect.addEventListener('change', () => populateSubcats(catSelect.value, null));

/* Pre-fill fields in edit mode */
if (editArticle) {
  document.getElementById('fieldTitle').value   = editArticle.title   || '';
  document.getElementById('fieldContent').value = editArticle.content || '';
  catSelect.value = editArticle.cat || '';
  populateSubcats(editArticle.cat || '', editArticle.subcat || '');
}

/* ── MARKDOWN TOOLBAR ── */
function insertMd(before, after) {
  const ta    = document.getElementById('fieldContent');
  const start = ta.selectionStart;
  const end   = ta.selectionEnd;
  const sel   = ta.value.substring(start, end);
  const ins   = before + sel + after;
  ta.value    = ta.value.substring(0, start) + ins + ta.value.substring(end);
  ta.focus();
  const cur = start + before.length + sel.length;
  ta.setSelectionRange(cur, cur);
}

/* ── SAVE ── */
function saveArticle() {
  const payload = {
    id:          isEdit ? params.get('id') : null,
    title:       document.getElementById('fieldTitle').value.trim(),
    category:    catSelect.value,
    subcategory: subcatSelect.value,
    content:     document.getElementById('fieldContent').value,
  };

  if (!payload.title) {
    alert('Please add a title before saving.');
    document.getElementById('fieldTitle').focus();
    return;
  }

  /* TODO: POST payload to backend endpoint */
  console.log('save payload:', payload);
  alert('Save wired up — POST to backend here.');
}
