/* Storefront catalogue is rendered from Laravel's database-backed layout data. */
var BW_CATALOG = window.BW_CATALOG || { categories: [], products: [], reviews: [] };
var BW_CATEGORIES = BW_CATALOG.categories || [];
var BW_PRODUCTS = BW_CATALOG.products || [];
var BW_REVIEWS = BW_CATALOG.reviews || window.BW_CATALOG_REVIEWS || [];

var BW_COLORS = {
  "Black": "#111112", "White": "#f4f3f0", "Charcoal": "#3a3a3f",
  "Off-White": "#ece7de", "Olive": "#4d5240", "Grey Melange": "#a8a8a4",
  "Navy": "#232c3d", "Beige": "#d9cbb4", "Stone": "#9b9182"
};

var IMG_CARD = function (image) { return typeof image === "string" && image ? image : null; };
var IMG_LARGE = function (image) { return typeof image === "string" && image ? image : null; };
var IMG_SQ = function (image) { return typeof image === "string" && image ? image : null; };

function bwFormat(value) { return "Rs " + Number(value).toLocaleString("en-PK"); }

function bwProduct(id) {
  id = Number(id);
  for (var i = 0; i < BW_PRODUCTS.length; i++) {
    if (Number(BW_PRODUCTS[i].id) === id) return BW_PRODUCTS[i];
  }
  return null;
}

function bwEffective(product) {
  return product.salePrice !== null && product.salePrice < product.price ? product.salePrice : product.price;
}

function bwDiscount(product) {
  if (product.salePrice === null || product.salePrice >= product.price) return 0;
  return Math.round(((product.price - product.salePrice) / product.price) * 100);
}

function bwCategory(name) {
  for (var i = 0; i < BW_CATEGORIES.length; i++) {
    if (BW_CATEGORIES[i].name === name) return BW_CATEGORIES[i];
  }
  return null;
}

function bwCategorySlug(name) {
  var category = bwCategory(name);
  return category ? category.slug : "shop";
}

function bwFilter(items, predicate) { return items.filter(predicate); }

function bwNewArrivals(limit) {
  return BW_PRODUCTS.slice().sort(function (a, b) { return b.added - a.added; }).slice(0, limit || 8);
}

function bwBestSellers(limit) {
  return BW_PRODUCTS.filter(function (product) { return product.bestSeller || product.popularity > 85; })
    .sort(function (a, b) { return b.popularity - a.popularity; }).slice(0, limit || 8);
}

function bwSaleItems(limit) {
  return BW_PRODUCTS.filter(function (product) { return bwDiscount(product) > 0; })
    .sort(function (a, b) { return bwDiscount(b) - bwDiscount(a); }).slice(0, limit || 8);
}

function bwByCategory(categoryName, limit) {
  var products = BW_PRODUCTS.filter(function (product) { return product.category === categoryName; });
  return limit ? products.slice(0, limit) : products;
}

function bwRelated(product, limit) {
  var count = limit || 4;
  var related = BW_PRODUCTS.filter(function (item) {
    return item.category === product.category && item.id !== product.id;
  });
  if (related.length < count) {
    BW_PRODUCTS.forEach(function (item) {
      if (item.category !== product.category && item.id !== product.id && related.length < count) related.push(item);
    });
  }
  return related.slice(0, count);
}

function bwStars(rating, size) {
  var full = Math.round(rating);
  var html = '<span class="stars"' + (size ? ' style="font-size:' + size + '"' : '') +
    ' aria-label="' + rating + ' out of 5">';
  for (var i = 1; i <= 5; i++) {
    html += '<svg class="' + (i <= full ? '' : 'off') +
      '" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.9 6.3 6.9.8-5 4.7 1.3 6.8L12 17.3 5.9 20.6 7.2 13.8 2.2 9.1l6.9-.8z"/></svg>';
  }
  return html + "</span>";
}
