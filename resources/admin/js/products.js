/* BeastWear.pk Admin — Products, Product Form, Categories, Category Form */
(function () {
  'use strict';
  const { esc } = BW.util; const F = BW.fmt;
  const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

  /* ================= PRODUCTS LIST ================= */
  async function initProducts() {
    const cats = await BW.api.list('categories'); let products = await BW.api.list('products');
    const catSel = document.getElementById('fCategory'); catSel.innerHTML = '<option value="">All Categories</option>' + cats.filter((c) => !c.virtual).map((c) => `<option value="${c.slug}">${esc(c.name)}</option>`).join('');
    const tbl = BW.table({
      table: document.getElementById('productsTable'), tbody: document.querySelector('#productsTable tbody'), pager: document.getElementById('pager'), info: document.getElementById('pageInfo'), pageSize: 10, sortKey: 'createdAt',
      row: (p) => `<tr data-id="${p.id}"><td><img class="product-thumb" src="${esc(p.image)}" alt=""></td><td><div class="cell-product"><div><a class="name" href="/admin/products/edit?id=${p.id}">${esc(p.name)}</a><span class="sub">${p.colors.map((c) => c.name).join(', ')} · ${p.sizes.join('/')}</span></div></div></td><td class="mono">${esc(p.sku)}</td><td>${esc(BW.domain.categoryName(cats, p.category))}</td><td class="amount">${F.money(p.price)}</td><td>${p.salePrice ? `<span class="amount text-success">${F.money(p.salePrice)}</span>` : '<span class="text-dim">—</span>'}</td><td><div class="stock-bar"><span class="fw-600">${p.stock}</span>${BW.badge(BW.domain.stockStatus(p), 'badge-plain')}</div></td><td>${BW.badge(p.status)}</td><td><label class="switch"><input type="checkbox" data-feat="${p.id}" ${p.featured ? 'checked' : ''}><span class="track"></span></label></td><td><div class="actions"><button class="btn-icon" data-view="${p.id}" title="View">${BW.icon('eye')}</button><a class="btn-icon" href="/admin/products/edit?id=${p.id}" title="Edit">${BW.icon('edit')}</a><button class="btn-icon danger" data-del="${p.id}" title="Delete">${BW.icon('trash')}</button></div></td></tr>`,
      afterRender: bindRows
    });
    const filters = ['fSearch', 'fCategory', 'fStock', 'fStatus', 'fPrice'].map((id) => document.getElementById(id));
    const apply = () => { const [q, cat, stock, status, price] = filters.map((f) => f.value.trim().toLowerCase()); tbl.setData(products.filter((p) => (!q || (p.name + ' ' + p.sku).toLowerCase().includes(q)) && (!cat || p.category === cat) && (!stock || BW.domain.stockStatus(p).toLowerCase() === stock) && (!status || p.status.toLowerCase() === status) && (!price || (price === 'under-2000' ? p.price < 2000 : price === '2000-4000' ? p.price >= 2000 && p.price <= 4000 : p.price > 4000)))); document.getElementById('productCount').textContent = tbl.state.data.length; };
    filters.forEach((f) => f.addEventListener(f.tagName === 'INPUT' ? 'input' : 'change', BW.util.debounce(apply, 150)));
    document.getElementById('clearFilters').addEventListener('click', () => { filters.forEach((f) => (f.value = '')); apply(); });
    apply();
    function bindRows() {
      document.querySelectorAll('[data-feat]').forEach((c) => c.addEventListener('change', async () => { await BW.api.update('products', c.dataset.feat, { featured: c.checked }); products = await BW.api.list('products'); BW.toast(c.checked ? 'Marked as featured' : 'Removed from featured', 'info'); }));
      document.querySelectorAll('[data-del]').forEach((b) => b.addEventListener('click', () => { const p = products.find((x) => String(x.id) === b.dataset.del); BW.ui.confirm(`Delete <b>${esc(p.name)}</b> (${p.sku})? This cannot be undone.`, async () => { await BW.api.remove('products', p.id); products = await BW.api.list('products'); apply(); BW.toast('Product deleted', 'success'); }); }));
      document.querySelectorAll('[data-view]').forEach((b) => b.addEventListener('click', () => quickView(products.find((x) => String(x.id) === b.dataset.view), cats)));
    }
  }
  function quickView(p, cats) {
    BW.ui.modal({ title: p.name, size: 'lg', body: `<div class="grid grid-1-2" style="gap:24px"><div><img class="img-preview-lg" src="${esc(p.image)}" alt="" style="max-width:none"><div class="upload-preview">${(p.images || []).slice(1).map((i) => `<div class="thumb"><img src="${esc(i)}" alt=""></div>`).join('')}</div></div><div><div class="flex gap-6 flex-wrap mb-16">${BW.badge(p.status)}${BW.badge(BW.domain.stockStatus(p))}${p.featured ? BW.badge('Featured', 'badge-dark') : ''}${p.newArrival ? BW.badge('New Arrival') : ''}${p.bestSeller ? BW.badge('Best Seller') : ''}${p.sale ? BW.badge('Sale') : ''}</div><dl class="kv"><dt>SKU</dt><dd class="mono">${esc(p.sku)}</dd><dt>Category</dt><dd>${esc(BW.domain.categoryName(cats, p.category))}</dd><dt>Price</dt><dd>${F.money(p.price)} ${p.salePrice ? `<span class="text-success">→ ${F.money(p.salePrice)}</span>` : ''}</dd><dt>Cost Price</dt><dd>${F.money(p.costPrice)} <span class="text-muted small">(margin ${Math.round((((p.salePrice || p.price) - p.costPrice) / (p.salePrice || p.price)) * 100)}%)</span></dd><dt>Stock</dt><dd>${p.stock} units · ${p.sold} sold</dd><dt>Sizes</dt><dd>${p.sizes.join(', ')}</dd><dt>Colors</dt><dd><div class="chips">${p.colors.map((c) => `<span class="chip"><i class="swatch" style="background:${c.hex}"></i>${esc(c.name)}</span>`).join('')}</div></dd><dt>Short Desc.</dt><dd>${esc(p.shortDescription)}</dd></dl><p class="text-muted mt-16 small">${esc(p.description)}</p></div></div>`, footer: `<button class="btn btn-outline" data-close>Close</button><a class="btn btn-primary" href="/admin/products/edit?id=${p.id}">${BW.icon('edit')} Edit Product</a>` });
  }

  /* ================= PRODUCT FORM (add / edit) ================= */
  async function initProductForm() {
    const isEdit = document.body.dataset.page === 'product-edit';
    const id = BW.util.qs('id'); const form = document.getElementById('productForm');
    const cats = await BW.api.list('categories');
    form.category.innerHTML = '<option value="">Select category</option>' + cats.filter((c) => !c.virtual).map((c) => `<option value="${c.slug}">${esc(c.name)}</option>`).join('');
    document.getElementById('sizePills').innerHTML = SIZES.map((s) => `<label class="size-pill"><input type="checkbox" name="sizes" value="${s}"><span>${s}</span></label>`).join('');
    let colors = [], mainImage = '', gallery = [];
    const renderColors = () => { document.getElementById('colorChips').innerHTML = colors.map((c, i) => `<span class="chip"><i class="swatch" style="background:${c.hex}"></i>${esc(c.name)}<button type="button" data-rc="${i}">${BW.icon('x')}</button></span>`).join('') || '<span class="text-dim small">No colors added yet</span>'; document.querySelectorAll('[data-rc]').forEach((b) => (b.onclick = () => { colors.splice(+b.dataset.rc, 1); renderColors(); })); };
    document.getElementById('addColor').addEventListener('click', () => { const n = document.getElementById('colorName'), h = document.getElementById('colorHex'); if (!n.value.trim()) return BW.toast('Enter a color name', 'warning'); colors.push({ name: n.value.trim(), hex: h.value }); n.value = ''; renderColors(); });
    const renderImages = () => { document.getElementById('mainPreview').innerHTML = mainImage ? `<div class="thumb main"><img src="${mainImage}" alt=""><button type="button" id="rmMain">${BW.icon('x')}</button></div>` : ''; const r = document.getElementById('rmMain'); if (r) r.onclick = () => { mainImage = ''; renderImages(); }; document.getElementById('galleryPreview').innerHTML = gallery.map((g, i) => `<div class="thumb"><img src="${g}" alt=""><button type="button" data-rg="${i}">${BW.icon('x')}</button></div>`).join(''); document.querySelectorAll('[data-rg]').forEach((b) => (b.onclick = () => { gallery.splice(+b.dataset.rg, 1); renderImages(); })); };
    document.getElementById('mainImage').addEventListener('change', async (e) => { if (e.target.files[0]) { mainImage = await BW.ui.readFile(e.target.files[0]); renderImages(); } });
    document.getElementById('galleryImages').addEventListener('change', async (e) => { for (const f of e.target.files) gallery.push(await BW.ui.readFile(f)); renderImages(); });
    document.querySelectorAll('.upload-box').forEach((b) => { b.addEventListener('dragover', (e) => { e.preventDefault(); b.classList.add('dragover'); }); b.addEventListener('dragleave', () => b.classList.remove('dragover')); b.addEventListener('drop', () => b.classList.remove('dragover')); });
    const nameEl = form.elements.name;
    nameEl.addEventListener('input', () => { if (!isEdit && !form.sku.dataset.touched) form.sku.value = 'BW-' + BW.util.slugify(nameEl.value).toUpperCase().replace(/-/g, '').slice(0, 6) + '-' + String(Math.floor(Math.random() * 900) + 100); });
    form.sku.addEventListener('input', () => (form.sku.dataset.touched = 1));
    const margin = () => { const p = Number(form.salePrice.value) || Number(form.price.value), c = Number(form.costPrice.value); document.getElementById('marginHint').textContent = p && c ? `Profit margin: ${Math.round(((p - c) / p) * 100)}% (${F.money(p - c)} per unit)` : ''; };
    ['price', 'salePrice', 'costPrice'].forEach((n) => form[n].addEventListener('input', margin));

    let product = null;
    if (isEdit) {
      product = await BW.api.get('products', id);
      if (!product) { BW.toast('Product not found', 'error'); return setTimeout(() => (location.href = '/admin/products'), 800); }
      ['name', 'sku', 'category', 'description', 'shortDescription', 'price', 'salePrice', 'costPrice', 'stock', 'lowStock'].forEach((k) => (form.elements[k].value = product[k] ?? ''));
      ['featured', 'newArrival', 'bestSeller', 'sale'].forEach((k) => (form[k].checked = !!product[k]));
      form.status.value = product.status; form.querySelectorAll('[name=sizes]').forEach((c) => (c.checked = product.sizes.includes(c.value)));
      colors = [...product.colors]; mainImage = product.image; gallery = (product.images || []).filter((i) => i !== product.image);
      document.getElementById('pageTitle').textContent = product.name; document.getElementById('productMeta').innerHTML = `${BW.badge(product.status)} ${BW.badge(BW.domain.stockStatus(product))} <span class="text-muted small">· ${product.sold} sold · created ${F.date(product.createdAt)}</span>`;
      form.sku.dataset.touched = 1; margin();
      document.getElementById('deleteProduct').addEventListener('click', () => BW.ui.confirm(`Delete <b>${esc(product.name)}</b> permanently?`, async () => { await BW.api.remove('products', product.id); BW.toast('Product deleted'); setTimeout(() => (location.href = '/admin/products'), 500); }));
    } else { form.lowStock.value = 10; form.status.value = 'Active'; }
    renderColors(); renderImages();

    form.addEventListener('submit', async (e) => {
      e.preventDefault(); if (!BW.ui.validate(form)) return BW.toast('Please fix the highlighted fields', 'error');
      const sizes = [...form.querySelectorAll('[name=sizes]:checked')].map((c) => c.value);
      if (!sizes.length) return BW.toast('Select at least one size', 'warning');
      if (!colors.length) return BW.toast('Add at least one color', 'warning');
      if (form.salePrice.value && Number(form.salePrice.value) >= Number(form.price.value)) return BW.toast('Sale price must be lower than regular price', 'warning');
      const data = { name: nameEl.value.trim(), sku: form.sku.value.trim().toUpperCase(), category: form.category.value, description: form.description.value.trim(), shortDescription: form.shortDescription.value.trim(), price: Number(form.price.value), salePrice: form.salePrice.value ? Number(form.salePrice.value) : null, costPrice: Number(form.costPrice.value || 0), stock: Number(form.stock.value), lowStock: Number(form.lowStock.value || 10), sizes, colors, image: mainImage || (gallery[0] || `https://picsum.photos/seed/${form.sku.value}/500/500`), images: [mainImage, ...gallery].filter(Boolean), featured: form.featured.checked, newArrival: form.newArrival.checked, bestSeller: form.bestSeller.checked, sale: form.sale.checked, status: form.status.value };
      const all = await BW.api.list('products'); if (all.some((p) => p.sku === data.sku && String(p.id) !== String(id))) return BW.toast('SKU already exists', 'error');
      try {
        if (isEdit) { await BW.api.update('products', id, data); BW.toast('Product updated successfully'); }
        else { await BW.api.create('products', Object.assign(data, { sold: 0 })); BW.toast('Product created successfully'); setTimeout(() => (location.href = '/admin/products'), 600); }
      } catch (err) { BW.toast(err.message, 'error'); }
    });
  }

  /* ================= CATEGORIES ================= */
  async function initCategories() {
    const render = async () => {
      const [cats, products] = await Promise.all([BW.api.list('categories'), BW.api.list('products')]);
      const count = (c) => c.slug === 'new-arrivals' ? products.filter((p) => p.newArrival).length : c.slug === 'sale' ? products.filter((p) => p.sale || p.salePrice).length : products.filter((p) => p.category === c.slug).length;
      document.getElementById('catGrid').innerHTML = cats.map((c) => `<div class="category-card"><img src="${esc(c.image)}" alt=""><div class="cc-body"><div class="flex-between"><h3>${esc(c.name)}</h3>${BW.badge(c.status)}</div><p>${esc(c.description || '')}</p><p class="mono mt-8">/${esc(c.slug)}</p></div><div class="cc-foot"><span class="small text-muted"><b class="fw-600" style="color:var(--text)">${count(c)}</b> products ${c.virtual ? '· dynamic' : ''}</span><div class="actions"><a class="btn-icon" href="/admin/categories/form?id=${c.id}" title="Edit">${BW.icon('edit')}</a><button class="btn-icon danger" data-del="${c.id}" title="Delete">${BW.icon('trash')}</button></div></div></div>`).join('');
      document.getElementById('catTable').innerHTML = cats.map((c) => `<tr><td><img class="product-thumb" src="${esc(c.image)}" alt=""></td><td><div class="fw-600">${esc(c.name)}</div><div class="small text-muted mono">/${esc(c.slug)}</div></td><td class="num">${count(c)}</td><td>${BW.badge(c.status)}</td><td><div class="actions"><a class="btn-icon" href="/admin/categories/form?id=${c.id}">${BW.icon('edit')}</a><button class="btn-icon danger" data-del="${c.id}">${BW.icon('trash')}</button></div></td></tr>`).join('');
      document.querySelectorAll('[data-del]').forEach((b) => b.addEventListener('click', () => { const c = cats.find((x) => String(x.id) === b.dataset.del); const n = count(c); BW.ui.confirm(`Delete category <b>${esc(c.name)}</b>?${n ? ` ${n} products are assigned to it and will become uncategorized.` : ''}`, async () => { await BW.api.remove('categories', c.id); BW.toast('Category deleted'); render(); }); }));
      document.getElementById('catCount').textContent = cats.length;
    };
    const grid = document.getElementById('catGrid'), table = document.getElementById('catTableWrap');
    document.querySelectorAll('[data-view]').forEach((b) => b.addEventListener('click', () => { document.querySelectorAll('[data-view]').forEach((x) => x.classList.remove('active')); b.classList.add('active'); grid.classList.toggle('hidden', b.dataset.view !== 'grid'); table.classList.toggle('hidden', b.dataset.view !== 'table'); }));
    render();
  }

  /* ================= CATEGORY FORM ================= */
  async function initCategoryForm() {
    const id = BW.util.qs('id'); const form = document.getElementById('categoryForm'); let cat = null, image = '', banner = '';
    const prev = () => { document.getElementById('imgPrev').src = image || 'https://picsum.photos/seed/placeholder-cat/600/450'; document.getElementById('bannerPrev').src = banner || 'https://picsum.photos/seed/placeholder-ban/1400/500'; };
    const catName = form.elements.name;
    catName.addEventListener('input', () => { if (!form.slug.dataset.touched) form.slug.value = BW.util.slugify(catName.value); });
    form.slug.addEventListener('input', () => (form.slug.dataset.touched = 1));
    document.getElementById('catImage').addEventListener('change', async (e) => { if (e.target.files[0]) { image = await BW.ui.readFile(e.target.files[0]); prev(); } });
    document.getElementById('catBanner').addEventListener('change', async (e) => { if (e.target.files[0]) { banner = await BW.ui.readFile(e.target.files[0]); prev(); } });
    if (id) { cat = await BW.api.get('categories', id); if (!cat) return (location.href = '/admin/categories'); catName.value = cat.name; form.slug.value = cat.slug; form.slug.dataset.touched = 1; form.description.value = cat.description || ''; form.status.value = cat.status; image = cat.image; banner = cat.banner; document.getElementById('pageTitle').textContent = 'Edit: ' + cat.name; }
    prev();
    form.addEventListener('submit', async (e) => {
      e.preventDefault(); if (!BW.ui.validate(form)) return;
      const data = { name: catName.value.trim(), slug: BW.util.slugify(form.slug.value), description: form.description.value.trim(), status: form.status.value, image: image || `https://picsum.photos/seed/${form.slug.value}/600/450`, banner: banner || `https://picsum.photos/seed/${form.slug.value}-b/1400/500` };
      const all = await BW.api.list('categories'); if (all.some((c) => c.slug === data.slug && String(c.id) !== String(id))) return BW.toast('Slug already in use', 'error');
      if (cat) { await BW.api.update('categories', id, data); if (cat.slug !== data.slug) { const ps = await BW.api.list('products'); for (const p of ps.filter((p) => p.category === cat.slug)) await BW.api.update('products', p.id, { category: data.slug }); } BW.toast('Category updated'); }
      else { await BW.api.create('categories', data); BW.toast('Category created'); }
      setTimeout(() => (location.href = '/admin/categories'), 500);
    });
  }

  BW.ready(() => { const page = document.body.dataset.page; BW.boot(page === 'product-create' || page === 'product-edit' ? initProductForm : page === 'category-form' ? initCategoryForm : page === 'categories' ? initCategories : initProducts); });
})();
