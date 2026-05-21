function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}

/* Populate parent dropdown */
const parentSelect = document.getElementById('fieldParent');
categories.forEach(c => {
  const opt = document.createElement('option');
  opt.value = c.id;
  opt.textContent = c.name;
  parentSelect.appendChild(opt);
});

/* ── IMAGE UPLOAD ── */
function previewImage(input) {
  const preview     = document.getElementById('imgPreview');
  const placeholder = document.getElementById('imgPlaceholder');
  if (input.files && input.files[0]) {
    preview.src = URL.createObjectURL(input.files[0]);
    preview.classList.add('visible');
    placeholder.style.display = 'none';
  }
}

/* ── CREATE ── */
function createCategory() {
  const payload = {
    name:   document.getElementById('fieldName').value.trim(),
    parent: parentSelect.value || null,
    image:  document.getElementById('fieldImage').files[0] || null,
  };

  if (!payload.name) {
    alert('Please enter a category name.');
    document.getElementById('fieldName').focus();
    return;
  }

  /* TODO: POST payload to backend endpoint */
  console.log('create category payload:', payload);
  alert('Create wired up — POST to backend here.');
}
