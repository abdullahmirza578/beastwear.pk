/* ==========================================================================
   BeastWear.pk — js/main.js
   Shared UI for every page: icons, mobile menu,
   search overlay, product cards, quick view, toasts, FAQ accordion.
   ========================================================================== */

/* ---------- 1. SVG ICON LIBRARY ---------- */
var BW_ICONS = {
  search: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>',
  heart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20s-7-4.4-7-9.3A4.1 4.1 0 0 1 12 8a4.1 4.1 0 0 1 7 2.7C19 15.6 12 20 12 20z"/></svg>',
  user: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.6"/><path d="M4.5 20c1.4-3.6 4-5.2 7.5-5.2S18.1 16.4 19.5 20"/></svg>',
  cart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h2.2l2.3 10h9.2l2.1-7.2H7.2"/><circle cx="10" cy="19.4" r="1.4"/><circle cx="17" cy="19.4" r="1.4"/></svg>',
  menu: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M4 7h16M4 12h16M4 17h16"/></svg>',
  close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M6 6l12 12M18 6L6 18"/></svg>',
  trash: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5h6v2M6 7l1 13h10l1-13"/></svg>',
  truck: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2 7h11v9H2zM13 10h4l3 3v3h-7z"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg>',
  shield: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3l7 3v5c0 4.4-3 7.9-7 10-4-2.1-7-5.6-7-10V6z"/><path d="M9 12l2 2 4-4"/></svg>',
  refresh: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 12a8 8 0 1 1-2.6-5.9"/><path d="M20 4v5h-5"/></svg>',
  tag: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3h9v9l-9 9-9-9z"/><circle cx="16.5" cy="7.5" r="1.3"/></svg>',
  cash: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/></svg>',
  check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 12.5l5 5L20 6.5"/></svg>',
  plus: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 5v14M5 12h14"/></svg>',
  filter: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M7 12h10M10 18h4"/></svg>',
  box: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 8l9-5 9 5v8l-9 5-9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/></svg>',
  phone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 3h3l2 5-2.5 1.5a11 11 0 0 0 5 5L14 12l5 2v3a2 2 0 0 1-2.2 2A16 16 0 0 1 3 5.2A2 2 0 0 1 5 3z"/></svg>',
  mail: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>',
  pin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg>',
  clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>',
  whatsapp: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.6-6.1c-.3-.1-1.5-.7-1.7-.8s-.4-.1-.6.2-.7.8-.8 1-.3.2-.6.1a6.7 6.7 0 0 1-2-1.2 7.4 7.4 0 0 1-1.4-1.7c-.1-.3 0-.4.1-.6l.4-.5a1.6 1.6 0 0 0 .3-.4.5.5 0 0 0 0-.4c0-.1-.6-1.4-.8-1.9s-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3A2.9 2.9 0 0 0 6.6 9a5 5 0 0 0 1.1 2.7 11.4 11.4 0 0 0 4.4 3.9 4.9 4.9 0 0 0 3.1.5 2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .1-1.3c-.1-.1-.3-.2-.5-.3z"/></svg>',
  instagram: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17" cy="7" r="1" fill="currentColor"/></svg>',
  facebook: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.5l.5-3H13v-2c0-.6.4-1 1-1z"/></svg>',
  tiktok: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 3h-2.5v11.2a2.6 2.6 0 1 1-2.1-2.5V9.1A5.4 5.4 0 1 0 16 14.4V9.8a6.6 6.6 0 0 0 4 1.3V8.6A4.1 4.1 0 0 1 16 3z"/></svg>'
};

/* ---------- 2. TOAST NOTIFICATIONS ---------- */
function bwToast(title, msg, type) {
  var wrap = document.getElementById("bw-toasts");
  if (!wrap) { wrap = document.createElement("div"); wrap.className = "toast-wrap"; wrap.id = "bw-toasts"; document.body.appendChild(wrap); }
  var el = document.createElement("div");
  el.className = "toast " + (type || "ok");
  el.innerHTML = (type === "err" ? BW_ICONS.close : BW_ICONS.check) + "<div><b>" + title + "</b>" + (msg ? "<span>" + msg + "</span>" : "") + "</div>";
  wrap.appendChild(el);
  requestAnimationFrame(function () { el.classList.add("show"); });
  setTimeout(function () {
    el.classList.add("hide");
    setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 320);
  }, 2600);
}

