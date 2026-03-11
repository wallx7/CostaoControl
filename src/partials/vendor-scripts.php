<!-- Vendor Javascript (Require in all Page) -->
<script src="assets/js/vendor.js"></script>

<!-- App Javascript (Require in all Page) -->
<script src="assets/js/app.js"></script>
<script>
document.addEventListener('click',function(e){var t=e.target.closest('#toggle-fullscreen');if(!t)return;e.preventDefault();var d=document;var el=d.documentElement;var icon=t.querySelector('i');var label=t.querySelector('span');if(!d.fullscreenElement){if(el.requestFullscreen)el.requestFullscreen();else if(el.webkitRequestFullscreen)el.webkitRequestFullscreen();if(icon){icon.classList.remove('bx-fullscreen');icon.classList.add('bx-exit-fullscreen');}if(label){label.textContent='Sair da tela cheia';}}else{if(d.exitFullscreen)d.exitFullscreen();else if(d.webkitExitFullscreen)d.webkitExitFullscreen();if(icon){icon.classList.remove('bx-exit-fullscreen');icon.classList.add('bx-fullscreen');}if(label){label.textContent='Tela cheia';}}});
document.addEventListener('click',function(e){var btn=e.target.closest('[data-expand-target]');if(!btn)return;var sel=btn.getAttribute('data-expand-target');var el=document.querySelector(sel);if(!el)return;e.preventDefault();if(el.requestFullscreen){el.requestFullscreen();}else if(el.webkitRequestFullscreen){el.webkitRequestFullscreen();}});
</script>