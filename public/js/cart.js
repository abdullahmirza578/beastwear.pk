/* ==========================================================================
   BeastWear.pk — js/cart.js
   Cart logic. Everything is stored in localStorage so the cart survives
   page reloads without a backend.
   ========================================================================== */

var CART_KEY = "bw_cart_v1";
var PROMO_KEY = "bw_promo_v1";
localStorage.removeItem(PROMO_KEY);

/* ---------- storage helpers ---------- */
function bwCartGet() {
  try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
  catch (e) { return []; }
}
function bwCartSave(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  bwUpdateCartBadge();
}

/* A unique line is defined by product + size + color */
function bwLineKey(id, size, color) { return id + "|" + size + "|" + color; }

async function bwCartAdd(id, size, color, qty, designId) {
  var p = bwProduct(id);
  if (!p) return false;
  var options = p.optionConfig || { size: p.sizes.length > 0, color: p.colors.length > 0, design: false };
  qty = qty || 1;
  size = options.size ? size : null;
  color = options.color ? color : null;
  designId = options.design ? designId : null;

  if (options.size && !size) {
    bwToast("Choose a size", "Select a size before adding this product to your cart.", "err");
    return false;
  }
  if (options.color && !color) {
    bwToast("Choose a color", "Select a color before adding this product to your cart.", "err");
    return false;
  }
  if (options.design && !designId) {
    bwToast("Choose a design", "Select a design before adding this product to your cart.", "err");
    return false;
  }

  if (p.stock <= 0) { bwToast("Out of stock", p.name + " is currently unavailable.", "err"); return false; }

  if (!window.BW_IS_AUTHENTICATED) {
    location.href = "/login";
    return false;
  }

  var variant = (p.variants || []).find(function (item) {
    return (!options.size || item.size === size) && (!options.color || item.color === color);
  });
  if ((options.size || options.color) && !variant) {
    bwToast("Unavailable option", "That option combination is not available.", "err");
    return false;
  }

  var payload = new URLSearchParams({
    product_id: String(p.id),
    quantity: String(qty),
    _token: document.querySelector('meta[name="csrf-token"]').content
  });
  if (size) payload.set("size", size);
  if (color) payload.set("color", color);
  if (designId) payload.set("design_image_id", String(designId));
  try {
    var response = await fetch("/cart/items", {
      method: "POST",
      headers: { "Accept": "application/json", "Content-Type": "application/x-www-form-urlencoded" },
      body: payload.toString()
    });
    if (!response.ok) {
      var errorData = await response.json().catch(function () { return {}; });
      var firstError = errorData.errors ? Object.values(errorData.errors)[0][0] : null;
      throw new Error(firstError || errorData.message || "Could not add this product to your cart.");
    }
    if (window.BW_SERVER_CART_COUNT !== null) window.BW_SERVER_CART_COUNT += Number(qty);
    bwUpdateCartBadge();
    var design = (p.designs || []).find(function (item) { return String(item.id) === String(designId); });
    bwToast("Added to cart", p.name + (design ? " · " + design.name : "") + (size ? " · " + size : "") + (color ? " · " + color : ""), "ok");
    return true;
  } catch (error) {
    bwToast("Cart update failed", error.message, "err");
    return false;
  }
}

function bwCartUpdate(key, qty) {
  var cart = bwCartGet();
  cart = cart.map(function (l) { if (l.key === key) l.qty = Math.max(1, Math.min(20, qty)); return l; });
  bwCartSave(cart);
}
function bwCartRemove(key) {
  bwCartSave(bwCartGet().filter(function (l) { return l.key !== key; }));
}
function bwCartClear() { bwCartSave([]); }

function bwCartCount() { return bwCartGet().reduce(function (s, l) { return s + l.qty; }, 0); }

function bwCartLines() {
  /* joins cart lines with the full product object */
  return bwCartGet().map(function (l) {
    var p = bwProduct(l.id);
    return p ? { key: l.key, product: p, size: l.size, color: l.color, qty: l.qty,
                 unit: bwEffective(p), lineTotal: bwEffective(p) * l.qty } : null;
  }).filter(Boolean);
}

function bwCartSubtotal() { return bwCartLines().reduce(function (s, l) { return s + l.lineTotal; }, 0); }

/* Delivery: free over Rs 3,000, otherwise Rs 199 (Pakistan-wide) */
var BW_DELIVERY_FEE = 199;
var BW_FREE_OVER = 3000;
function bwDeliveryFee(subtotal) {
  subtotal = subtotal === undefined ? bwCartSubtotal() : subtotal;
  if (subtotal <= 0) return 0;
  return subtotal >= BW_FREE_OVER ? 0 : BW_DELIVERY_FEE;
}

