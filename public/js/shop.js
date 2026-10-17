/* ==========================================================================
   BeastWear.pk — js/shop.js
   Powers shop.html (all products + filters) and category.html.
   Filtering, sorting, availability, price range and pagination.
   ========================================================================== */

var BW_PAGE_SIZE = 12;
var bwShop = { q: "", cats: [], sizes: [], colors: [], max: 6000, avail: "all", sort: "featured", page: 1, sale: false, lockCat: null };

/* ---------- build the filter panel markup ---------- */
function bwAllSizes() {
  var out = [];
  BW_PRODUCTS.forEach(function (p) { p.sizes.forEach(function (s) { if (out.indexOf(s) < 0) out.push(s); }); });
  /* sort clothing sizes in a logical order, then numeric ones */
  var order = ["S", "M", "L", "XL", "XXL", "2-3Y", "4-5Y", "6-7Y", "8-9Y", "10-11Y", "28", "30", "32", "34", "36"];
  return out.sort(function (a, b) { return order.indexOf(a) - order.indexOf(b); });
}
function bwAllColors() {
  var out = [];
  BW_PRODUCTS.forEach(function (p) { p.colors.forEach(function (c) { if (out.indexOf(c) < 0) out.push(c); }); });
  return out;
}

function bwFilterHTML() {
  var s = bwShop;
  var catList = BW_CATEGORIES.map(function (c) {
    var n = bwByCategory(c.name).length;
    var checked = s.cats.indexOf(c.name) > -1 ? " checked" : "";
    return '<label class="chk"><input type="checkbox" data-f="cat" value="' + c.name + '"' + checked +
      (s.lockCat ? " disabled" : "") + '><span>' + c.name + '</span><em>' + n + "</em></label>";
  }).join("");

  var sizeList = bwAllSizes().map(function (v) {
    return '<label class="chk"><input type="checkbox" data-f="size" value="' + v + '"' +
      (s.sizes.indexOf(v) > -1 ? " checked" : "") + '><span>' + (v.indexOf("Y") > -1 ? v + " (kids)" : v) + "</span></label>";
  }).join("");

  var colorList = bwAllColors().map(function (c) {
    return '<label class="chk"><input type="checkbox" data-f="color" value="' + c + '"' +
      (s.colors.indexOf(c) > -1 ? " checked" : "") + '><span><i style="display:inline-block;width:11px;height:11px;border-radius:50%;background:' +
      (BW_COLORS[c] || "#111") + ';margin-right:7px;border:1px solid rgba(0,0,0,.15)"></i>' + c + "</span></label>";
  }).join("");

  return '' +
    '<div class="filter-block"><h4>Search</h4>' +
      '<input class="input" type="text" data-f="q" value="' + s.q + '" placeholder="Search in results..."></div>' +
    '<div class="filter-block"><h4>Category</h4>' + catList + '</div>' +
    '<div class="filter-block"><h4>Size</h4><div style="max-height:190px;overflow:auto">' + sizeList + '</div></div>' +
    '<div class="filter-block"><h4>Color</h4><div style="max-height:190px;overflow:auto">' + colorList + '</div></div>' +
    '<div class="filter-block"><h4>Max price</h4>' +
      '<div class="range-vals"><span>Rs 0</span><span id="price-out">' + bwFormat(s.max) + '</span></div>' +
      '<input type="range" data-f="max" min="800" max="6000" step="100" value="' + s.max + '"></div>' +
    '<div class="filter-block"><h4>Availability</h4>' +
      '<label class="chk"><input type="radio" name="avail" data-f="avail" value="all"' + (s.avail === "all" ? " checked" : "") + '><span>All items</span></label>' +
      '<label class="chk"><input type="radio" name="avail" data-f="avail" value="in"' + (s.avail === "in" ? " checked" : "") + '><span>In stock only</span></label>' +
      '<label class="chk"><input type="radio" name="avail" data-f="avail" value="out"' + (s.avail === "out" ? " checked" : "") + '><span>Out of stock</span></label></div>' +
    '<div class="filter-block"><h4>Offers</h4>' +
      '<label class="chk"><input type="checkbox" data-f="sale"' + (s.sale ? " checked" : "") + '><span>On sale only</span></label></div>' +
    '<div style="padding-top:16px"><button class="btn btn-outline btn-block btn-sm" data-clear-filters>Clear all filters</button></div>';
}

function bwRenderFilters() {
  document.querySelectorAll("[data-filters]").forEach(function (el) { el.innerHTML = bwFilterHTML(); });
}

