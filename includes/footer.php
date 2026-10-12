<?php // includes/footer.php ?>
</main>
</div></div><!-- /canvas /page-frame -->
<script>
  // Theme toggle stored in a cookie — instant, no reload
  document.getElementById('themeBtn')?.addEventListener('click', function () {
    const html = document.documentElement;
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    document.cookie = 'cc_theme=' + next + ';path=/;max-age=2592000';
    this.textContent = next === 'dark' ? 'Light' : 'Dark';
  });
</script>
<script>window.CC_AUTH = <?= currentUser() ? 'true' : 'false' ?>;</script>
<script src="assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