/* Coupon discounts are validated by the checkout endpoint against the database. */
var BW_PROMOS = [];
function bwPromoActive() {
  try { return JSON.parse(localStorage.getItem(PROMO_KEY)); } catch (e) { return null; }
}
function bwPromoApply(code) {
  code = (code || "").trim().toUpperCase();
  var promo = null;
  for (var i = 0; i < BW_PROMOS.length; i++) if (BW_PROMOS[i].code === code) promo = BW_PROMOS[i];
  if (!promo) { bwToast("Invalid code", "That promo code does not exist.", "err"); return false; }
  var sub = bwCartSubtotal();
  if (sub < promo.min) { bwToast("Not eligible", "Spend at least " + bwFormat(promo.min) + " to use " + code + ".", "err"); return false; }
  localStorage.setItem(PROMO_KEY, JSON.stringify(promo));
  bwToast("Promo applied", code + " — " + promo.label, "ok");
  bwRenderCart(); if (typeof bwRenderSummary === "function") bwRenderSummary();
  return true;
}
function bwPromoRemove() { localStorage.removeItem(PROMO_KEY); }

function bwDiscountAmount(subtotal) {
  var promo = bwPromoActive();
  if (!promo) return 0;
  if (promo.type === "percent") return Math.round(subtotal * promo.value / 100);
  if (promo.type === "flat") return Math.min(promo.value, subtotal);
  return 0;
}
function bwCartTotal() {
  var sub = bwCartSubtotal();
  return Math.max(0, sub - bwDiscountAmount(sub)) + bwDeliveryFee(sub);
}

/* ---------- header cart badge ---------- */
function bwUpdateCartBadge() {
  var n = window.BW_SERVER_CART_COUNT === null ? bwCartCount() : window.BW_SERVER_CART_COUNT;
  /* header icon badge (only these get hidden/shown) */
  document.querySelectorAll("[data-cart-count]").forEach(function (el) {
    el.textContent = n; el.style.display = n > 0 ? "grid" : "none";
  });
  /* text mentions on cart / checkout pages: only update the number */
  document.querySelectorAll("[data-cart-label]").forEach(function (el) { el.textContent = n; });
}

/* ---------- cart page rendering (cart.html) ---------- */
function bwRenderCart() {
  var box = document.getElementById("cart-items");
  if (!box) return;
  var lines = bwCartLines();

  if (!lines.length) {
    box.innerHTML =
      '<div class="empty">' + BW_ICONS.cart +
      "<h3>Your cart is empty</h3><p>Looks like you have not added anything yet. Explore the latest BeastWear drops.</p>" +
      '<a class="btn btn-dark" href="shop.html">Start Shopping</a></div>';
    var sum = document.getElementById("cart-summary");
    if (sum) sum.style.display = "none";
    return;
  }
  var sum2 = document.getElementById("cart-summary");
  if (sum2) sum2.style.display = "";

  box.innerHTML = lines.map(function (l) {
    var p = l.product;
    return '<div class="cart-row">' +
      '<a class="cart-thumb" href="product.html?id=' + p.id + '"><img src="' + p.image + '" alt="' + p.name + '" loading="lazy"></a>' +
      '<div class="cart-meta">' +
        '<div class="card-cat">' + p.category + '</div>' +
        '<h4><a href="product.html?id=' + p.id + '">' + p.name + '</a></h4>' +
        '<div class="vars">Size: <strong>' + l.size + '</strong> &nbsp;·&nbsp; Color: <strong>' + l.color + '</strong></div>' +
        '<div class="vars" style="margin-top:6px">' + bwFormat(l.unit) + ' each</div>' +
      '</div>' +
      '<div class="cart-side">' +
        '<div class="qty-box">' +
          '<button type="button" data-dec="' + l.key + '" aria-label="Decrease quantity">−</button>' +
          '<input type="text" value="' + l.qty + '" data-qty="' + l.key + '" readonly aria-label="Quantity">' +
          '<button type="button" data-inc="' + l.key + '" aria-label="Increase quantity">+</button>' +
        '</div>' +
        '<div class="price"><b>' + bwFormat(l.lineTotal) + '</b></div>' +
        '<button class="remove-btn" data-remove="' + l.key + '">' + BW_ICONS.trash + ' Remove</button>' +
      '</div></div>';
  }).join("");

  bwRenderSummary();
}

