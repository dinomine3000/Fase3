document.getElementById('edit-bio-btn').addEventListener('click', function() {
    document.getElementById('bio-view').style.display = 'none';
    document.getElementById('bio-edit-form').style.display = 'block';
});

document.getElementById('cancel-bio-btn').addEventListener('click', function() {
    document.getElementById('bio-edit-form').style.display = 'none';
    document.getElementById('bio-view').style.display = 'block';
});