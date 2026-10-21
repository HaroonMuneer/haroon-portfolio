/* Haroon Muneer Portfolio theme: small enhancements */
(function () {
  'use strict';

  // Mobile menu
  var toggle = document.querySelector('.menu-toggle');
  var nav = document.querySelector('.hm-primary-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.textContent = open ? '✕' : '☰';
    });
  }

  // Back to top
  var top = document.querySelector('.hm-to-top');
  if (top) {
    window.addEventListener('scroll', function () { top.classList.toggle('show', window.scrollY > 600); });
    top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
  }

  // Project filter: works with Elementor button widgets that carry the classes
  // hm-filter hm-filter-<slug>, and cards that carry hm-project-card hm-cat-<slug>.
  var filters = document.querySelectorAll('.hm-filter');
  if (filters.length) {
    filters.forEach(function (f) {
      f.addEventListener('click', function (e) {
        e.preventDefault();
        var m = f.className.match(/hm-filter-([a-z0-9_-]+)/);
        var slug = m ? m[1] : 'all';
        filters.forEach(function (x) { x.classList.remove('is-active'); });
        f.classList.add('is-active');
        document.querySelectorAll('.hm-project-card').forEach(function (c) {
          c.classList.toggle('hm-hide', slug !== 'all' && !c.classList.contains('hm-cat-' + slug));
        });
      });
    });
  }

  // Blog search
  var search = document.getElementById('hmSearch');
  if (search) {
    search.addEventListener('input', function () {
      var q = search.value.toLowerCase().trim(), shown = 0;
      document.querySelectorAll('.hm-post-card').forEach(function (c) {
        var ok = c.textContent.toLowerCase().indexOf(q) !== -1;
        c.style.display = ok ? '' : 'none';
        if (ok) shown++;
      });
      var empty = document.querySelector('.hm-empty');
      if (empty) empty.style.display = shown ? 'none' : 'block';
    });
  }
})();
