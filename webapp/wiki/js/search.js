function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}

document.getElementById('searchInput').addEventListener('keydown', function (e) {
  if (e.key === 'Enter' && this.value.trim()) {
    location.href = 'search.php?q=' + encodeURIComponent(this.value.trim());
  }
});
