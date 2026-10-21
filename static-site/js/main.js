/* Haroon Muneer — Portfolio scripts */
(function () {
  'use strict';

  // ---- Mobile nav ----
  var toggle = document.querySelector('.menu-toggle');
  var nav = document.querySelector('.nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open);
      toggle.textContent = open ? '✕' : '☰';
    });
  }

  // ---- Reveal on scroll ----
  var reveals = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      });
    }, { threshold: .12 });
    reveals.forEach(function (el) { io.observe(el); });
  } else {
    reveals.forEach(function (el) { el.classList.add('in'); });
  }

  // ---- Counters ----
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var target = parseInt(el.dataset.count, 10), done = false;
    var run = function () {
      if (done) return; done = true;
      var start = null;
      function step(t) {
        if (!start) start = t;
        var p = Math.min((t - start) / 1400, 1);
        el.textContent = Math.floor(p * target) + '+';
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    };
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (en, ob) {
        if (en[0].isIntersecting) { run(); ob.disconnect(); }
      }).observe(el);
    } else run();
  });

  // ---- Skill bars ----
  var bars = document.querySelectorAll('.bar i');
  if (bars.length && 'IntersectionObserver' in window) {
    var bo = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.style.width = e.target.dataset.w + '%'; bo.unobserve(e.target); }
      });
    }, { threshold: .4 });
    bars.forEach(function (b) { bo.observe(b); });
  } else bars.forEach(function (b) { b.style.width = b.dataset.w + '%'; });

  // ---- Project filter ----
  var fbtns = document.querySelectorAll('.filter-btn');
  fbtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      fbtns.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      var f = btn.dataset.filter;
      document.querySelectorAll('.project-card[data-cat]').forEach(function (c) {
        c.classList.toggle('hide', f !== 'all' && c.dataset.cat.split(' ').indexOf(f) === -1);
      });
    });
  });

  // ---- Project modal ----
  var modal = document.getElementById('projectModal');
  if (modal) {
    var mImg = modal.querySelector('img'), mT = modal.querySelector('h3'),
        mK = modal.querySelector('.eyebrow'), mD = modal.querySelector('.m-desc'),
        mTags = modal.querySelector('.tags'), mStack = modal.querySelector('.m-stack');
    document.querySelectorAll('.project-card[data-title]').forEach(function (card) {
      card.addEventListener('click', function () {
        mImg.src = card.dataset.img; mImg.alt = card.dataset.title;
        mT.textContent = card.dataset.title; mK.textContent = card.dataset.kind;
        mD.textContent = card.dataset.desc;
        mStack.textContent = 'Stack: ' + card.dataset.stack;
        mTags.innerHTML = '';
        card.dataset.tags.split(',').forEach(function (t) {
          var s = document.createElement('span'); s.className = 'tag'; s.textContent = t; mTags.appendChild(s);
        });
        modal.classList.add('open'); document.body.style.overflow = 'hidden';
      });
    });
    var close = function () { modal.classList.remove('open'); document.body.style.overflow = ''; };
    modal.addEventListener('click', function (e) { if (e.target === modal || e.target.classList.contains('modal-close')) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  }

  // ---- Blog search ----
  var search = document.getElementById('blogSearch');
  if (search) {
    search.addEventListener('input', function () {
      var q = search.value.toLowerCase().trim(), shown = 0;
      document.querySelectorAll('.post-card').forEach(function (c) {
        var m = c.textContent.toLowerCase().indexOf(q) !== -1;
        c.style.display = m ? '' : 'none'; if (m) shown++;
      });
      var e = document.querySelector('.empty'); if (e) e.style.display = shown ? 'none' : 'block';
    });
  }

  // ---- Blog post renderer (post.html?id=slug) ----
  var postRoot = document.getElementById('postRoot');
  if (postRoot && window.POSTS) {
    var id = new URLSearchParams(location.search).get('id');
    var post = window.POSTS.filter(function (p) { return p.id === id; })[0] || window.POSTS[0];
    document.title = post.title + ' — Haroon Muneer';
    postRoot.innerHTML = '<span class="eyebrow">' + post.cat + '</span>' +
      '<h1 class="display">' + post.title + '</h1>' +
      '<p class="meta">' + post.date + ' · ' + post.read + '</p>' +
      '<div style="margin:30px 0;border:1px solid var(--line)"><img src="' + post.img + '" alt=""></div>' + post.body;
  }

  // ---- Contact form ----
  var form = document.getElementById('contactForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var ok = true;
      form.querySelectorAll('[required]').forEach(function (f) {
        var bad = !f.value.trim() || (f.type === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(f.value));
        f.classList.toggle('invalid', bad);
        var er = f.parentNode.querySelector('.err'); if (er) er.style.display = bad ? 'block' : 'none';
        if (bad) ok = false;
      });
      if (!ok) return;
      var d = new FormData(form);
      /* To receive messages without a mail client, point the form to a service such as
         Formspree/Web3Forms with fetch(). Until then it opens the visitor's mail app. */
      var body = 'Name: ' + d.get('name') + '\nEmail: ' + d.get('email') + '\nService: ' + d.get('service') + '\n\n' + d.get('message');
      window.location.href = 'mailto:haroonmuneer@gmail.com?subject=' + encodeURIComponent(d.get('subject') || 'Project enquiry') + '&body=' + encodeURIComponent(body);
      var n = document.getElementById('formNote'); if (n) n.style.display = 'block';
      form.reset();
    });
  }

  // ---- Back to top ----
  var top = document.querySelector('.to-top');
  if (top) {
    window.addEventListener('scroll', function () { top.classList.toggle('show', window.scrollY > 600); });
    top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
  }

  // ---- Footer year ----
  document.querySelectorAll('.year').forEach(function (y) { y.textContent = new Date().getFullYear(); });
})();