/* ---------- read URL params (so nav links deep-link into filters) ---------- */
function bwShopReadParams() {
  var p = new URLSearchParams(location.search);
  bwShop.q = p.get("q") || "";
  bwShop.sale = p.get("sale") === "1";
  bwShop.sort = p.get("sort") || "featured";
  if (p.get("max")) bwShop.max = Number(p.get("max"));
  var cat = p.get("cat");
  if (cat) {
    var found = null;
    BW_CATEGORIES.forEach(function (c) { if (c.slug === cat) found = c; });
    if (found) { bwShop.cats = [found.name]; bwShop.lockCat = found; }
  }
}

/* ---------- apply state -> product list ---------- */
function bwShopResults() {
  var s = bwShop, q = s.q.toLowerCase().trim();
  var allowedIds = window.BW_SHOP_PRODUCT_IDS;
  var list = BW_PRODUCTS.filter(function (p) {
    if (Array.isArray(allowedIds) && allowedIds.indexOf(p.id) < 0) return false;
    if (s.cats.length && s.cats.indexOf(p.category) < 0) return false;
    if (s.sizes.length) {
      var hit = false;
      p.sizes.forEach(function (x) { if (s.sizes.indexOf(x) > -1) hit = true; });
      if (!hit) return false;
    }
    if (s.colors.length) {
      var ch = false;
      p.colors.forEach(function (x) { if (s.colors.indexOf(x) > -1) ch = true; });
      if (!ch) return false;
    }
    if (bwEffective(p) > s.max) return false;
    if (s.avail === "in" && p.stock <= 0) return false;
    if (s.avail === "out" && p.stock > 0) return false;
    if (s.sale && !(p.salePrice !== null && p.salePrice < p.price)) return false;
    if (q) {
      var hay = (p.name + " " + (p.sku || "") + " " + p.category).toLowerCase();
      if (hay.indexOf(q) < 0) return false;
    }
    return true;
  });

  if (s.sort === "price-asc") list.sort(function (a, b) { return bwEffective(a) - bwEffective(b); });
  else if (s.sort === "price-desc") list.sort(function (a, b) { return bwEffective(b) - bwEffective(a); });
  else if (s.sort === "newest") list.sort(function (a, b) { return b.added - a.added; });
  else if (s.sort === "popular") list.sort(function (a, b) { return b.popularity - a.popularity; });
  else if (s.sort === "rating") list.sort(function (a, b) { return b.rating - a.rating; });
  else list.sort(function (a, b) { return (b.bestSeller ? 1 : 0) - (a.bestSeller ? 1 : 0) || b.popularity - a.popularity; });

  return list;
}

function bwApply() {
  var grid = document.getElementById("shop-grid");
  if (!grid) return;
  var list = bwShopResults();
  var pages = Math.max(1, Math.ceil(list.length / BW_PAGE_SIZE));
  if (bwShop.page > pages) bwShop.page = pages;
  var slice = list.slice((bwShop.page - 1) * BW_PAGE_SIZE, bwShop.page * BW_PAGE_SIZE);

  var count = document.getElementById("shop-count");
  if (count) count.textContent = list.length + " product" + (list.length === 1 ? "" : "s");

  if (!list.length) {
    grid.innerHTML = '<div class="empty" style="grid-column:1/-1">' + BW_ICONS.filter +
      "<h3>No products match your filters</h3><p>Try removing a filter or two to see more results.</p>" +
      '<button class="btn btn-dark" data-clear-filters>Clear Filters</button></div>';
  } else {
    grid.innerHTML = slice.map(function (p) { return bwCardHTML(p); }).join("");
    bwSyncWishButtons();
  }
  bwRenderPager(pages);
  bwRenderChips();
  if (typeof bwObserveReveals === "function") bwObserveReveals();
}

function bwRenderPager(pages) {
  var box = document.getElementById("pager");
  if (!box) return;
  if (pages <= 1) { box.innerHTML = ""; return; }
  var h = "";
  for (var i = 1; i <= pages; i++) h += '<button type="button" data-page="' + i + '" class="' + (i === bwShop.page ? "active" : "") + '">' + i + "</button>";
  box.innerHTML = h;
}

function bwRenderChips() {
  var box = document.getElementById("active-chips");
  if (!box) return;
  var s = bwShop, chips = [];
  s.cats.forEach(function (c) { chips.push(["cat", c]); });
  s.sizes.forEach(function (c) { chips.push(["size", c]); });
  s.colors.forEach(function (c) { chips.push(["color", c]); });
  if (s.max < 6000) chips.push(["max", "Under " + bwFormat(s.max)]);
  if (s.avail !== "all") chips.push(["avail", s.avail === "in" ? "In stock" : "Out of stock"]);
  if (s.sale) chips.push(["sale", "On sale"]);
  if (s.q) chips.push(["q", '"' + s.q + '"']);

  box.innerHTML = chips.map(function (c) {
    return '<button class="chip active" data-chip="' + c[0] + '" data-val="' + c[1] + '">' + c[1] + " ✕</button>";
  }).join("") + (chips.length ? '<button class="chip" data-clear-filters>Clear all</button>' : "");
}

