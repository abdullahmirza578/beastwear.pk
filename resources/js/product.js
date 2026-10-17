/* ==========================================================================
   BeastWear.pk — js/product.js
   Product detail page: gallery + zoom, size/color/qty selection,
   add to cart, buy now, WhatsApp order, tabs, reviews, related items.
   ========================================================================== */

var bwPdp = { id: null, size: null, color: null, qty: 1, imgIndex: 0 };

document.addEventListener("DOMContentLoaded", function () {
  var main = document.getElementById("pdp-main-img");
  if (!main) return; /* not the product page */

  var params = new URLSearchParams(location.search);
  var id = Number(params.get("id") || BW_PRODUCTS[0].id);
  var p = bwProduct(id);
  if (!p) { location.href = "shop.html"; return; }

  bwPdp.id = p.id;
  bwPdp.size = p.sizes[0];
  bwPdp.color = p.colors[0];
  document.title = p.name + " — BeastWear.pk";

  /* ---------- breadcrumb + heading ---------- */
  setText("crumb-product", p.name);
  setText("pdp-name", p.name);
  setText("pdp-cat", p.category);
  setText("pdp-rating-num", p.rating + " (" + p.reviews + " reviews)");

  /* ---------- price ---------- */
  var off = bwDiscount(p);
  var priceBox = document.getElementById("pdp-price");
  if (priceBox) priceBox.innerHTML =
    "<b>" + bwFormat(bwEffective(p)) + "</b>" +
    (off ? " <s>" + bwFormat(p.price) + "</s>" + '<span class="off-tag">-' + off + "% OFF</span>" : "");

  /* ---------- stock ---------- */
  var stock = document.getElementById("pdp-stock");
  if (stock) {
    stock.className = "stock-line" + (p.stock > 0 && p.stock < 10 ? " low" : "");
    stock.innerHTML = p.stock > 0
      ? (p.stock < 10 ? "<b>Only " + p.stock + " left</b> — order soon" : "<b>In stock</b> · ships within 24 hours")
      : "<b>Out of stock</b> · restocking soon";
  }

  /* ---------- gallery ---------- */
  main.src = p.images[0];
  main.alt = p.name;
  var thumbs = document.getElementById("pdp-thumbs");
  if (thumbs) {
    thumbs.innerHTML = p.images.map(function (src, i) {
      return '<button type="button" class="' + (i === 0 ? "active" : "") + '" data-thumb="' + i + '"><img src="' + src + '" alt="View ' + (i + 1) + '"></button>';
    }).join("");
    thumbs.addEventListener("click", function (e) {
      var b = e.target.closest("[data-thumb]");
      if (!b) return;
      bwPdp.imgIndex = Number(b.getAttribute("data-thumb"));
      main.src = p.images[bwPdp.imgIndex];
      thumbs.querySelectorAll("button").forEach(function (x) { x.classList.remove("active"); });
      b.classList.add("active");
    });
  }

  /* zoom: hover to magnify, click to toggle (mobile friendly) */
  var frame = document.getElementById("pdp-main");
  frame.addEventListener("click", function () { frame.classList.toggle("zoomed"); });
  frame.addEventListener("mousemove", function (e) {
    if (!frame.classList.contains("zoomed")) return;
    var r = frame.getBoundingClientRect();
    var x = ((e.clientX - r.left) / r.width) * 100;
    var y = ((e.clientY - r.top) / r.height) * 100;
    main.style.transformOrigin = x + "% " + y + "%";
  });

  /* ---------- sizes / colors / qty ---------- */
  var sizes = document.getElementById("pdp-sizes");
  if (sizes) {
    sizes.innerHTML = p.sizes.map(function (s) {
      return '<button type="button" data-size="' + s + '" class="' + (s === bwPdp.size ? "active" : "") + '">' + s + "</button>";
    }).join("") + '<a href="size-guide.html" class="hint" style="align-self:center;margin-left:6px;text-decoration:underline">Size guide</a>';
    sizes.addEventListener("click", function (e) {
      var b = e.target.closest("[data-size]");
      if (!b) return;
      sizes.querySelectorAll("[data-size]").forEach(function (x) { x.classList.remove("active"); });
      b.classList.add("active"); bwPdp.size = b.getAttribute("data-size");
    });
  }

  var colors = document.getElementById("pdp-colors");
  if (colors) {
    colors.innerHTML = p.colors.map(function (c) {
      return '<button type="button" class="swatch ' + (c === bwPdp.color ? "active" : "") + '" data-color="' + c +
        '"><i style="background:' + (BW_COLORS[c] || "#111") + '"></i>' + c + "</button>";
    }).join("");
    colors.addEventListener("click", function (e) {
      var b = e.target.closest("[data-color]");
      if (!b) return;
      colors.querySelectorAll("[data-color]").forEach(function (x) { x.classList.remove("active"); });
      b.classList.add("active"); bwPdp.color = b.getAttribute("data-color");
    });
  }

  var qtyInput = document.getElementById("pdp-qty");
  var qMinus = document.getElementById("qty-minus"), qPlus = document.getElementById("qty-plus");
  if (qMinus) qMinus.addEventListener("click", function () { bwPdp.qty = Math.max(1, bwPdp.qty - 1); qtyInput.value = bwPdp.qty; });
  if (qPlus) qPlus.addEventListener("click", function () { bwPdp.qty = Math.min(20, bwPdp.qty + 1); qtyInput.value = bwPdp.qty; });

  /* ---------- wishlist button state ---------- */
  var wb = document.getElementById("pdp-wish");
  if (wb) {
    if (bwWishHas(p.id)) wb.classList.add("active");
    wb.addEventListener("click", function () { wb.classList.toggle("active", bwWishToggle(p.id)); });
  }

  /* ---------- actions ---------- */
  var addBtn = document.getElementById("pdp-add");
  if (addBtn) {
    if (p.stock <= 0) { addBtn.disabled = true; addBtn.textContent = "Sold Out"; }
    addBtn.addEventListener("click", function () {
      if (bwCartAdd(p.id, bwPdp.size, bwPdp.color, bwPdp.qty)) bwBump(addBtn);
    });
  }
  var buyBtn = document.getElementById("pdp-buy");
  if (buyBtn) {
    if (p.stock <= 0) buyBtn.disabled = true;
    buyBtn.addEventListener("click", function () {
      if (!bwCartAdd(p.id, bwPdp.size, bwPdp.color, bwPdp.qty)) return;
      location.href = "checkout.html";
    });
  }

  /* WhatsApp order for this single product */
  var waBtn = document.getElementById("pdp-wa");
  if (waBtn) waBtn.addEventListener("click", function () {
    var msg = "Hello BeastWear.pk! I want to order:\n\n" +
      "*Product:* " + p.name + "\n*Size:* " + bwPdp.size + "\n*Color:* " + bwPdp.color +
      "\n*Quantity:* " + bwPdp.qty + "\n*Price:* " + bwFormat(bwEffective(p) * bwPdp.qty) +
      "\n*Link:* " + location.href + "\n\nPlease confirm availability.";
    window.open(bwWhatsAppLink(msg), "_blank");
  });

  /* ---------- tabs / info ---------- */
  setText("tab-desc", p.desc);
  setText("tab-fabric", p.fabric);
  setText("tab-fit", p.fit + " · Model is 5'11\" wearing size " + p.sizes[Math.min(2, p.sizes.length - 1)] + ".");
  setText("tab-stock", p.stock > 0 ? p.stock + " pieces available right now." : "Currently out of stock.");
  var cat = bwCategory(p.category);
  setText("tab-cat", p.category + (cat ? " — " + cat.desc : ""));

  /* reviews */
  var rAvg = document.getElementById("pdp-avg");
  if (rAvg) rAvg.innerHTML = "<b style='font-size:40px;display:block'>" + p.rating + "</b>" + bwStars(p.rating) +
    "<div class='hint' style='margin-top:6px'>Based on " + p.reviews + " verified reviews</div>";

  /* related products */
  bwRenderCards("related-grid", bwRelated(p, 4));

  /* sticky mobile bar */
  var bar = document.getElementById("sticky-bar");
  if (bar) {
    bar.innerHTML =
      '<div class="mini-price"><b>' + bwFormat(bwEffective(p)) + "</b><span class='hint'>" + bwPdp.size + " · " + bwPdp.color + "</span></div>" +
      '<button class="btn btn-outline" data-bar-add' + (p.stock <= 0 ? " disabled" : "") + ">Add to Cart</button>" +
      '<button class="btn btn-dark" data-bar-buy' + (p.stock <= 0 ? " disabled" : "") + ">Buy Now</button>";
    document.body.classList.add("has-sticky");
    bar.querySelector("[data-bar-add]").addEventListener("click", function () {
      if (bwCartAdd(p.id, bwPdp.size, bwPdp.color, bwPdp.qty)) bwBump(bar.querySelector("[data-bar-add]"));
    });
    bar.querySelector("[data-bar-buy]").addEventListener("click", function () {
      if (!bwCartAdd(p.id, bwPdp.size, bwPdp.color, bwPdp.qty)) return;
      location.href = "checkout.html";
    });
  }
});

/* small helpers */
function setText(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }
function bwBump(el) {
  el.style.transform = "scale(.94)";
  setTimeout(function () { el.style.transform = ""; }, 160);
}
