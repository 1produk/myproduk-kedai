// assets/app.js
document.addEventListener('click', function(e){
  var el = e.target;
  
  xhr.open('POST', '/a/upload.php', true);


  // preview banner
  if (el && el.id === 'preview_banner_btn') {
    var field = document.getElementById('company_banner');
    var preview = document.getElementById('banner_preview');
    if (!field) return;
    var url = field.value.trim();
    if (!url) { preview.innerHTML = ''; return; }
    preview.innerHTML = '<img src="'+url+'" style="max-width:300px; height:auto; border:1px solid #ddd; padding:4px;" onerror="this.style.display=\'none\'">';
  }

  // remove image
  if (el && el.classList.contains('btn-remove-image')) {
    var target = el.getAttribute('data-target');
    if (!target) return;
    var field = document.querySelector(target);
    if (field) field.value = '';
    var preview = document.querySelector(target+'_preview');
    if (preview) preview.innerHTML = '';
  }
});

// preview when product image field changes (delegated)
document.addEventListener('input', function(e){
  var el = e.target;
  if (el && el.classList.contains('prod-image')) {
    var preview = document.getElementById(el.id + '_preview');
    if (!preview) return;
    var url = el.value.trim();
    if (!url) { preview.innerHTML = ''; return; }
    preview.innerHTML = '<img src="'+url+'" style="max-width:180px; height:auto; border:1px solid #ddd; padding:4px;" onerror="this.style.display=\'none\'">';
  }
});

