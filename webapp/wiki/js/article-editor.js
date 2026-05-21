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
  document.title = 'Edit Article — Smiki';
}

/* Populate category select */
const catSelect = document.getElementById('fieldCategory');
categories.forEach(c => {
  const opt = document.createElement('option');
  opt.value = c.id;
  opt.textContent = c.name;
  catSelect.appendChild(opt);
});

/* Pre-fill fields in edit mode */
if (editArticle) {
  document.getElementById('fieldTitle').value    = editArticle.title   || '';
  document.getElementById('fieldTags').value     = editArticle.tag     || '';
  document.getElementById('fieldContent').value  = editArticle.content || '';
  catSelect.value = editArticle.cat || '';
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
    id:       isEdit ? params.get('id') : null,
    title:    document.getElementById('fieldTitle').value.trim(),
    category: catSelect.value,
    tags:     document.getElementById('fieldTags').value.trim(),
    content:  document.getElementById('fieldContent').value,
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