/* ---------- events ---------- */
document.addEventListener("change", function (e) {
  var el = e.target, f = el.getAttribute && el.getAttribute("data-f");
  if (!f) return;
  var s = bwShop;
  if (f === "cat") s.cats = toggle(s.cats, el.value);
  else if (f === "size") s.sizes = toggle(s.sizes, el.value);
  else if (f === "color") s.colors = toggle(s.colors, el.value);
  else if (f === "avail") s.avail = el.value;
  else if (f === "sale") s.sale = el.checked;
  else if (f === "max") s.max = Number(el.value);
  else if (f === "q") { s.q = el.value; }
  s.page = 1;
  bwApply(); bwRenderFilters();
  function toggle(arr, v) { var i = arr.indexOf(v); if (i > -1) arr.splice(i, 1); else arr.push(v); return arr; }
});

/* typing in the filter search box should not wait for a change event */
document.addEventListener("input", function (e) {
  if (e.target.getAttribute && e.target.getAttribute("data-f") === "max") {
    var out = document.getElementById("price-out");
    if (out) out.textContent = bwFormat(Number(e.target.value));
  }
});

document.addEventListener("click", function (e) {
  var pg = e.target.closest("[data-page]");
  if (pg) {
    bwShop.page = Number(pg.getAttribute("data-page"));
    bwApply();
    var g = document.getElementById("shop-grid");
    if (g) window.scrollTo({ top: g.offsetTop - 140, behavior: "smooth" });
    return;
  }
  var chip = e.target.closest("[data-chip]");
  if (chip) {
    var k = chip.getAttribute("data-chip"), v = chip.getAttribute("data-val"), s = bwShop;
    if (k === "cat") s.cats = s.cats.filter(function (x) { return x !== v; });
    if (k === "size") s.sizes = s.sizes.filter(function (x) { return x !== v; });
    if (k === "color") s.colors = s.colors.filter(function (x) { return x !== v; });
    if (k === "max") s.max = 6000;
    if (k === "avail") s.avail = "all";
    if (k === "sale") s.sale = false;
    if (k === "q") s.q = "";
    s.page = 1; bwApply(); bwRenderFilters();
  }
  if (e.target.closest("[data-clear-filters]")) {
    bwShop.cats = bwShop.lockCat ? [bwShop.lockCat.name] : [];
    bwShop.sizes = []; bwShop.colors = []; bwShop.max = 6000; bwShop.avail = "all";
    bwShop.sale = false; bwShop.q = ""; bwShop.page = 1;
    bwApply(); bwRenderFilters();
  }
  if (e.target.closest("[data-open-filters]")) {
    document.getElementById("filter-drawer").classList.add("open");
    document.getElementById("scrim").classList.add("open");
    document.body.classList.add("locked");
  }
  if (e.target.closest("[data-close-filters]")) {
    document.getElementById("filter-drawer").classList.remove("open");
    document.getElementById("scrim").classList.remove("open");
    document.body.classList.remove("locked");
  }
});

var sortEl = null;
document.addEventListener("change", function (e) {
  if (e.target.id === "sort-select") { bwShop.sort = e.target.value; bwShop.page = 1; bwApply(); }
});

/* ---------- boot for shop.html / category.html ---------- */
document.addEventListener("DOMContentLoaded", function () {
  var grid = document.getElementById("shop-grid");
  if (!grid) return;

  bwShopReadParams();
  bwRenderFilters();
  var sel = document.getElementById("sort-select");
  if (sel) sel.value = bwShop.sort;

  /* category page banner + heading */
  if (bwShop.lockCat) {
    var c = bwShop.lockCat;
    var t = document.getElementById("cat-title"); if (t) t.textContent = c.name;
    var d = document.getElementById("cat-desc"); if (d) d.textContent = c.desc;
    var i = document.getElementById("cat-banner-img"); if (i && c.image) i.src = c.image;
    var cr = document.getElementById("crumb-cat"); if (cr) cr.textContent = c.name;
    document.title = c.name + " — BeastWear.pk";
    /* sibling category chips */
    var chips = document.getElementById("cat-chips");
    if (chips) chips.innerHTML = BW_CATEGORIES.map(function (x) {
      return '<a class="chip ' + (x.slug === c.slug ? "active" : "") + '" href="/category?cat=' + encodeURIComponent(x.slug) + '">' + x.name + "</a>";
    }).join("");
  }

  bwApply();
});
