/* ============================================================
   BeastWear.pk Admin — Core (BW namespace)
   - BW.config   : global configuration
   - BW.storage  : localStorage wrapper
   - BW.api      : DATA LAYER. Currently reads/writes localStorage.
                   To connect PHP/Laravel/MySQL: set BW.config.api.enabled=true
                   and the same method signatures will call fetch() endpoints.
   - BW.auth     : Laravel-session-backed admin identity and logout.
   - BW.layout   : renders sidebar/topbar on every authenticated page.
   - BW.ui       : modal, confirm, toast, dropdowns, validation
   - BW.table    : search/sort/pagination helper
   - BW.chart    : pure SVG/CSS charts
   ============================================================ */
(function () {
  'use strict';
  const BW = (window.BW = window.BW || {});

  /* ---------------- CONFIG ---------------- */
  BW.config = {
    brand: 'BeastWear.pk',
    short: 'BW',
    currency: 'Rs.',
    storagePrefix: 'bw_admin_',
    dataVersion: 4,
    api: { enabled: false, baseUrl: '/api/admin' }
  };

  /* ---------------- THEME (apply early) ---------------- */
  try {
    const t = localStorage.getItem(BW.config.storagePrefix + 'theme');
    document.documentElement.setAttribute('data-theme', t ? JSON.parse(t) : 'dark');
    if (JSON.parse(localStorage.getItem(BW.config.storagePrefix + 'sidebar_collapsed') || 'false')) document.body && document.body.classList.add('sidebar-collapsed');
  } catch (e) {}

  /* ---------------- STORAGE ---------------- */
  BW.storage = {
    key: (k) => BW.config.storagePrefix + k,
    get(k, fallback = null) { try { const v = localStorage.getItem(this.key(k)); return v === null ? fallback : JSON.parse(v); } catch (e) { return fallback; } },
    set(k, v) { localStorage.setItem(this.key(k), JSON.stringify(v)); },
    remove(k) { localStorage.removeItem(this.key(k)); }
  };

  /* ---------------- UTILS ---------------- */
  const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  BW.util = {
    esc,
    uid: (p = 'id') => p + '_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7),
    qs: (name) => new URLSearchParams(location.search).get(name),
    debounce(fn, ms = 250) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; },
    slugify: (s) => String(s).toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''),
    initials: (n) => String(n || '?').split(' ').map((x) => x[0]).join('').slice(0, 2).toUpperCase(),
    clamp: (n, a, b) => Math.max(a, Math.min(b, n)),
    sum: (arr, fn) => arr.reduce((s, x) => s + (fn ? fn(x) : x), 0),
    daysAgo: (d) => Math.floor((Date.now() - new Date(d)) / 864e5),
    today: () => new Date().toISOString().slice(0, 10),
    groupBy(arr, fn) { return arr.reduce((m, x) => { const k = fn(x); (m[k] = m[k] || []).push(x); return m; }, {}); }
  };

  BW.fmt = {
    money: (n, dec = 0) => BW.config.currency + ' ' + Number(n || 0).toLocaleString('en-PK', { maximumFractionDigits: dec }),
    num: (n) => Number(n || 0).toLocaleString('en-PK'),
    date(d) { if (!d) return '—'; const x = new Date(d); return x.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); },
    dateTime(d) { if (!d) return '—'; const x = new Date(d); return x.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + x.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }); },
    relative(d) { const m = Math.floor((Date.now() - new Date(d)) / 60000); if (m < 1) return 'just now'; if (m < 60) return m + 'm ago'; const h = Math.floor(m / 60); if (h < 24) return h + 'h ago'; const dd = Math.floor(h / 24); if (dd < 30) return dd + 'd ago'; return BW.fmt.date(d); },
    inputDate: (d) => new Date(d).toISOString().slice(0, 10)
  };

  /* ---------------- ICONS ---------------- */
  const P = {
    dashboard: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    box: '<path d="M21 16V8l-9-5-9 5v8l9 5 9-5z"/><path d="M3.3 7.5L12 12l8.7-4.5M12 12v10"/>',
    tag: '<path d="M20.6 13.4L13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><circle cx="7" cy="7" r="1.5"/>',
    layers: '<path d="M12 2l10 5-10 5L2 7l10-5z"/><path d="M2 12l10 5 10-5M2 17l10 5 10-5"/>',
    star: '<path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
    cart: '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>',
    users: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
    ticket: '<path d="M3 9V6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v3a3 3 0 0 0 0 6v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-3a3 3 0 0 0 0-6z"/><path d="M13 5v14"/>',
    image: '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
    chart: '<path d="M18 20V10M12 20V4M6 20v-6"/>',
    pie: '<path d="M21.2 15.9A10 10 0 1 1 8 2.8"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
    shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    settings: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
    truck: '<rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
    user: '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    search: '<circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/>',
    bell: '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/>',
    menu: '<path d="M3 12h18M3 6h18M3 18h18"/>',
    chevron: '<path d="M6 9l6 6 6-6"/>',
    sun: '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/>',
    moon: '<path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    minus: '<path d="M5 12h14"/>',
    edit: '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4z"/>',
    trash: '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>',
    eye: '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>',
    x: '<path d="M18 6L6 18M6 6l12 12"/>',
    check: '<path d="M20 6L9 17l-5-5"/>',
    print: '<path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
    phone: '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
    download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
    upload: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
    filter: '<path d="M22 3H2l8 9.5V19l4 2v-8.5z"/>',
    alert: '<path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/>',
    money: '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 12h.01M18 12h.01"/>',
    clock: '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    arrowLeft: '<path d="M19 12H5M12 19l-7-7 7-7"/>',
    arrowUp: '<path d="M12 19V5M5 12l7-7 7 7"/>',
    arrowDown: '<path d="M12 5v14M19 12l-7 7-7-7"/>',
    refresh: '<path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.5 9a9 9 0 0 1 14.9-3.4L23 10M1 14l4.6 4.4A9 9 0 0 0 20.5 15"/>',
    home: '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
    mail: '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/>',
    lock: '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    whatsapp: '<path d="M21 11.5a8.4 8.4 0 0 1-12.6 7.3L3 21l2.2-5.2A8.5 8.5 0 1 1 21 11.5z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 1a4 4 0 0 1-2-2l1-1-1-2z"/>',
    ban: '<circle cx="12" cy="12" r="10"/><path d="M4.9 4.9l14.2 14.2"/>',
    package: '<path d="M16.5 9.4l-9-5.2M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.7z"/><path d="M3.3 7L12 12l8.7-5M12 22V12"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
    more: '<circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>',
    trending: '<path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/>'
  };
  BW.icon = (name, cls = '') => `<svg class="icon ${cls}" viewBox="0 0 24 24" aria-hidden="true">${P[name] || P.info}</svg>`;

  /* ---------------- EMPTY LOCAL DATA ---------------- */
  function buildEmptyData() {
    return {
      categories: [], products: [], customers: [], orders: [], coupons: [], reviews: [], admins: [],
      settings: {}, shipping: {}, homepage: {}, notifications: []
    };
  }

  BW.data = {
    ensure() {
      if (BW.storage.get('data_version') !== BW.config.dataVersion) {
        const data = buildEmptyData();
        Object.keys(data).forEach((k) => BW.storage.set('data_' + k, data[k]));
        BW.storage.set('data_version', BW.config.dataVersion);
      }
    },
  };

  /* ---------------- API / DATA LAYER ---------------- */
  BW.api = {
    _read(r) { BW.data.ensure(); return BW.storage.get('data_' + r, Array.isArray(BW.storage.get('data_' + r)) ? [] : null); },
    _write(r, v) { BW.storage.set('data_' + r, v); },
    async requestOrders(path, method = 'GET', body) {
      const headers = { Accept: 'application/json' };
      const token = document.querySelector('meta[name="csrf-token"]')?.content;
      if (token) headers['X-CSRF-TOKEN'] = token;
      if (body) headers['Content-Type'] = 'application/json';
      const response = await fetch('/admin/data/orders' + path, {
        method,
        headers,
        credentials: 'same-origin',
        body: body ? JSON.stringify(body) : undefined
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Could not load order data.');
      return data;
    },
    async requestCustomers(path = '', method = 'GET', body) {
      const headers = { Accept: 'application/json' };
      const token = document.querySelector('meta[name="csrf-token"]')?.content;
      if (token) headers['X-CSRF-TOKEN'] = token;
      if (body) headers['Content-Type'] = 'application/json';
      const response = await fetch('/admin/data/customers' + path, {
        method,
        headers,
        credentials: 'same-origin',
        body: body ? JSON.stringify(body) : undefined
      });
      const data = await response.json();
      if (!response.ok) {
        const validationErrors = data.errors ? Object.values(data.errors).flat().join(' ') : '';
        throw new Error(validationErrors || data.message || 'Could not save customer data.');
      }
      return data;
    },
    async request(method, path, body) {
      const res = await fetch(BW.config.api.baseUrl + path, { method, headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: 'Bearer ' + (BW.auth.token() || '') }, body: body ? JSON.stringify(body) : undefined });
      if (!res.ok) throw new Error('API ' + res.status);
      return res.json();
    },
    async list(r) { if (r === 'orders') return this.requestOrders(''); if (r === 'customers') return this.requestCustomers(); if (BW.config.api.enabled) return this.request('GET', '/' + r); return JSON.parse(JSON.stringify(this._read(r) || [])); },
    async get(r, id) { if (r === 'orders') return this.requestOrders('/' + encodeURIComponent(id)); if (r === 'customers') return this.requestCustomers('/' + encodeURIComponent(id)); if (BW.config.api.enabled) return this.request('GET', `/${r}/${id}`); const all = this._read(r) || []; return JSON.parse(JSON.stringify(all.find((x) => String(x.id) === String(id)) || null)); },
    async create(r, data) {
      if (BW.config.api.enabled) return this.request('POST', '/' + r, data);
      const all = this._read(r) || [];
      if (data.id == null) { const nums = all.map((x) => Number(x.id)).filter((n) => !isNaN(n)); data.id = nums.length ? Math.max(...nums) + 1 : 1; }
      data.createdAt = data.createdAt || new Date().toISOString();
      all.unshift(data); this._write(r, all); return data;
    },
    async update(r, id, patch) {
      if (r === 'orders') return this.requestOrders('/' + encodeURIComponent(id), 'PUT', patch);
      if (r === 'customers') return this.requestCustomers('/' + encodeURIComponent(id), 'PUT', patch);
      if (BW.config.api.enabled) return this.request('PUT', `/${r}/${id}`, patch);
      const all = this._read(r) || []; const i = all.findIndex((x) => String(x.id) === String(id));
      if (i < 0) throw new Error('Not found');
      all[i] = Object.assign({}, all[i], patch, { updatedAt: new Date().toISOString() }); this._write(r, all); return all[i];
    },
    async remove(r, id) { if (BW.config.api.enabled) return this.request('DELETE', `/${r}/${id}`); this._write(r, (this._read(r) || []).filter((x) => String(x.id) !== String(id))); return true; },
    async getObject(key) { if (BW.config.api.enabled) return this.request('GET', '/' + key); return JSON.parse(JSON.stringify(this._read(key) || {})); },
    async saveObject(key, obj) { if (BW.config.api.enabled) return this.request('PUT', '/' + key, obj); this._write(key, obj); return obj; }
  };

  /* Domain helpers (derived data) */
  BW.domain = {
    stockStatus(p) { const s = Number(p.stock || 0); if (s <= 0) return 'Out of Stock'; if (s <= Number(p.lowStock || 10)) return 'Low Stock'; return 'In Stock'; },
    categoryName(cats, slug) { const c = cats.find((x) => x.slug === slug); return c ? c.name : slug; },
    customerStats(orders, customerId) {
      const mine = orders.filter((o) => o.customerId === customerId);
      const delivered = mine.filter((o) => o.status === 'Delivered');
      return { total: mine.length, delivered: delivered.length, cancelled: mine.filter((o) => o.status === 'Cancelled').length, spending: BW.util.sum(delivered, (o) => o.total), last: mine[0] ? mine[0].date : null };
    },
    ORDER_STATUSES: ['Pending', 'Confirmed', 'Processing', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned', 'Exchange Requested']
  };

  BW.badge = (status, extra = '') => {
    const s = String(status);
    const map = { Delivered: 'success', Active: 'success', Approved: 'success', 'In Stock': 'success', Paid: 'success', Yes: 'success', Pending: 'warning', 'Low Stock': 'warning', Processing: 'warning', Packed: 'warning', Unpaid: 'warning', 'Exchange Requested': 'warning', Cancelled: 'danger', 'Out of Stock': 'danger', Rejected: 'danger', Blocked: 'danger', Inactive: 'danger', Expired: 'danger', Returned: 'danger', Shipped: 'info', 'Out for Delivery': 'info', Confirmed: 'info', 'Super Admin': 'dark' };
    return `<span class="badge ${map[s] ? 'badge-' + map[s] : ''} ${extra}">${esc(s)}</span>`;
  };
  BW.avatar = (name, cls = '', src = '') => src ? `<img class="avatar ${cls}" src="${esc(src)}" alt="">` : `<span class="avatar ${cls}">${BW.util.initials(name)}</span>`;
  BW.stars = (n) => `<span class="rating">${'★'.repeat(n)}<span class="off">${'★'.repeat(5 - n)}</span></span>`;

  /* ---------------- AUTH ---------------- */
  BW.auth = {
    session() { return window.BW_ADMIN_USER || null; },
    token() { return null; },
    logout() {
      const form = document.getElementById('adminLogoutForm');
      if (!form) throw new Error('Admin logout form is missing.');
      form.submit();
    },
    require() {
      if (this.session()) return true;
      location.replace('/admin/login');
      return false;
    }
  };

  /* ---------------- UI: toast / modal / confirm / dropdown ---------------- */
  BW.ui = {
    toast(message, type = 'success', title) {
      let c = document.querySelector('.toast-container'); if (!c) { c = document.createElement('div'); c.className = 'toast-container'; document.body.appendChild(c); }
      const t = document.createElement('div'); t.className = 'toast ' + type;
      const ic = { success: 'check', error: 'alert', warning: 'alert', info: 'info' }[type] || 'info';
      t.innerHTML = `<span class="t-icon">${BW.icon(ic)}</span><div class="t-body">${title ? `<strong>${esc(title)}</strong>` : ''}<span>${esc(message)}</span></div><button class="t-close" aria-label="Close">${BW.icon('x')}</button>`;
      const kill = () => { t.classList.add('hide'); setTimeout(() => t.remove(), 250); };
      t.querySelector('.t-close').onclick = kill; c.appendChild(t); setTimeout(kill, 3800);
    },
    modal({ title, body, footer = '', size = '', onOpen }) {
      this.closeModal();
      const b = document.createElement('div'); b.className = 'modal-backdrop'; b.id = 'bwModal';
      b.innerHTML = `<div class="modal ${size}" role="dialog" aria-modal="true"><div class="modal-header"><h3>${esc(title)}</h3><button class="modal-close" data-close>${BW.icon('x')}</button></div><div class="modal-body">${body}</div>${footer ? `<div class="modal-footer">${footer}</div>` : ''}</div>`;
      document.body.appendChild(b);
      b.addEventListener('click', (e) => { if (e.target === b || e.target.closest('[data-close]')) this.closeModal(); });
      requestAnimationFrame(() => b.classList.add('open'));
      if (onOpen) onOpen(b);
      return b;
    },
    closeModal() { const m = document.getElementById('bwModal'); if (m) { m.classList.remove('open'); setTimeout(() => m.remove(), 200); } },
    confirm(message, onYes, { title = 'Are you sure?', yes = 'Delete', danger = true } = {}) {
      this.modal({ title, size: 'sm', body: `<div class="confirm-icon">${BW.icon('alert')}</div><p style="font-size:14px;line-height:1.6;color:var(--text-2)">${message}</p>`, footer: `<button class="btn btn-outline" data-close>Cancel</button><button class="btn ${danger ? 'btn-danger' : 'btn-primary'}" id="confirmYes">${esc(yes)}</button>`, onOpen: (m) => { m.querySelector('#confirmYes').onclick = async () => { await onYes(); this.closeModal(); }; } });
    },
    initDropdowns(root = document) {
      root.querySelectorAll('[data-dropdown]').forEach((btn) => {
        btn.addEventListener('click', (e) => { e.stopPropagation(); const menu = document.getElementById(btn.dataset.dropdown); const open = menu.classList.contains('open'); document.querySelectorAll('.dropdown-menu.open').forEach((m) => m.classList.remove('open')); if (!open) menu.classList.add('open'); });
      });
      document.addEventListener('click', (e) => { if (!e.target.closest('.dropdown-menu')) document.querySelectorAll('.dropdown-menu.open').forEach((m) => m.classList.remove('open')); });
    },
    validate(form) {
      let ok = true, first = null;
      form.querySelectorAll('[required],[data-validate]').forEach((el) => {
        const g = el.closest('.form-group'); let msg = '';
        const v = el.value.trim();
        if (el.hasAttribute('required') && !v) msg = 'This field is required.';
        else if (v && el.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) msg = 'Enter a valid email address.';
        else if (v && el.type === 'number' && el.min !== '' && Number(v) < Number(el.min)) msg = `Minimum value is ${el.min}.`;
        else if (v && el.dataset.validate === 'phone' && !/^0?3\d{2}-?\d{7}$/.test(v.replace(/\s/g, ''))) msg = 'Enter a valid Pakistani mobile number (03xx-xxxxxxx).';
        else if (v && el.minLength > 0 && v.length < el.minLength) msg = `Minimum ${el.minLength} characters.`;
        else if (el.dataset.match && v !== form.querySelector(el.dataset.match).value) msg = 'Values do not match.';
        if (g) { g.classList.toggle('has-error', !!msg); let e = g.querySelector('.form-error'); if (!e) { e = document.createElement('div'); e.className = 'form-error'; g.appendChild(e); } e.textContent = msg; }
        el.classList.toggle('is-invalid', !!msg);
        if (msg) { ok = false; first = first || el; }
      });
      if (first) first.focus();
      return ok;
    },
    formData(form) { const o = {}; new FormData(form).forEach((v, k) => (o[k] = v)); form.querySelectorAll('input[type=checkbox][name]').forEach((c) => { if (!c.closest('[data-multi]')) o[c.name] = c.checked; }); return o; },
    readFile(file) { return new Promise((res, rej) => { const r = new FileReader(); r.onload = () => res(r.result); r.onerror = rej; r.readAsDataURL(file); }); }
  };
  BW.toast = (m, t, title) => BW.ui.toast(m, t, title);

  /* ---------------- TABLE helper ---------------- */
  BW.pager = function (el, { total, page, size, onChange }) {
    const pages = Math.max(1, Math.ceil(total / size)); if (!el) return;
    let html = `<button ${page <= 1 ? 'disabled' : ''} data-p="${page - 1}">‹</button>`;
    const range = []; for (let i = 1; i <= pages; i++) if (i === 1 || i === pages || Math.abs(i - page) <= 1) range.push(i);
    let prev = 0; range.forEach((i) => { if (i - prev > 1) html += '<span class="dots">…</span>'; html += `<button class="${i === page ? 'active' : ''}" data-p="${i}">${i}</button>`; prev = i; });
    html += `<button ${page >= pages ? 'disabled' : ''} data-p="${page + 1}">›</button>`;
    el.className = 'pagination'; el.innerHTML = html;
    el.querySelectorAll('button[data-p]').forEach((b) => (b.onclick = () => onChange(Number(b.dataset.p))));
  };
  BW.table = function (opts) {
    const st = { page: 1, size: opts.pageSize || 10, sortKey: opts.sortKey || null, sortDir: opts.sortDir || -1, data: opts.data || [] };
    const t = {
      state: st,
      setData(d) { st.data = d; st.page = 1; t.render(); },
      render() {
        let d = [...st.data];
        if (st.sortKey) d.sort((a, b) => { let x = a[st.sortKey], y = b[st.sortKey]; if (typeof x === 'string' && !isNaN(Date.parse(x)) && st.sortKey.toLowerCase().includes('date')) { x = Date.parse(x); y = Date.parse(y); } if (typeof x === 'string') { x = x.toLowerCase(); y = String(y || '').toLowerCase(); } return (x > y ? 1 : x < y ? -1 : 0) * st.sortDir; });
        const total = d.length, pages = Math.max(1, Math.ceil(total / st.size)); if (st.page > pages) st.page = pages;
        const start = (st.page - 1) * st.size, slice = d.slice(start, start + st.size);
        opts.tbody.innerHTML = slice.length ? slice.map((row, i) => opts.row(row, start + i)).join('') : `<tr><td colspan="99" class="table-empty">${BW.icon('search')}<div style="margin-top:8px">${opts.empty || 'No records found'}</div></td></tr>`;
        if (opts.pager) BW.pager(opts.pager, { total, page: st.page, size: st.size, onChange: (p) => { st.page = p; t.render(); } });
        if (opts.info) opts.info.textContent = total ? `Showing ${start + 1}–${Math.min(start + st.size, total)} of ${total}` : 'No results';
        if (opts.afterRender) opts.afterRender(slice);
      }
    };
    if (opts.table) opts.table.querySelectorAll('th[data-sort]').forEach((th) => th.addEventListener('click', () => { const k = th.dataset.sort; if (st.sortKey === k) st.sortDir *= -1; else { st.sortKey = k; st.sortDir = 1; } opts.table.querySelectorAll('th').forEach((h) => h.classList.remove('sort-asc', 'sort-desc')); th.classList.add(st.sortDir > 0 ? 'sort-asc' : 'sort-desc'); t.render(); }));
    t.render(); return t;
  };

  /* ---------------- CHARTS (pure SVG) ---------------- */
  BW.chart = {
    _tip(el) { let t = el.querySelector('.chart-tooltip'); if (!t) { t = document.createElement('div'); t.className = 'chart-tooltip'; el.appendChild(t); } return t; },
    bars(el, { labels, values, format = (v) => v, height = 260 }) {
      const W = 800, H = height, padL = 46, padB = 28, padT = 14; const max = Math.max(1, ...values) * 1.15;
      const n = values.length, bw = (W - padL) / n, gap = Math.min(bw * 0.35, 14);
      let g = ''; for (let i = 0; i <= 4; i++) { const y = padT + ((H - padB - padT) * i) / 4; g += `<line class="grid-line" x1="${padL}" x2="${W}" y1="${y}" y2="${y}"/><text class="axis-text" x="${padL - 8}" y="${y + 4}" text-anchor="end">${BW.chart._short(max - (max * i) / 4)}</text>`; }
      const step = Math.ceil(n / 12);
      const bars = values.map((v, i) => { const h = ((H - padB - padT) * v) / max, x = padL + i * bw + gap / 2, y = H - padB - h; return `<rect class="bar" data-i="${i}" x="${x}" y="${y}" width="${bw - gap}" height="${h}" rx="4"/>${i % step === 0 ? `<text class="axis-text" x="${x + (bw - gap) / 2}" y="${H - 8}" text-anchor="middle">${esc(labels[i])}</text>` : ''}`; }).join('');
      el.classList.add('chart'); el.innerHTML = `<svg viewBox="0 0 ${W} ${H}" preserveAspectRatio="none" style="height:${H}px">${g}${bars}</svg>`;
      const tip = this._tip(el);
      el.querySelectorAll('.bar').forEach((b) => { b.addEventListener('mouseenter', (e) => { const i = +b.dataset.i; tip.innerHTML = `${esc(labels[i])}: ${format(values[i])}`; tip.classList.add('show'); }); b.addEventListener('mousemove', (e) => { const r = el.getBoundingClientRect(); tip.style.left = e.clientX - r.left + 'px'; tip.style.top = e.clientY - r.top - 6 + 'px'; }); b.addEventListener('mouseleave', () => tip.classList.remove('show')); });
    },
    line(el, { labels, values, format = (v) => v, height = 260 }) {
      const W = 800, H = height, padL = 46, padB = 28, padT = 14; const max = Math.max(1, ...values) * 1.15; const n = values.length;
      const px = (i) => padL + (n === 1 ? 0 : ((W - padL - 10) * i) / (n - 1)), py = (v) => H - padB - ((H - padB - padT) * v) / max;
      let g = ''; for (let i = 0; i <= 4; i++) { const y = padT + ((H - padB - padT) * i) / 4; g += `<line class="grid-line" x1="${padL}" x2="${W}" y1="${y}" y2="${y}"/><text class="axis-text" x="${padL - 8}" y="${y + 4}" text-anchor="end">${BW.chart._short(max - (max * i) / 4)}</text>`; }
      const pts = values.map((v, i) => `${px(i)},${py(v)}`); const step = Math.ceil(n / 10);
      const xl = labels.map((l, i) => (i % step === 0 ? `<text class="axis-text" x="${px(i)}" y="${H - 8}" text-anchor="middle">${esc(l)}</text>` : '')).join('');
      const dots = values.map((v, i) => `<circle class="pt" data-i="${i}" cx="${px(i)}" cy="${py(v)}" r="3.5"/>`).join('');
      el.classList.add('chart'); el.innerHTML = `<svg viewBox="0 0 ${W} ${H}" preserveAspectRatio="none" style="height:${H}px"><defs><linearGradient id="areaGrad" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="currentColor" stop-opacity=".22"/><stop offset="1" stop-color="currentColor" stop-opacity="0"/></linearGradient></defs>${g}<path class="area" d="M${pts[0]} L${pts.join(' L')} L${px(n - 1)},${H - padB} L${px(0)},${H - padB} Z" style="color:var(--text)"/><polyline class="line" points="${pts.join(' ')}"/>${dots}${xl}</svg>`;
      const tip = this._tip(el);
      el.querySelectorAll('.pt').forEach((b) => { b.addEventListener('mouseenter', () => { const i = +b.dataset.i; tip.innerHTML = `${esc(labels[i])}: ${format(values[i])}`; tip.classList.add('show'); }); b.addEventListener('mousemove', (e) => { const r = el.getBoundingClientRect(); tip.style.left = e.clientX - r.left + 'px'; tip.style.top = e.clientY - r.top - 6 + 'px'; }); b.addEventListener('mouseleave', () => tip.classList.remove('show')); });
    },
    donut(el, { items, centerLabel = 'Total', format = (v) => v }) {
      const total = Math.max(1, BW.util.sum(items, (i) => i.value)); const shades = ['#f4f4f4', '#bdbdbd', '#8a8a8a', '#5e5e5e', '#3d3d3d', '#2a2a2a', '#1f1f1f', '#181818'];
      const light = document.documentElement.getAttribute('data-theme') === 'light'; const cols = light ? ['#111', '#3a3a3a', '#636363', '#8c8c8c', '#b3b3b3', '#cfcfcf', '#e0e0e0', '#ebebeb'] : shades;
      let acc = 0; const segs = items.map((it, i) => { const a = (it.value / total) * 360; const s = `${cols[i % cols.length]} ${acc}deg ${acc + a}deg`; acc += a; return s; });
      el.innerHTML = `<div class="donut"><div class="ring" style="background:conic-gradient(${segs.join(',')})"><div class="center"><strong>${format(total)}</strong><span>${esc(centerLabel)}</span></div></div><div class="legend">${items.map((it, i) => `<div><i style="background:${cols[i % cols.length]}"></i>${esc(it.label)}<b>${format(it.value)}</b><small>${Math.round((it.value / total) * 100)}%</small></div>`).join('')}</div></div>`;
    },
    hbars(el, { items, format = (v) => v }) { const max = Math.max(1, ...items.map((i) => i.value)); el.innerHTML = `<div class="hbar-list">${items.map((it) => `<div class="hbar"><div class="hbar-top"><span>${esc(it.label)}</span><b>${format(it.value)}</b></div><div class="hbar-track"><div class="hbar-fill" style="width:${(it.value / max) * 100}%"></div></div></div>`).join('')}</div>`; },
    _short(n) { return n >= 1e6 ? (n / 1e6).toFixed(1) + 'M' : n >= 1e3 ? Math.round(n / 1e3) + 'k' : Math.round(n); }
  };

  /* ---------------- LAYOUT ---------------- */
  BW.layout = {
    nav: [
      { section: 'Main', items: [{ label: 'Dashboard', href: '/admin/dashboard', icon: 'dashboard', page: 'dashboard' }] },
      { section: 'Catalog', items: [{ label: 'Products', href: '/admin/products', icon: 'box', page: 'products' }, { label: 'Categories', href: '/admin/categories', icon: 'tag', page: 'categories' }, { label: 'Inventory', href: '/admin/inventory', icon: 'layers', page: 'inventory', count: 'lowStock' }, { label: 'Reviews', href: '/admin/reviews', icon: 'star', page: 'reviews', count: 'pendingReviews' }] },
      { section: 'Sales', items: [{ label: 'Orders', href: '/admin/orders', icon: 'cart', page: 'orders', count: 'pendingOrders' }, { label: 'Customers', href: '/admin/customers', icon: 'users', page: 'customers' }, { label: 'Coupons', href: '/admin/coupons', icon: 'ticket', page: 'coupons' }] },
      { section: 'Website', items: [{ label: 'Homepage & Banners', href: '/admin/banners', icon: 'image', page: 'banners' }] },
      { section: 'Reports', items: [{ label: 'Sales Reports', href: '/admin/reports', icon: 'chart', page: 'reports' }, { label: 'Analytics', href: '/admin/reports#analytics', icon: 'pie', page: 'analytics' }] },
      { section: 'Administration', items: [{ label: 'Admin Users', href: '/admin/admins', icon: 'shield', page: 'admins' }, { label: 'Settings', href: '/admin/settings', icon: 'settings', page: 'settings' }, { label: 'Shipping Settings', href: 'shipping-/admin/settings', icon: 'truck', page: 'shipping' }] },
      { section: 'Account', items: [{ label: 'My Profile', href: '/admin/settings/profile', icon: 'user', page: 'profile' }, { label: 'Logout', href: '#logout', icon: 'logout', page: 'logout' }] }
    ],
    async render() {
      if (!BW.auth.require()) return;
      const s = BW.auth.session();
      const [products, orders, reviews] = await Promise.all([BW.api.list('products'), BW.api.list('orders'), BW.api.list('reviews')]);
      const counts = { lowStock: products.filter((p) => BW.domain.stockStatus(p) !== 'In Stock').length, pendingOrders: orders.filter((o) => o.status === 'Pending').length, pendingReviews: reviews.filter((r) => r.status === 'Pending').length };
      document.querySelectorAll('[data-admin-count]').forEach((el) => {
        const count = counts[el.dataset.adminCount] || 0;
        el.textContent = count ? String(count) : '';
        el.hidden = !count;
      });
      document.querySelectorAll('[data-admin-icon]').forEach((el) => {
        el.innerHTML = BW.icon(el.dataset.adminIcon);
      });
      document.querySelectorAll('[data-admin-name]').forEach((el) => { el.textContent = s.name; });
      document.querySelectorAll('[data-admin-email]').forEach((el) => { el.textContent = s.email; });
      document.querySelectorAll('[data-admin-avatar]').forEach((el) => { el.textContent = BW.util.initials(s.name); });

      const notifs = await BW.api.list('notifications'); const unread = notifs.filter((n) => !n.read).length;
      const theme = document.documentElement.getAttribute('data-theme');
      const themeButton = document.getElementById('themeToggle');
      if (themeButton) themeButton.innerHTML = BW.icon(theme === 'light' ? 'moon' : 'sun');
      const notificationDot = document.querySelector('[data-notification-dot]');
      if (notificationDot) notificationDot.hidden = unread === 0;
      const notificationItems = document.getElementById('notificationItems');
      if (notificationItems) notificationItems.innerHTML = notifs.slice(0, 6).map((n) => `<div class="notif-item ${n.read ? '' : 'unread'}" data-type="${n.type}"><div class="n-icon">${BW.icon({ order: 'cart', stock: 'alert', review: 'star' }[n.type] || 'bell')}</div><div class="n-body"><p>${esc(n.text)}</p><small>${BW.fmt.relative(n.time)}</small></div></div>`).join('');
      this.bind(products, orders);
    },
    bind(products, orders) {
      const body = document.body;
      const toggle = document.getElementById('sidebarToggle');
      toggle && toggle.addEventListener('click', () => { if (window.innerWidth <= 1024) body.classList.toggle('sidebar-open'); else { body.classList.toggle('sidebar-collapsed'); BW.storage.set('sidebar_collapsed', body.classList.contains('sidebar-collapsed')); } });
      const ov = document.getElementById('sidebarOverlay'); ov && ov.addEventListener('click', () => body.classList.remove('sidebar-open'));
      document.querySelectorAll('[data-logout]').forEach((a) => a.addEventListener('click', (e) => { e.preventDefault(); BW.ui.confirm('You will be signed out of the BeastWear.pk admin panel.', () => BW.auth.logout(), { title: 'Logout?', yes: 'Logout', danger: false }); }));
      const th = document.getElementById('themeToggle'); th && th.addEventListener('click', () => { const cur = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light'; document.documentElement.setAttribute('data-theme', cur); BW.storage.set('theme', cur); th.innerHTML = BW.icon(cur === 'light' ? 'moon' : 'sun'); });
      BW.ui.initDropdowns();
      const mar = document.getElementById('markAllRead'); mar && mar.addEventListener('click', async (e) => { e.preventDefault(); const n = await BW.api.list('notifications'); await BW.api.saveObject('notifications', n.map((x) => ({ ...x, read: true }))); document.querySelectorAll('.notif-item').forEach((i) => i.classList.remove('unread')); document.querySelector('[data-dropdown=notifMenu] .dot')?.remove(); BW.toast('All notifications marked as read', 'info'); });
      document.querySelectorAll('.notif-item').forEach((i) => i.addEventListener('click', () => (location.href = { order: '/admin/orders', stock: '/admin/inventory', review: '/admin/reviews' }[i.dataset.type] || '/admin/dashboard')));
      /* global search */
      const gs = document.getElementById('globalSearch'), res = document.getElementById('searchResults');
      if (gs) {
        document.addEventListener('keydown', (e) => { if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) { e.preventDefault(); gs.focus(); } if (e.key === 'Escape') { res.classList.remove('open'); BW.ui.closeModal(); } });
        gs.addEventListener('input', BW.util.debounce(async () => {
          const q = gs.value.trim().toLowerCase(); if (q.length < 2) { res.classList.remove('open'); return; }
          const customers = await BW.api.list('customers');
          const p = products.filter((x) => (x.name + x.sku).toLowerCase().includes(q)).slice(0, 4);
          const o = orders.filter((x) => (x.id + x.customer + x.phone).toLowerCase().includes(q)).slice(0, 4);
          const c = customers.filter((x) => (x.name + x.phone + x.email).toLowerCase().includes(q)).slice(0, 4);
          const grp = (t, rows) => rows.length ? `<div class="search-group"><div class="search-group-title">${t}</div>${rows.join('')}</div>` : '';
          res.innerHTML = grp('Products', p.map((x) => `<a class="search-item" href="/admin/products/edit?id=${x.id}"><img class="product-thumb sm" src="${x.image}" alt=""><b>${esc(x.name)}</b><small>${x.sku}</small></a>`)) + grp('Orders', o.map((x) => `<a class="search-item" href="/admin/orders/show?id=${x.id}">${BW.icon('cart')}<b>${x.id}</b> ${esc(x.customer)}<small>${BW.fmt.money(x.total)}</small></a>`)) + grp('Customers', c.map((x) => `<a class="search-item" href="/admin/customers/show?id=${x.id}">${BW.avatar(x.name, 'sm')}<b>${esc(x.name)}</b><small>${esc(x.city)}</small></a>`)) || `<div class="search-group"><div class="search-item">No results for “${esc(q)}”</div></div>`;
          res.classList.add('open');
        }, 200));
        document.addEventListener('click', (e) => { if (!e.target.closest('.topbar-search')) res.classList.remove('open'); });
      }
      window.addEventListener('resize', () => { if (window.innerWidth > 1024) body.classList.remove('sidebar-open'); });
    }
  };

  /* Boot layout for authenticated pages */
  BW.ready = (fn) => { if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn(); };
  BW.boot = async function (pageInit) {
    BW.data.ensure();
    if (document.body.dataset.page && document.body.dataset.page !== 'login') {
      if (!BW.auth.require()) return;
      await BW.layout.render();
    }
    if (pageInit) { try { await pageInit(); } catch (e) { console.error(e); BW.toast(e.message || 'Something went wrong', 'error'); } }
  };
})();
