/* ==========================================================================
   BeastWear.pk — js/search.js
   Searches products by NAME, CATEGORY and DESCRIPTION.
   Used by the header search overlay (live suggestions) and search.html.
   ========================================================================== */

/* Return database-backed active products matching a query. */
function bwSearchProducts(query) {
  var q = (query || "").toLowerCase().trim();
  if (!q) return [];
  /* Keep live header suggestions in step with the server-side search fields. */
  var words = q.split(/\s+/);

  return BW_PRODUCTS.filter(function (p) {
    var hay = (p.name + " " + (p.sku || "") + " " + p.category).toLowerCase();
    return words.every(function (w) { return hay.indexOf(w) > -1; });
  });
}

/* Live suggestions for the header search overlay */
function bwSuggest(query, box) {
  if (!box) return;
  var q = (query || "").trim();
  if (q.length < 2) { box.innerHTML = ""; return; }
  var res = bwSearchProducts(q);

  box.innerHTML =
    '<div class="row-between" style="margin-bottom:6px"><span class="eyebrow" style="margin:0">' +
      res.length + ' result' + (res.length === 1 ? "" : "s") + ' for "' + q + '"</span>' +
      '<a class="link-arrow" href="/search?q=' + encodeURIComponent(q) + '">View all</a></div>' +
    (res.length ? res.slice(0, 5).map(function (p) {
      return '<a class="suggest-item" href="' + (p.url || '/shop') + '">' +
        '<img src="' + p.image + '" alt="' + p.name + '" loading="lazy">' +
        '<span><b>' + p.name + '</b><small>' + p.category + ' · ' + p.sizes.join("/") + '</small></span>' +
        '<span class="p">' + bwFormat(bwEffective(p)) + '</span></a>';
    }).join("")
      : '<p class="hint" style="padding:12px 0">No products matched. Try "hoodie", "oversized", "cargo" or "kids".</p>');
}

/* ---------- search.html ---------- */
function bwSearchPageInit() {
  var grid = document.getElementById("search-grid");
  if (!grid) return;

  var params = new URLSearchParams(location.search);
  var q = params.get("q") || "";
  var input = document.getElementById("page-search-input");
  if (input) input.value = q;

  var title = document.getElementById("search-term");
  if (title) title.textContent = q ? '"' + q + '"' : "all products";

  var resultIds = window.BW_SEARCH_PRODUCT_IDS || [];
  var res = BW_PRODUCTS.filter(function (product) { return resultIds.indexOf(product.id) !== -1; });

  var count = document.getElementById("search-count");
  if (count) count.textContent = (window.BW_SEARCH_RESULT_COUNT || res.length) + " product" +
    ((window.BW_SEARCH_RESULT_COUNT || res.length) === 1 ? "" : "s") + " found";

  if (!res.length) {
    grid.innerHTML = '<div class="empty" style="grid-column:1/-1">' + BW_ICONS.search +
      "<h3>No results found</h3><p>We could not find anything for <strong>" + q +
      '</strong>. Check the spelling or browse the full collection.</p>' +
      '<a class="btn btn-dark" href="/shop">Browse All Products</a></div>';
  } else {
    grid.innerHTML = res.map(function (p) { return bwCardHTML(p); }).join("");
    bwSyncWishButtons();
  }

}

document.addEventListener("DOMContentLoaded", bwSearchPageInit);