/* tiny helper used by several page scripts */
function setText(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }

/* ---------- 3. PRODUCT CARD (used on every listing page) ---------- */
function bwCardHTML(p, opts) {
  opts = opts || {};
  var off = bwDiscount(p), out = p.stock <= 0, alt = p.images[1] || p.image;
  var badge = out ? '<span class="badge out">Sold Out</span>'
    : off > 0 ? '<span class="badge sale">-' + off + "%</span>"
    : p.isNew ? '<span class="badge new">New</span>' : "";

  return '<article class="card">' +
    '<div class="card-media">' + badge +
      '<button class="wish-btn" data-wish="' + p.id + '" aria-label="Add ' + p.name + ' to wishlist">' + BW_ICONS.heart + '</button>' +
      '<a href="product.html?id=' + p.id + '" aria-label="' + p.name + '">' +
        '<img class="main" src="' + p.image + '" alt="' + p.name + '" loading="lazy">' +
        '<img class="alt" src="' + alt + '" alt="" loading="lazy" aria-hidden="true">' +
      '</a>' +
      '<button class="quick-btn" data-quick="' + p.id + '">Quick View</button>' +
    '</div>' +
    '<div class="card-body">' +
      '<span class="card-cat">' + p.category + '</span>' +
      '<h3 class="card-title"><a href="product.html?id=' + p.id + '">' + p.name + '</a></h3>' +
      '<div class="row-center" style="gap:7px">' + bwStars(p.rating) + '<span class="card-cat">(' + p.reviews + ')</span></div>' +
      '<div class="card-foot">' +
        '<div class="price"><b>' + bwFormat(bwEffective(p)) + '</b>' + (off ? " <s>" + bwFormat(p.price) + "</s>" : "") + '</div>' +
        '<button class="card-add" data-add="' + p.id + '" aria-label="Add to cart"' + (out ? " disabled" : "") + '>' + BW_ICONS.cart + '</button>' +
      '</div>' +
      '<div class="card-sizes">' + p.sizes.join(" · ") + '</div>' +
    '</div></article>';
}

function bwRenderCards(gridId, list, cols) {
  var el = document.getElementById(gridId);
  if (!el) return;
  if (cols) el.className = "product-grid " + cols;
  el.innerHTML = list.map(function (p) { return bwCardHTML(p); }).join("");
  bwSyncWishButtons();
}

function bwSkeleton(gridId, n, cls) {
  var el = document.getElementById(gridId);
  if (!el) return;
  if (cls) el.className = "product-grid " + cls;
  var h = "";
  for (var i = 0; i < (n || 8); i++) {
    h += '<div><div class="skeleton" style="aspect-ratio:4/5"></div>' +
         '<div class="skeleton" style="height:12px;margin-top:12px;width:60%"></div>' +
         '<div class="skeleton" style="height:12px;margin-top:8px;width:40%"></div></div>';
  }
  el.innerHTML = h;
}

/* ---------- 4. BOOT: wire global UI ---------- */

