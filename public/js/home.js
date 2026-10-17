document.addEventListener("DOMContentLoaded", function () {
  var categoryGrid = document.getElementById("cat-grid");
  if (categoryGrid) {
    categoryGrid.innerHTML = BW_CATEGORIES.map(function (category) {
      return '<a class="cat-card" href="/category?cat=' + encodeURIComponent(category.slug) + '">' +
        (category.image ? '<img src="' + category.image + '" alt="' + category.name + '" loading="lazy">' : '') +
        "<span>" + category.name + "<i>→</i></span></a>";
    }).join("");
  }

  bwRenderCards("new-arrivals", bwNewArrivals(8));
  bwRenderCards("best-sellers", bwBestSellers(8));

  document.querySelectorAll("[data-ico]").forEach(function (element) {
    element.innerHTML = BW_ICONS[element.getAttribute("data-ico")] || "";
  });

  if (typeof bwObserveReveals === "function") bwObserveReveals();
});
