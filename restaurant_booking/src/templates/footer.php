  </main>

  <!-- ─── FOOTER ──────────────────────────────────────────── -->
  <footer class="site-footer">
    <p>&copy; <?= date('Y') ?> La Table d'Or — Tous droits réservés</p>
  </footer>

  <!-- ─── GSAP 1.11.1 ────────────────────────────────────── -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.11.1/TweenMax.min.js"></script>
  <script src="/restaurant_booking/public/js/animations.js"></script>

  <!-- ─── MOBILE NAV TOGGLE ──────────────────────────────── -->
  <script>
  (function(){
    var toggle = document.getElementById('navToggle');
    if (toggle) {
      toggle.addEventListener('click', function() {
        var links = document.querySelector('.nav-links');
        if (links) links.classList.toggle('open');
        toggle.classList.toggle('active');
      });
    }
  })();
  </script>
</body>
</html>