/* ---------- 5. BOOT: inject layout, wire global UI ---------- */
(function boot() {
  var here = location.pathname.replace(/^\/|\/$/g, "") || "home";
  document.querySelectorAll(".main-nav a").forEach(function (a) {
    var t = a.getAttribute("data-nav");
    if (t === here) a.classList.add("active");
    if (t === "new" && here === "shop" && location.search.indexOf("sort=new") > -1) a.classList.add("active");
    if (t === "sale" && here === "shop" && location.search.indexOf("sale=1") > -1) a.classList.add("active");
  });

  /* current year */
  var y = document.getElementById("bw-year");
  if (y) y.textContent = new Date().getFullYear();

  var themeToggle = document.getElementById("customer-theme-toggle");
  function updateThemeToggle(theme) {
    if (!themeToggle) return;
    var nextTheme = theme === "dark" ? "light" : "dark";
    themeToggle.setAttribute("aria-label", "Switch to " + nextTheme + " mode");
    themeToggle.setAttribute("title", "Switch to " + nextTheme + " mode");
  }
  updateThemeToggle(document.documentElement.dataset.theme || "light");
  if (themeToggle) themeToggle.addEventListener("click", function () {
    var theme = document.documentElement.dataset.theme === "dark" ? "light" : "dark";
    document.documentElement.dataset.theme = theme;
    updateThemeToggle(theme);
    try {
      localStorage.setItem("bw_customer_theme", theme);
    } catch (error) {
      bwToast("Theme changed", "Your browser did not allow saving this preference.", "err");
    }
  });

  /* --- mobile drawer --- */
  var drawer = document.getElementById("nav-drawer"), scrim = document.getElementById("scrim");
  function openDrawer() { drawer.classList.add("open"); scrim.classList.add("open"); document.body.classList.add("locked"); }
  function closeDrawer() { drawer.classList.remove("open"); scrim.classList.remove("open"); document.body.classList.remove("locked"); }
  var no = document.getElementById("nav-open");
  if (no) no.addEventListener("click", openDrawer);
  document.addEventListener("click", function (e) {
    if (e.target.closest("[data-close-drawer]") || e.target === scrim) closeDrawer();
    if (e.target.closest("[data-close-modal]")) bwCloseModal();
  });
  var mc = document.getElementById("m-cats");
  if (mc) mc.addEventListener("click", function () { document.getElementById("m-cats-list").classList.toggle("hide"); });

  /* --- search overlay --- */
  var so = document.getElementById("search-overlay"), si = document.getElementById("search-input");
  function openSearch() { so.classList.add("open"); document.body.classList.add("locked"); setTimeout(function () { si.focus(); }, 260); }
  function closeSearch() { so.classList.remove("open"); document.body.classList.remove("locked"); }
  var sOpen = document.getElementById("search-open");
  if (sOpen) sOpen.addEventListener("click", openSearch);
  var sClose = document.getElementById("search-close");
  if (sClose) sClose.addEventListener("click", closeSearch);
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") { closeSearch(); closeDrawer(); bwCloseModal(); }
    if (e.key === "/" && document.activeElement.tagName !== "INPUT") { e.preventDefault(); openSearch(); }
  });
  if (si) si.addEventListener("input", function () {
    if (typeof bwSuggest === "function") bwSuggest(si.value, document.getElementById("search-suggest"));
  });
  if (si) si.addEventListener("keydown", function (e) {
    if (e.key === "Enter" && si.value.trim()) location.href = "/search?q=" + encodeURIComponent(si.value.trim());
  });

  /* --- global product actions (add to cart / wishlist / quick view) --- */
  document.addEventListener("click", function (e) {
    var add = e.target.closest("[data-add]");
    if (add) { e.preventDefault(); bwCartAdd(add.getAttribute("data-add")); return; }

    var w = e.target.closest("[data-wish]");
    if (w) {
      e.preventDefault();
      var nowActive = bwWishToggle(w.getAttribute("data-wish"));
      w.classList.toggle("active", nowActive);
      return;
    }
    var q = e.target.closest("[data-quick]");
    if (q) { e.preventDefault(); bwQuickView(q.getAttribute("data-quick")); }
  });

  /* --- FAQ accordion (faq.html + product page) --- */
  document.addEventListener("click", function (e) {
    var h = e.target.closest(".acc-head");
    if (!h) return;
    var acc = h.parentNode, body = acc.querySelector(".acc-body"), open = acc.classList.contains("open");
    /* optional single-open behaviour within the same group */
    var group = acc.parentNode;
    if (group && group.hasAttribute("data-single")) {
      group.querySelectorAll(".acc.open").forEach(function (a) {
        if (a !== acc) { a.classList.remove("open"); a.querySelector(".acc-body").style.maxHeight = null; }
      });
    }
    acc.classList.toggle("open", !open);
    body.style.maxHeight = open ? null : body.scrollHeight + "px";
  });

  /* --- reveal on scroll --- */
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add("in"); io.unobserve(en.target); } });
  }, { threshold: .12 });
  function bwObserveReveals() { document.querySelectorAll(".reveal:not(.in)").forEach(function (el) { io.observe(el); }); }
  bwObserveReveals();
  window.bwObserveReveals = bwObserveReveals;

  bwUpdateCartBadge();
  bwUpdateWishBadge();
})();

