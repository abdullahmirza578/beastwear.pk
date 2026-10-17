/* ==========================================================================
   BeastWear.pk — js/product.js
   Product detail page: gallery + zoom, size/color/qty selection,
   add to cart, buy now, WhatsApp order, tabs, reviews, related items.
   ========================================================================== */

var bwPdp = { id: null, size: null, color: null, designId: null, qty: 1, imgIndex: 0 };

document.addEventListener("DOMContentLoaded", function () {
  var main = document.getElementById("pdp-main-img");
  var frame = document.getElementById("pdp-main");
  if (!frame) return; /* not the product page */

  var params = new URLSearchParams(location.search);
  var id = Number(params.get("id"));
  var p = id ? bwProduct(id) : BW_PRODUCTS.find(function (product) { return product.slug === window.BW_PRODUCT_SLUG; });
  if (!p) { location.href = window.BW_SHOP_URL || "/shop"; return; }

  bwPdp.id = p.id;
  var options = p.optionConfig || { size: p.sizes.length > 0, color: p.colors.length > 0, design: false };
  bwPdp.size = null;
  bwPdp.color = null;
  bwPdp.designId = null;
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
  if (main && p.images.length) main.src = p.images[0];
  if (main) main.alt = p.name;
  var thumbs = document.getElementById("pdp-thumbs");
  if (thumbs && main && p.images.length) {
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
  if (main) frame.addEventListener("click", function () { frame.classList.toggle("zoomed"); });
  if (main) frame.addEventListener("mousemove", function (e) {
    if (!frame.classList.contains("zoomed")) return;
    var r = frame.getBoundingClientRect();
    var x = ((e.clientX - r.left) / r.width) * 100;
    var y = ((e.clientY - r.top) / r.height) * 100;
    main.style.transformOrigin = x + "% " + y + "%";
  });

  /* ---------- sizes / colors / qty ---------- */
  var sizes = document.getElementById("pdp-sizes");
  var colors = document.getElementById("pdp-colors");
  var designs = document.getElementById("pdp-designs");
  var sizeLabel = sizes && sizes.previousElementSibling;
  var colorLabel = colors && colors.previousElementSibling;
  var addBtn = document.getElementById("pdp-add");
  var buyBtn = document.getElementById("pdp-buy");
  var bar = document.getElementById("sticky-bar");
  if (sizeLabel) sizeLabel.hidden = !options.size;
  if (sizes) sizes.hidden = !options.size;
  if (colorLabel) colorLabel.hidden = !options.color;
  if (colors) colors.hidden = !options.color;
  if (designs) {
    var designLabel = designs.previousElementSibling;
    if (designLabel) designLabel.hidden = !options.design;
    designs.hidden = !options.design;
  }

  function selectedVariant() {
    if ((!options.size && !options.color) ||
        (options.size && !bwPdp.size) ||
        (options.color && !bwPdp.color)) return null;
    return (p.variants || []).find(function (variant) {
      return (!options.size || variant.size === bwPdp.size) &&
        (!options.color || variant.color === bwPdp.color) &&
        Number(variant.stock) > 0;
    }) || null;
  }

  function updateSelection() {
    if (sizes) sizes.querySelectorAll("[data-size]").forEach(function (button) {
      var value = button.getAttribute("data-size");
      button.disabled = !(p.variants || []).some(function (variant) {
        return Number(variant.stock) > 0 && variant.size === value &&
          (!options.color || !bwPdp.color || variant.color === bwPdp.color);
      });
      button.classList.toggle("active", value === bwPdp.size);
    });
    if (colors) colors.querySelectorAll("[data-color]").forEach(function (button) {
      var value = button.getAttribute("data-color");
      button.disabled = !(p.variants || []).some(function (variant) {
        return Number(variant.stock) > 0 && variant.color === value &&
          (!options.size || !bwPdp.size || variant.size === bwPdp.size);
      });
      button.classList.toggle("active", value === bwPdp.color);
    });

    var variant = selectedVariant();
    var addition = variant ? Number(variant.additionalPrice || 0) : 0;
    var original = Number(p.price) + addition;
    var effective = Number(bwEffective(p)) + addition;
    if (priceBox) priceBox.innerHTML = "<b>" + bwFormat(effective) + "</b>" +
      (effective < original ? " <s>" + bwFormat(original) + "</s>" +
        '<span class="off-tag">-' + Math.round(((original - effective) / original) * 100) + "% OFF</span>" : "");

    var available = variant ? variant.stock : p.stock;
    bwPdp.qty = Math.min(bwPdp.qty, Math.max(1, available));
    if (qtyInput) qtyInput.value = bwPdp.qty;
    if (stock) {
      stock.className = "stock-line" + (available > 0 && available < 10 ? " low" : "");
      stock.innerHTML = available > 0
        ? (available < 10 ? "<b>Only " + available + " left</b> — order soon" : "<b>In stock</b> · ships within 24 hours")
        : "<b>Out of stock</b> · restocking soon";
    }
    [addBtn, buyBtn].forEach(function (button) {
      if (button) button.disabled = available <= 0;
    });
    var colorOut = document.getElementById("color-out");
    if (colorOut) colorOut.textContent = bwPdp.color || "";
    if (bar) {
      var barPrice = bar.querySelector(".mini-price b");
      var barSelection = bar.querySelector(".mini-price .hint");
      if (barPrice) barPrice.textContent = bwFormat(effective);
      if (barSelection) barSelection.textContent = [bwPdp.designId && (p.designs || []).find(function (design) { return String(design.id) === String(bwPdp.designId); })?.name, bwPdp.size, bwPdp.color].filter(Boolean).join(" · ");
      bar.querySelectorAll("[data-bar-add], [data-bar-buy]").forEach(function (button) {
        button.disabled = available <= 0;
      });
    }
  }

  if (sizes) {
    sizes.innerHTML = p.sizes.map(function (s) {
      return '<button type="button" data-size="' + s + '" class="' + (s === bwPdp.size ? "active" : "") + '">' + s + "</button>";
    }).join("") + (p.sizes.length ? '<a href="/size-guide" class="hint" style="align-self:center;margin-left:6px;text-decoration:underline">Size guide</a>' : "");
    sizes.addEventListener("click", function (e) {
      var b = e.target.closest("[data-size]");
      if (!b) return;
      bwPdp.size = b.getAttribute("data-size");
      if (options.color && bwPdp.color && !(p.variants || []).some(function (item) {
        return Number(item.stock) > 0 && item.size === bwPdp.size && item.color === bwPdp.color;
      })) bwPdp.color = null;
      updateSelection();
    });
  }

  if (colors) {
    colors.innerHTML = p.colors.map(function (c) {
      return '<button type="button" class="swatch ' + (c === bwPdp.color ? "active" : "") + '" data-color="' + c +
        '"><i style="background:' + (BW_COLORS[c] || "#111") + '"></i>' + c + "</button>";
    }).join("");
    colors.addEventListener("click", function (e) {
      var b = e.target.closest("[data-color]");
      if (!b) return;
      bwPdp.color = b.getAttribute("data-color");
      if (options.size && bwPdp.size && !(p.variants || []).some(function (item) {
        return Number(item.stock) > 0 && item.color === bwPdp.color && item.size === bwPdp.size;
      })) bwPdp.size = null;
      updateSelection();
    });
  }

  if (designs && options.design) {
    designs.innerHTML = (p.designs || []).map(function (design) {
      return '<button type="button" class="swatch" data-design="' + Number(design.id) + '">' +
        (design.image ? '<img src="' + bwHtml(design.image) + '" alt="" width="48" height="48" style="object-fit:cover;border-radius:6px">' : '') +
        bwHtml(design.name) + "</button>";
    }).join("");
    designs.addEventListener("click", function (event) {
      var button = event.target.closest("[data-design]");
      if (!button) return;
      bwPdp.designId = button.getAttribute("data-design");
      designs.querySelectorAll("[data-design]").forEach(function (choice) {
        choice.classList.toggle("active", choice === button);
      });
      updateSelection();
    });
  }

  var qtyInput = document.getElementById("pdp-qty");
  var qMinus = document.getElementById("qty-minus"), qPlus = document.getElementById("qty-plus");
  if (qMinus) qMinus.addEventListener("click", function () { bwPdp.qty = Math.max(1, bwPdp.qty - 1); qtyInput.value = bwPdp.qty; });
  if (qPlus) qPlus.addEventListener("click", function () {
    var variant = selectedVariant();
    var available = variant ? variant.stock : p.stock;
    bwPdp.qty = Math.min(20, Math.max(1, available), bwPdp.qty + 1);
    qtyInput.value = bwPdp.qty;
  });

  /* ---------- wishlist button state ---------- */
  var wb = document.getElementById("pdp-wish");
  if (wb) {
    if (bwWishHas(p.id)) wb.classList.add("active");
    wb.addEventListener("click", function () { wb.classList.toggle("active", bwWishToggle(p.id)); });
  }

  /* ---------- actions ---------- */
  updateSelection();
  if (addBtn) {
    if (p.stock <= 0) addBtn.textContent = "Sold Out";
    addBtn.addEventListener("click", function () {
      bwCartAdd(p.id, bwPdp.size, bwPdp.color, bwPdp.qty, bwPdp.designId)
        .then(function (added) { if (added) bwBump(addBtn); });
    });
  }
  if (buyBtn) {
    buyBtn.addEventListener("click", function () {
      bwCartAdd(p.id, bwPdp.size, bwPdp.color, bwPdp.qty, bwPdp.designId)
        .then(function (added) { if (added) location.href = "/checkout"; });
    });
  }

  /* WhatsApp order for this single product */
  var waBtn = document.getElementById("pdp-wa");
  if (waBtn) waBtn.addEventListener("click", function () {
    var msg = "Hello BeastWear.pk! I want to order:\n\n" +
      "*Product:* " + p.name + (bwPdp.designId ? "\n*Design:* " + ((p.designs || []).find(function (design) { return String(design.id) === String(bwPdp.designId); }) || {}).name : "") + (bwPdp.size ? "\n*Size:* " + bwPdp.size : "") + (bwPdp.color ? "\n*Color:* " + bwPdp.color : "") +
      "\n*Quantity:* " + bwPdp.qty + "\n*Price:* " + bwFormat((bwEffective(p) + Number(selectedVariant() ? selectedVariant().additionalPrice : 0)) * bwPdp.qty) +
      "\n*Link:* " + location.href + "\n\nPlease confirm availability.";
    window.open(bwWhatsAppLink(msg), "_blank");
  });

  /* ---------- tabs / info ---------- */
  setText("tab-desc-content", p.desc);
  setText("tab-fabric-content", p.fabric);
  setText("tab-fit", p.fit);
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
  if (bar) {
    bar.innerHTML =
      '<div class="mini-price"><b>' + bwFormat(bwEffective(p)) + "</b><span class='hint'>" + bwPdp.size + " · " + bwPdp.color + "</span></div>" +
      '<button class="btn btn-outline" data-bar-add' + (p.stock <= 0 ? " disabled" : "") + ">Add to Cart</button>" +
      '<button class="btn btn-dark" data-bar-buy' + (p.stock <= 0 ? " disabled" : "") + ">Buy Now</button>";
    document.body.classList.add("has-sticky");
    bar.querySelector("[data-bar-add]").addEventListener("click", function () {
      bwCartAdd(p.id, bwPdp.size, bwPdp.color, bwPdp.qty, bwPdp.designId)
        .then(function (added) { if (added) bwBump(bar.querySelector("[data-bar-add]")); });
    });
    bar.querySelector("[data-bar-buy]").addEventListener("click", function () {
      bwCartAdd(p.id, bwPdp.size, bwPdp.color, bwPdp.qty, bwPdp.designId)
        .then(function (added) { if (added) location.href = "/checkout"; });
    });
    updateSelection();
  }
});

/* small helpers */
function bwHtml(value) {
  return String(value == null ? "" : value).replace(/[&<>"']/g, function (char) {
    return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[char];
  });
}
function setText(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }
function bwBump(el) {
  el.style.transform = "scale(.94)";
  setTimeout(function () { el.style.transform = ""; }, 160);
}
