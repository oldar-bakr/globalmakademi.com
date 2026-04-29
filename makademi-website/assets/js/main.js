document.addEventListener('DOMContentLoaded', function () {
  var menuBtn = document.getElementById('mobile-menu-btn');
  var mobileNav = document.getElementById('mobile-nav');
  var menuOpen = document.getElementById('menu-icon-open');
  var menuClose = document.getElementById('menu-icon-close');

  if (menuBtn && mobileNav) {
    menuBtn.addEventListener('click', function () {
      var isOpen = mobileNav.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', isOpen);
      if (menuOpen && menuClose) {
        menuOpen.style.display = isOpen ? 'none' : 'block';
        menuClose.style.display = isOpen ? 'block' : 'none';
      }
    });
    var links = mobileNav.querySelectorAll('a');
    links.forEach(function (link) {
      link.addEventListener('click', function () {
        mobileNav.classList.remove('open');
        menuBtn.setAttribute('aria-expanded', 'false');
        if (menuOpen && menuClose) {
          menuOpen.style.display = 'block';
          menuClose.style.display = 'none';
        }
      });
    });
  }

  var triggers = document.querySelectorAll('.accordion-trigger');
  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      var content = this.nextElementSibling;
      var expanded = this.getAttribute('aria-expanded') === 'true';
      var parent = this.closest('.accordion');
      if (parent) {
        parent.querySelectorAll('.accordion-trigger').forEach(function (t) {
          t.setAttribute('aria-expanded', 'false');
          if (t.nextElementSibling) t.nextElementSibling.classList.remove('open');
        });
      }
      if (!expanded) {
        this.setAttribute('aria-expanded', 'true');
        if (content) content.classList.add('open');
      }
    });
  });

  var yearEls = document.querySelectorAll('.current-year');
  var yr = new Date().getFullYear();
  yearEls.forEach(function (el) { el.textContent = yr; });

  function animateCounters() {
    var counters = document.querySelectorAll('[data-count]');
    counters.forEach(function (el) {
      if (el.dataset.counted) return;
      var rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        el.dataset.counted = 'true';
        var end = parseInt(el.dataset.count, 10);
        var suffix = el.dataset.suffix || '';
        var dur = 2000;
        var start = performance.now();
        function step(now) {
          var progress = Math.min((now - start) / dur, 1);
          el.textContent = Math.floor(progress * end) + suffix;
          if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
      }
    });
  }
  animateCounters();
  window.addEventListener('scroll', animateCounters, { passive: true });

  var searchInput = document.getElementById('course-search');
  var catBtns = document.querySelectorAll('.cat-btn');
  var courseCards = document.querySelectorAll('.course-item');
  var countDisplay = document.getElementById('course-count');
  var noResults = document.getElementById('no-results');

  function filterCourses() {
    if (!searchInput || !courseCards.length) return;
    var term = searchInput.value.toLowerCase();
    var activeCat = document.querySelector('.cat-btn.active');
    var cat = activeCat ? activeCat.dataset.category : 'All';
    var visible = 0;
    courseCards.forEach(function (card) {
      var title = (card.dataset.title || '').toLowerCase();
      var desc = (card.dataset.desc || '').toLowerCase();
      var cardCat = card.dataset.category || '';
      var matchSearch = !term || title.indexOf(term) !== -1 || desc.indexOf(term) !== -1;
      var matchCat = cat === 'All' || cardCat === cat;
      if (matchSearch && matchCat) {
        card.style.display = '';
        visible++;
      } else {
        card.style.display = 'none';
      }
    });
    if (countDisplay) countDisplay.textContent = visible;
    if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
  }

  if (searchInput) searchInput.addEventListener('input', filterCourses);
  catBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      catBtns.forEach(function (b) { b.classList.remove('active'); });
      this.classList.add('active');
      filterCourses();
    });
  });

  // Pre-select category from ?category=<slug> query param (used by homepage tiles + footer links)
  if (catBtns.length) {
    var qs = new URLSearchParams(window.location.search);
    var slug = qs.get('category');
    if (slug) {
      var match = null;
      catBtns.forEach(function (b) {
        if (b.dataset.slug === slug) match = b;
      });
      if (match) {
        catBtns.forEach(function (b) { b.classList.remove('active'); });
        match.classList.add('active');
        filterCourses();
        var sidebar = document.querySelector('.courses-sidebar');
        if (sidebar && sidebar.scrollIntoView) {
          setTimeout(function () { sidebar.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 50);
        }
      }
    }
  }

  var contactForm = document.getElementById('contact-form');
  var formPanel = document.getElementById('form-panel');
  var successPanel = document.getElementById('success-panel');

  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var data = new FormData(contactForm);
      var action = contactForm.getAttribute('action');
      var submitBtn = contactForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';
      }
      fetch(action, {
        method: 'POST',
        body: data,
        headers: { 'Accept': 'application/json' }
      }).then(function (res) {
        if (res.ok) {
          if (formPanel) formPanel.style.display = 'none';
          if (successPanel) successPanel.style.display = 'flex';
        } else {
          alert('Something went wrong. Please try again or email us directly at info@globalmakademi.com');
        }
      }).catch(function () {
        alert('Network error. Please try again or email us directly at info@globalmakademi.com');
      }).finally(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Send Message';
        }
      });
    });
  }

  var resetBtn = document.getElementById('reset-form');
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      if (formPanel) formPanel.style.display = 'block';
      if (successPanel) successPanel.style.display = 'none';
      if (contactForm) contactForm.reset();
    });
  }

  function handleLogoError(img) {
    var fallback = img.nextElementSibling;
    if (fallback && fallback.classList.contains('fallback')) {
      img.style.display = 'none';
      fallback.style.display = 'block';
    }
  }
  document.querySelectorAll('.logo-fallback').forEach(function (img) {
    img.addEventListener('error', function () { handleLogoError(this); });
    if (img.complete && img.naturalWidth === 0) handleLogoError(img);
  });

  var clearBtn = document.getElementById('clear-filters');
  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      if (searchInput) searchInput.value = '';
      catBtns.forEach(function (b) { b.classList.remove('active'); });
      var allBtn = document.querySelector('.cat-btn[data-category="All"]');
      if (allBtn) allBtn.classList.add('active');
      filterCourses();
    });
  }

  var params = new URLSearchParams(window.location.search);
  var subjectParam = params.get('subject');
  var subjectInput = document.getElementById('subject');
  if (subjectParam && subjectInput) {
    subjectInput.value = subjectParam;
  }

  // ===== LIGHTBOX (gallery) =====
  var lightbox = document.getElementById('lightbox');
  var lightboxImage = document.getElementById('lightbox-image');
  var lightboxCaption = document.getElementById('lightbox-caption');
  var photoTiles = document.querySelectorAll('.photo-tile');

  if (lightbox && lightboxImage && photoTiles.length) {
    var tilesArray = Array.prototype.slice.call(photoTiles);
    var currentIndex = -1;
    var lastFocused = null;

    function showAt(idx) {
      if (idx < 0) idx = tilesArray.length - 1;
      if (idx >= tilesArray.length) idx = 0;
      currentIndex = idx;
      var tile = tilesArray[idx];
      lightboxImage.src = tile.getAttribute('data-lightbox-src') || '';
      lightboxImage.alt = tile.getAttribute('data-lightbox-caption') || '';
      if (lightboxCaption) {
        lightboxCaption.textContent = tile.getAttribute('data-lightbox-caption') || '';
      }
    }

    function openLightbox(idx) {
      lastFocused = document.activeElement;
      showAt(idx);
      lightbox.hidden = false;
      document.body.style.overflow = 'hidden';
      var closeBtn = lightbox.querySelector('[data-lightbox-close]');
      if (closeBtn) closeBtn.focus();
    }

    function closeLightbox() {
      lightbox.hidden = true;
      lightboxImage.src = '';
      document.body.style.overflow = '';
      if (lastFocused && typeof lastFocused.focus === 'function') {
        lastFocused.focus();
      }
      currentIndex = -1;
    }

    tilesArray.forEach(function (tile, i) {
      tile.addEventListener('click', function () { openLightbox(i); });
    });

    var closeBtn = lightbox.querySelector('[data-lightbox-close]');
    var prevBtn = lightbox.querySelector('[data-lightbox-prev]');
    var nextBtn = lightbox.querySelector('[data-lightbox-next]');
    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (prevBtn) prevBtn.addEventListener('click', function () { showAt(currentIndex - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { showAt(currentIndex + 1); });

    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', function (e) {
      if (lightbox.hidden) return;
      if (e.key === 'Escape') { e.preventDefault(); closeLightbox(); }
      else if (e.key === 'ArrowLeft') { e.preventDefault(); showAt(currentIndex - 1); }
      else if (e.key === 'ArrowRight') { e.preventDefault(); showAt(currentIndex + 1); }
      else if (e.key === 'Tab') {
        // Simple focus trap among the lightbox controls
        var focusables = [closeBtn, prevBtn, nextBtn].filter(Boolean);
        if (!focusables.length) return;
        var idx = focusables.indexOf(document.activeElement);
        e.preventDefault();
        var nextIdx = e.shiftKey ? (idx <= 0 ? focusables.length - 1 : idx - 1) : ((idx + 1) % focusables.length);
        focusables[nextIdx].focus();
      }
    });
  }
});