/* ---------- 6. QUICK VIEW MODAL ---------- */
var bwQvState = { id: null, size: null, color: null, qty: 1 };

function bwOpenModal() {
  document.getElementById("quick-modal").classList.add("open");
  document.body.classList.add("locked");
}
function bwCloseModal() {
  var m = document.getElementById("quick-modal");
  if (m) { m.classList.remove("open"); if (!document.getElementById("nav-drawer").classList.contains("open")) document.body.classList.remove("locked"); }
}
document.addEventListener("click", function (e) {
  var m = document.getElementById("quick-modal");
  if (m && e.target === m) bwCloseModal();
});

function bwQuickView(id) {
  var p = bwProduct(id);
  if (!p) return;
  bwQvState = { id: p.id, size: p.sizes[0], color: p.colors[0], qty: 1 };
  var off = bwDiscount(p), out = p.stock <= 0;

  document.getElementById("quick-body").innerHTML =
    '<div class="qv">' +
      '<div><img src="' + p.images[0] + '" alt="' + p.name + '" style="border-radius:12px;aspect-ratio:4/5;object-fit:cover;width:100%"></div>' +
      '<div>' +
        '<span class="card-cat">' + p.category + '</span>' +
        '<h2 style="font-size:24px;margin:6px 0 8px">' + p.name + '</h2>' +
        '<div class="row-center" style="gap:8px">' + bwStars(p.rating) + '<span class="hint">' + p.rating + " · " + p.reviews + ' reviews</span></div>' +
        '<div class="pdp-price"><b>' + bwFormat(bwEffective(p)) + '</b>' +
          (off ? "<s>" + bwFormat(p.price) + "</s>" + '<span class="off-tag">-' + off + '%</span>' : "") + '</div>' +
        '<p class="hint" style="margin-bottom:0">' + p.desc.slice(0, 130) + '...</p>' +
        '<div class="opt-label">Size <span>' + bwQvState.size + '</span></div>' +
        '<div class="size-list">' + p.sizes.map(function (s) {
          return '<button type="button" data-qv-size="' + s + '" class="' + (s === bwQvState.size ? "active" : "") + '">' + s + "</button>";
        }).join("") + '</div>' +
        '<div class="opt-label">Color</div><div class="chips">' + p.colors.map(function (c) {
          return '<button type="button" class="swatch ' + (c === bwQvState.color ? "active" : "") + '" data-qv-color="' + c + '"><i style="background:' + (BW_COLORS[c] || "#111") + '"></i>' + c + "</button>";
        }).join("") + '</div>' +
        '<div class="pdp-actions">' +
          '<button class="btn btn-dark" data-qv-add' + (out ? " disabled" : "") + '>' + (out ? "Sold Out" : "Add to Cart") + '</button>' +
          '<a class="btn btn-outline" href="product.html?id=' + p.id + '">Full Details</a>' +
        '</div>' +
      '</div></div>';

  bwOpenModal();

  var body = document.getElementById("quick-body");
  body.querySelectorAll("[data-qv-size]").forEach(function (b) {
    b.addEventListener("click", function () {
      body.querySelectorAll("[data-qv-size]").forEach(function (x) { x.classList.remove("active"); });
      b.classList.add("active"); bwQvState.size = b.getAttribute("data-qv-size");
      body.querySelector(".opt-label span").textContent = bwQvState.size;
    });
  });
  body.querySelectorAll("[data-qv-color]").forEach(function (b) {
    b.addEventListener("click", function () {
      body.querySelectorAll("[data-qv-color]").forEach(function (x) { x.classList.remove("active"); });
      b.classList.add("active"); bwQvState.color = b.getAttribute("data-qv-color");
    });
  });
  var addBtn = body.querySelector("[data-qv-add]");
  if (addBtn) addBtn.addEventListener("click", function () {
    if (bwCartAdd(bwQvState.id, bwQvState.size, bwQvState.color, 1)) bwCloseModal();
  });
}
