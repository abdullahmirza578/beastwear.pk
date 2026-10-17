/* BeastWear.pk Admin — Inventory / stock management */
(function () {
  'use strict';
  const { esc } = BW.util;
  const F = BW.fmt;
  const stockStatus = (product) => {
    if (Number(product.stock) <= 0) return 'Out of Stock';
    return Number(product.stock) <= Number(product.lowStock) ? 'Low Stock' : 'In Stock';
  };

  async function request(url, body) {
    const response = await fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(body)
    });
    const result = await response.json();
    if (!response.ok) {
      const validationMessage = result.errors && Object.values(result.errors).flat()[0];
      throw new Error(validationMessage || result.message || 'Unable to save inventory changes.');
    }
    return result;
  }

  async function loadInventory() {
    const response = await fetch('/admin/inventory/data', {
      headers: { Accept: 'application/json' }
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.message || 'Unable to load inventory.');
    return result;
  }

  async function init() {
    let products;
    let categories;
    let active = '';
    try {
      ({ products, categories } = await loadInventory());
    } catch (error) {
      BW.toast(error.message, 'error');
      return;
    }

    const search = document.getElementById('iSearch');
    const category = document.getElementById('iCategory');
    category.innerHTML = '<option value="">All Categories</option>' + categories.map((item) =>
      `<option value="${esc(item.slug)}">${esc(item.name)}</option>`
    ).join('');

    const table = BW.table({
      table: document.getElementById('invTable'),
      tbody: document.querySelector('#invTable tbody'),
      pager: document.getElementById('pager'),
      info: document.getElementById('pageInfo'),
      pageSize: 12,
      sortKey: 'stock',
      sortDir: 1,
      row: (product) => {
        const status = stockStatus(product);
        const percent = Math.min(100, (product.stock / Math.max(product.lowStock * 5, 1)) * 100);
        const color = status === 'Out of Stock'
          ? 'var(--danger)'
          : status === 'Low Stock' ? 'var(--warning)' : 'var(--text)';
        return `<tr>
          <td><div class="cell-product"><img class="product-thumb" src="${esc(product.image)}" alt=""><div>
            <a class="name" href="/admin/products/edit?id=${encodeURIComponent(product.id)}">${esc(product.name)}</a>
            <span class="sub">${esc(product.sizes.join(' / '))}</span>
          </div></div></td>
          <td class="mono">${esc(product.sku)}</td>
          <td>${esc(product.category ? product.category.name : '—')}</td>
          <td><div class="stock-bar"><b style="min-width:28px">${product.stock}</b><div class="progress">
            <span style="width:${percent}%;background:${color}"></span></div></div></td>
          <td class="num">${product.sold}</td>
          <td class="num fw-600">${product.stock}</td>
          <td class="num"><span class="mono">${product.lowStock}</span></td>
          <td>${BW.badge(status)}</td>
          <td><div class="actions">
            <button class="btn btn-sm btn-outline" data-adj="${product.id}">${BW.icon('refresh')} Update Stock</button>
            <button class="btn-icon" data-thr="${product.id}" title="Low stock threshold">${BW.icon('alert')}</button>
          </div></td>
        </tr>`;
      },
      afterRender: () => {
        document.querySelectorAll('[data-adj]').forEach((button) => {
          button.addEventListener('click', () => adjust(products.find((product) => String(product.id) === button.dataset.adj)));
        });
        document.querySelectorAll('[data-thr]').forEach((button) => {
          button.addEventListener('click', () => threshold(products.find((product) => String(product.id) === button.dataset.thr)));
        });
      }
    });

    const tabs = document.getElementById('stockTabs');
    const renderTabs = () => {
      const counts = { '': products.length, 'In Stock': 0, 'Low Stock': 0, 'Out of Stock': 0 };
      products.forEach((product) => counts[stockStatus(product)]++);
      tabs.innerHTML = Object.keys(counts).map((status) =>
        `<button class="tab ${active === status ? 'active' : ''}" data-s="${status}">${status || 'All Products'}<span class="count">${counts[status]}</span></button>`
      ).join('');
      tabs.querySelectorAll('.tab').forEach((tab) => {
        tab.onclick = () => {
          active = tab.dataset.s;
          renderTabs();
          apply();
        };
      });
      document.getElementById('sUnits').textContent = F.num(BW.util.sum(products, (product) => product.stock));
      document.getElementById('sValue').textContent = F.money(BW.util.sum(products, (product) => product.stock * product.costPrice));
      document.getElementById('sRetail').textContent = F.money(BW.util.sum(products, (product) => product.stock * (product.salePrice || product.price)));
      document.getElementById('sAlerts').textContent = counts['Low Stock'] + counts['Out of Stock'];
    };
    const apply = () => {
      const query = search.value.trim().toLowerCase();
      table.setData(products.filter((product) =>
        (!active || stockStatus(product) === active)
        && (!query || (product.name + product.sku).toLowerCase().includes(query))
        && (!category.value || (product.category && product.category.slug === category.value))
      ));
    };
    const reload = async () => {
      ({ products } = await loadInventory());
      renderTabs();
      apply();
    };

    search.addEventListener('input', BW.util.debounce(apply, 150));
    category.addEventListener('change', apply);
    document.getElementById('exportInv').addEventListener('click', () => {
      const quote = (value) => `"${String(value == null ? '' : value).replace(/"/g, '""')}"`;
      const rows = [
        ['SKU', 'Product', 'Category', 'Stock', 'Sold', 'Threshold', 'Status'],
        ...products.map((product) => [
          product.sku,
          product.name,
          product.category ? product.category.name : '',
          product.stock,
          product.sold,
          product.lowStock,
          stockStatus(product)
        ])
      ];
      const csv = rows.map((row) => row.map(quote).join(',')).join('\n');
      const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
      const link = document.createElement('a');
      link.href = url;
      link.download = 'beastwear-inventory.csv';
      link.click();
      setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
    renderTabs();
    apply();

    function adjust(product) {
      if (!product) return;
      BW.ui.modal({
        title: 'Update Stock',
        size: 'sm',
        body: `<div class="flex mb-16"><img class="product-thumb lg" src="${esc(product.image)}" alt=""><div>
          <strong>${esc(product.name)}</strong><div class="small text-muted mono">${esc(product.sku)}</div>
          <div class="mt-8">Current stock: <b>${product.stock}</b> ${BW.badge(stockStatus(product))}</div>
        </div></div>
        <div class="form-group"><label class="form-label">Action</label><div class="tabs" id="adjMode">
          <button type="button" class="tab active" data-m="add">Add (+)</button>
          <button type="button" class="tab" data-m="remove">Remove (−)</button>
          <button type="button" class="tab" data-m="set">Set exact</button>
        </div></div>
        <div class="form-group"><label class="form-label" for="adjQty">Quantity</label><input type="number" class="form-control" id="adjQty" min="0" max="1000000" value="10"></div>
        <div class="form-group"><label class="form-label" for="adjReason">Reason</label><select class="form-control" id="adjReason">
          <option>New stock received</option><option>Stock count correction</option><option>Damaged / defective</option>
          <option>Returned by customer</option><option>Manual adjustment</option>
        </select></div><div class="alert small" id="adjPreview"></div>`,
        footer: '<button class="btn btn-outline" data-close>Cancel</button><button class="btn btn-primary" id="adjSave">Save Stock</button>',
        onOpen: (modal) => {
          let mode = 'add';
          const quantity = modal.querySelector('#adjQty');
          const preview = modal.querySelector('#adjPreview');
          const calculate = () => {
            const amount = Number(quantity.value) || 0;
            return mode === 'add' ? product.stock + amount : mode === 'remove' ? Math.max(0, product.stock - amount) : amount;
          };
          const updatePreview = () => {
            preview.innerHTML = `${BW.icon('info')} New stock will be <b>&nbsp;${calculate()}&nbsp;</b> units.`;
          };
          modal.querySelectorAll('#adjMode .tab').forEach((tab) => {
            tab.onclick = () => {
              modal.querySelectorAll('#adjMode .tab').forEach((item) => item.classList.remove('active'));
              tab.classList.add('active');
              mode = tab.dataset.m;
              updatePreview();
            };
          });
          quantity.addEventListener('input', updatePreview);
          updatePreview();
          modal.querySelector('#adjSave').onclick = async (event) => {
            const button = event.currentTarget;
            button.disabled = true;
            try {
              await request(`/admin/inventory/${product.id}/stock`, {
                mode,
                quantity: Number(quantity.value),
                reason: modal.querySelector('#adjReason').value
              });
              BW.ui.closeModal();
              BW.toast(`${product.name}: stock ${product.stock} → ${calculate()}`);
            } catch (error) {
              BW.toast(error.message, 'error');
              button.disabled = false;
              return;
            }
            try {
              await reload();
            } catch (error) {
              BW.toast(`Stock saved, but inventory could not refresh: ${error.message}`, 'error');
            }
          };
        }
      });
    }

    function threshold(product) {
      if (!product) return;
      BW.ui.modal({
        title: 'Low Stock Threshold',
        size: 'sm',
        body: `<p class="text-muted mb-16">You will be alerted when <b>${esc(product.name)}</b> drops to or below this quantity.</p>
          <div class="form-group"><label class="form-label" for="thrVal">Threshold</label>
          <input type="number" class="form-control" id="thrVal" min="0" max="1000000" value="${product.lowStock}"></div>`,
        footer: '<button class="btn btn-outline" data-close>Cancel</button><button class="btn btn-primary" id="thrSave">Save</button>',
        onOpen: (modal) => {
          modal.querySelector('#thrSave').onclick = async (event) => {
            const button = event.currentTarget;
            button.disabled = true;
            try {
              await request(`/admin/inventory/${product.id}/threshold`, {
                lowStock: Number(modal.querySelector('#thrVal').value)
              });
              BW.ui.closeModal();
              BW.toast('Threshold updated');
            } catch (error) {
              BW.toast(error.message, 'error');
              button.disabled = false;
              return;
            }
            try {
              await reload();
            } catch (error) {
              BW.toast(`Threshold saved, but inventory could not refresh: ${error.message}`, 'error');
            }
          };
        }
      });
    }
  }

  BW.ready(() => BW.boot(init));
})();