/* ---------- order summary (cart + checkout) ---------- */
function bwRenderSummary() {
  var sub = bwCartSubtotal(), disc = bwDiscountAmount(sub), ship = bwDeliveryFee(sub), total = bwCartTotal();
  var set = function (id, val) { var el = document.getElementById(id); if (el) el.textContent = val; };
  set("sum-subtotal", bwFormat(sub));
  set("sum-delivery", ship === 0 ? "FREE" : bwFormat(ship));
  var dEl = document.getElementById("sum-discount");
  if (dEl) { dEl.textContent = disc > 0 ? "− " + bwFormat(disc) : bwFormat(0); }
  set("sum-total", bwFormat(total));
  var promo = bwPromoActive(), promoBox = document.getElementById("promo-active");
  if (promoBox) {
    promoBox.innerHTML = promo
      ? '<span class="chip active">' + promo.code + " · " + promo.label +
        ' <button type="button" data-promo-remove style="margin-left:6px;color:#fff;text-decoration:underline">remove</button></span>'
      : '<span class="hint">Try <strong>BW10</strong>, <strong>BEAST500</strong> or <strong>FREESHIP</strong></span>';
  }
  var line = document.getElementById("sum-discount-row");
  if (line) line.style.display = disc > 0 ? "flex" : "none";
  var cnt = document.getElementById("sum-count");
  if (cnt) cnt.textContent = bwCartCount();
  var free = document.getElementById("free-note");
  if (free) free.textContent = sub >= BW_FREE_OVER
      ? "You have unlocked free delivery."
      : "Add " + bwFormat(BW_FREE_OVER - sub) + " more for free delivery.";
}

/* ---------- WhatsApp order message ---------- */
/* Change this number to the brand's real WhatsApp number (country code, no +) */
var BW_WHATSAPP = "923001234567";

function bwBuildOrderMessage(order) {
  var lines = [];
  lines.push("*NEW ORDER — BeastWear.pk*");
  lines.push("");
  lines.push("*Order No:* " + order.number);
  lines.push("*Date:* " + order.date);
  lines.push("*Customer:* " + order.name);
  lines.push("*Mobile:* " + order.phone);
  lines.push("*City:* " + order.city + ", " + order.province);
  lines.push("*Address:* " + order.address + (order.area ? ", " + order.area : ""));
  lines.push("");
  lines.push("*Products:*");
  order.items.forEach(function (l, i) {
    lines.push((i + 1) + ". " + l.name + " (" + l.size + " / " + l.color + ") x" + l.qty + " — " + bwFormat(l.lineTotal));
  });
  lines.push("");
  lines.push("Subtotal: " + bwFormat(order.subtotal));
  if (order.discount > 0) lines.push("Discount: -" + bwFormat(order.discount));
  lines.push("Delivery: " + (order.delivery === 0 ? "FREE" : bwFormat(order.delivery)));
  lines.push("*TOTAL: " + bwFormat(order.total) + "*");
  lines.push("");
  lines.push("*Payment:* Cash on Delivery");
  if (order.notes) lines.push("Notes: " + order.notes);
  lines.push("");
  lines.push("Please confirm my order. Thank you!");
  return lines.join("\n");
}

function bwWhatsAppLink(message) {
  return "https://wa.me/" + BW_WHATSAPP + "?text=" + encodeURIComponent(message);
}

/* ---------- cart page event wiring ---------- */
document.addEventListener("click", function (e) {
  var t = e.target;

  var inc = t.closest("[data-inc]");
  if (inc) {
    var input = document.querySelector('[data-qty="' + inc.getAttribute("data-inc") + '"]');
    bwCartUpdate(inc.getAttribute("data-inc"), Number(input.value) + 1); bwRenderCart(); return;
  }
  var dec = t.closest("[data-dec]");
  if (dec) {
    var inp = document.querySelector('[data-qty="' + dec.getAttribute("data-dec") + '"]');
    if (Number(inp.value) <= 1) { bwCartRemove(dec.getAttribute("data-dec")); bwToast("Removed from cart", "", "ok"); }
    else bwCartUpdate(dec.getAttribute("data-dec"), Number(inp.value) - 1);
    bwRenderCart(); return;
  }
  var rem = t.closest("[data-remove]");
  if (rem) { bwCartRemove(rem.getAttribute("data-remove")); bwToast("Removed from cart", "", "ok"); bwRenderCart(); return; }

  if (t.closest("[data-promo-remove]")) { bwPromoRemove(); bwRenderCart(); return; }

  var apply = t.closest("[data-promo-apply]");
  if (apply) {
    var val = document.getElementById("promo-input").value;
    if (bwPromoApply(val)) bwRenderCart();
  }
});
