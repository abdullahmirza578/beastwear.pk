/* ==========================================================================
   BeastWear.pk — js/wishlist.js
   Saved products, stored in localStorage. Powers the heart buttons
   and the wishlist page.
   ========================================================================== */

var WISH_KEY = "bw_wishlist_v1";

function bwWishGet() {
  try { return JSON.parse(localStorage.getItem(WISH_KEY)) || []; }
  catch (e) { return []; }
}
function bwWishSave(list) {
  localStorage.setItem(WISH_KEY, JSON.stringify(list));
  bwUpdateWishBadge();
}
function bwWishHas(id) { return bwWishGet().indexOf(Number(id)) > -1; }

function bwWishToggle(id) {
  var p = bwProduct(id), list = bwWishGet();
  if (!p) return false;
  if (bwWishHas(id)) {
    list = list.filter(function (x) { return x !== Number(id); });
    bwWishSave(list); bwToast("Removed from wishlist", p.name, "ok");
    return false;
  }
  list.push(Number(id)); bwWishSave(list);
  bwToast("Saved to wishlist", p.name, "ok");
  return true;
}
function bwWishRemove(id) {
  bwWishSave(bwWishGet().filter(function (x) { return x !== Number(id); }));
}
function bwWishCount() { return bwWishGet().length; }

function bwUpdateWishBadge() {
  var n = bwWishCount();
  /* only the header icon badge is hidden/shown; text counters just update */
  document.querySelectorAll(".badge-count[data-wish-count]").forEach(function (el) {
    el.textContent = n; el.style.display = n > 0 ? "grid" : "none";
  });
  document.querySelectorAll("[data-wish-count]").forEach(function (el) { el.textContent = n; });
}

/* ---------- wishlist page (wishlist.html) ---------- */
function bwRenderWishlist() {
  var box = document.getElementById("wishlist-items");
  if (!box) return;
  var ids = bwWishGet().map(bwProduct).filter(Boolean);

  if (!ids.length) {
    box.innerHTML = '<div class="empty" style="grid-column:1/-1">' + BW_ICONS.heart +
      "<h3>Your wishlist is empty</h3><p>Tap the heart on any product to save it here for later.</p>" +
      '<a class="btn btn-dark" href="shop.html">Browse Products</a></div>';
    return;
  }
  box.innerHTML = ids.map(function (p) { return bwCardHTML(p); }).join("");
  bwSyncWishButtons();
}

/* Keep every heart on the page in sync with storage */
function bwSyncWishButtons() {
  document.querySelectorAll("[data-wish]").forEach(function (btn) {
    var active = bwWishHas(btn.getAttribute("data-wish"));
    btn.classList.toggle("active", active);
    btn.setAttribute("aria-pressed", active ? "true" : "false");
  });
}
