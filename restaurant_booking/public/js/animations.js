/**
 * animations.js — All GSAP animations for Restaurant Booking (La Table d'Or)
 * GSAP v1.11.1 — TweenMax / TimelineMax
 *
 * Philosophy:
 *  - Progressive enhancement: all content visible without JS.
 *  - Non-blocking: never hide critical form fields or error messages permanently.
 *  - Reversible: transitions can be rewound where applicable.
 */

(function () {
  "use strict";

  // ─── Utility: check element exists before animating ───────────────────────
  function $(sel) { return document.querySelector(sel); }
  function $$(sel) { return Array.from(document.querySelectorAll(sel)); }

  // ─── 1. LOGIN PAGE — card fade-in ─────────────────────────────────────────
  var loginCard = $('#loginCard');
  if (loginCard) {
    TweenMax.from(loginCard, 0.6, { opacity: 0, y: -30, ease: Power2.easeOut });
  }

  // ─── 2. FLASH MESSAGES — stagger in, auto-dismiss ─────────────────────────
  var flashes = $$('.flash-message');
  if (flashes.length) {
    TweenMax.staggerFrom(flashes, 0.4, { opacity: 0, x: 40, ease: Back.easeOut }, 0.15);
    // Auto-dismiss after 4 seconds
    flashes.forEach(function (el) {
      TweenMax.to(el, 0.4, { delay: 4, opacity: 0, x: 40, onComplete: function () { el.remove(); } });
    });
  }

  // ─── 3. LIST ITEMS — stagger reveal ───────────────────────────────────────
  var listItems = $$('.list-item, .card');
  if (listItems.length) {
    TweenMax.staggerFrom(listItems, 0.5, { opacity: 0, y: 20, ease: Power1.easeOut }, 0.08);
  }

  // ─── 4. PAGE HEADER — slide down ──────────────────────────────────────────
  var pageHeader = $('#pageHeader');
  if (pageHeader) {
    TweenMax.from(pageHeader, 0.5, { opacity: 0, y: -20, ease: Power2.easeOut });
  }

  // ─── 5. FORM APPEAR — fade + scale ────────────────────────────────────────
  var mainForm = $('#mainForm');
  if (mainForm) {
    TweenMax.from(mainForm, 0.5, { opacity: 0, scale: 0.97, ease: Power2.easeOut });
  }

  // ─── 6. 403 / ERROR PAGE ──────────────────────────────────────────────────
  var errorCard = $('#errorCard');
  if (errorCard) {
    var tl = new TimelineMax();
    tl.from(errorCard, 0.4, { opacity: 0, scale: 0.8, ease: Back.easeOut })
      .from(errorCard.querySelector('h1'), 0.3, { opacity: 0, y: -10 }, '-=0.1');
  }

  // ─── RESTAURANT-SPECIFIC ANIMATIONS ───────────────────────────────────────

  // ─── 7. DASHBOARD — admin stats + welcome header timeline ─────────────────
  var dashHeader = $('#dashHeader');
  if (dashHeader) {
    var dashTl = new TimelineMax();
    dashTl.from(dashHeader, 0.5, { opacity: 0, y: -30, ease: Power3.easeOut })
          .staggerFrom($$('.stat-card'), 0.4, { opacity: 0, scale: 0.9 }, 0.1, '-=0.2');
  }

  // ─── 8. RESERVATION FORM — sequential field reveal timeline ────────────────
  var resForm = $('#reservationForm');
  if (resForm) {
    var resTl = new TimelineMax();
    resTl.from('#dateField',  0.4, { opacity: 0, x: -20, ease: Power2.easeOut })
         .from('#slotField',  0.4, { opacity: 0, x: -20 }, '-=0.2')
         .from('#guestField', 0.4, { opacity: 0, x: -20 }, '-=0.2')
         .from('#submitBtn',  0.3, { opacity: 0, scale: 0.9, ease: Back.easeOut }, '-=0.1');
  }

  // ─── 9. CAPACITY BAR — animated fill with percentage ──────────────────────
  var capacityBar = $('#capacityBar');
  if (capacityBar) {
    var pct = parseFloat(capacityBar.dataset.pct || 0);
    TweenMax.to(capacityBar, 1.2, { width: pct + '%', ease: Power2.easeOut });
  }

  // ─── 10. CONFIRMATION PAGE — icon + details reveal ────────────────────────
  var confirmPage = $('#confirmPage');
  if (confirmPage) {
    var confTl = new TimelineMax();
    confTl.from('#confIcon',  0.5, { scale: 0, ease: Back.easeOut(2) })
          .staggerFrom($$('.conf-detail'), 0.3, { opacity: 0, y: 10 }, 0.1, '-=0.2');
  }

  // ─── 11. FILTER BAR — slide in ────────────────────────────────────────────
  var filterBar = $('#filterBar');
  if (filterBar) {
    TweenMax.from(filterBar, 0.4, { opacity: 0, x: -20, ease: Power2.easeOut });
  }

  // ─── 12. STATUS BADGES — scale bounce ─────────────────────────────────────
  var statusBadge = $('#statusBadge');
  if (statusBadge) {
    TweenMax.from(statusBadge, 0.5, { scale: 0, ease: Back.easeOut(1.7) });
  }

  // ─── 13. STATUS CHANGE FEEDBACK — admin action confirmation ───────────────
  var statusFeedback = $('#statusFeedback');
  if (statusFeedback) {
    TweenMax.from(statusFeedback, 0.4, { opacity: 0, y: -15, ease: Power2.easeOut });
  }

})();
