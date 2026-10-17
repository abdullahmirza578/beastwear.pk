/* BeastWear.pk Admin — Dashboard & Reports */
(function () {
  'use strict';
  const { esc } = BW.util; const F = BW.fmt;

  /* ---- shared aggregation helpers ---- */
  function seriesFor(orders, period, range) {
    const valid = orders.filter((o) => !['Cancelled', 'Returned'].includes(o.status));
    const labels = [], values = [], counts = [];
    const now = new Date();
    if (period === 'daily') { const n = range || 14; for (let i = n - 1; i >= 0; i--) { const d = new Date(now); d.setDate(d.getDate() - i); const key = d.toISOString().slice(0, 10); const day = valid.filter((o) => o.date.slice(0, 10) === key); labels.push(d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })); values.push(BW.util.sum(day, (o) => o.total)); counts.push(day.length); } }
    else if (period === 'weekly') { const n = range || 10; for (let i = n - 1; i >= 0; i--) { const end = new Date(now); end.setDate(end.getDate() - i * 7); const start = new Date(end); start.setDate(start.getDate() - 7); const wk = valid.filter((o) => { const t = new Date(o.date); return t > start && t <= end; }); labels.push('W' + (n - i)); values.push(BW.util.sum(wk, (o) => o.total)); counts.push(wk.length); } }
    else if (period === 'monthly') { const n = range || 6; for (let i = n - 1; i >= 0; i--) { const d = new Date(now.getFullYear(), now.getMonth() - i, 1); const key = d.toISOString().slice(0, 7); const mo = valid.filter((o) => o.date.slice(0, 7) === key); labels.push(d.toLocaleDateString('en-GB', { month: 'short' })); values.push(BW.util.sum(mo, (o) => o.total)); counts.push(mo.length); } }
    else { for (let i = 2; i >= 0; i--) { const y = now.getFullYear() - i; const yr = valid.filter((o) => o.date.slice(0, 4) === String(y)); labels.push(String(y)); values.push(BW.util.sum(yr, (o) => o.total)); counts.push(yr.length); } }
    return { labels, values, counts };
  }
  BW.reportsSeries = seriesFor;

  /* ================= DASHBOARD ================= */
  async function initDashboard() {
    const [orders, products, customers, cats] = await Promise.all([BW.api.list('orders'), BW.api.list('products'), BW.api.list('customers'), BW.api.list('categories')]);
    const delivered = orders.filter((o) => o.status === 'Delivered');
    const sales = BW.util.sum(delivered, (o) => o.total);
    const low = products.filter((p) => BW.domain.stockStatus(p) !== 'In Stock');
    const last30 = orders.filter((o) => BW.util.daysAgo(o.date) <= 30 && o.status !== 'Cancelled'), prev30 = orders.filter((o) => { const d = BW.util.daysAgo(o.date); return d > 30 && d <= 60 && o.status !== 'Cancelled'; });
    const growth = prev30.length ? Math.round(((BW.util.sum(last30, (o) => o.total) - BW.util.sum(prev30, (o) => o.total)) / BW.util.sum(prev30, (o) => o.total)) * 100) : 0;
    const stats = [
      { label: 'Total Sales', value: F.money(sales), icon: 'money', trend: `${growth >= 0 ? '+' : ''}${growth}% vs last 30 days`, up: growth >= 0, accent: true },
      { label: 'Total Orders', value: F.num(orders.length), icon: 'cart', trend: `${last30.length} in last 30 days`, up: true },
      { label: 'Pending Orders', value: F.num(orders.filter((o) => o.status === 'Pending').length), icon: 'clock', trend: 'Need confirmation', href: '/admin/orders?status=Pending' },
      { label: 'Delivered Orders', value: F.num(delivered.length), icon: 'check', trend: `${Math.round((delivered.length / orders.length) * 100)}% delivery rate`, up: true },
      { label: 'Customers', value: F.num(customers.length), icon: 'users', trend: `${customers.filter((c) => BW.util.daysAgo(c.registeredAt) <= 30).length} new this month`, up: true },
      { label: 'Products', value: F.num(products.length), icon: 'box', trend: `${products.filter((p) => p.status === 'Active').length} active` },
      { label: 'Low Stock', value: F.num(low.length), icon: 'alert', trend: `${products.filter((p) => p.stock <= 0).length} out of stock`, up: false, href: '/admin/inventory' }
    ];
    document.getElementById('statsGrid').innerHTML = stats.map((s) => `<a href="${s.href || '#'}" class="stat-card ${s.accent ? 'accent' : ''}" ${s.href ? '' : 'onclick="return false"'}><div class="stat-top"><span class="stat-label">${s.label}</span><span class="stat-icon">${BW.icon(s.icon)}</span></div><div class="stat-value">${s.value}</div><div class="stat-trend ${s.up === true ? 'up' : s.up === false ? 'down' : ''}">${s.up === true ? BW.icon('arrowUp') : s.up === false ? BW.icon('arrowDown') : ''}${s.trend}</div></a>`).join('');

    /* chart */
    const chartEl = document.getElementById('salesChart'); let period = 'daily', type = 'bars';
    const draw = () => { const s = seriesFor(orders, period); BW.chart[type](chartEl, { labels: s.labels, values: s.values, format: (v) => F.money(v) }); document.getElementById('chartTotal').textContent = F.money(BW.util.sum(s.values)); document.getElementById('chartOrders').textContent = F.num(BW.util.sum(s.counts)) + ' orders'; document.getElementById('chartAvg').textContent = F.money(BW.util.sum(s.counts) ? BW.util.sum(s.values) / BW.util.sum(s.counts) : 0); };
    document.querySelectorAll('#periodTabs .tab').forEach((t) => t.addEventListener('click', () => { document.querySelectorAll('#periodTabs .tab').forEach((x) => x.classList.remove('active')); t.classList.add('active'); period = t.dataset.period; draw(); }));
    document.getElementById('chartType').addEventListener('click', (e) => { type = type === 'bars' ? 'line' : 'bars'; e.currentTarget.innerHTML = BW.icon(type === 'bars' ? 'trending' : 'chart'); draw(); });
    draw(); window.addEventListener('resize', BW.util.debounce(draw, 200));

    /* recent orders */
    document.getElementById('recentOrders').innerHTML = orders.slice(0, 8).map((o) => `<tr><td><a href="/admin/orders/show?id=${o.id}" class="fw-600">${o.id}</a></td><td><div class="flex">${BW.avatar(o.customer, 'sm')}<div><div class="fw-600">${esc(o.customer)}</div><div class="small text-muted">${esc(o.address.city)}</div></div></div></td><td class="text-muted">${F.date(o.date)}</td><td class="amount">${F.money(o.total)}</td><td><span class="small">${esc(o.payment)}</span></td><td>${BW.badge(o.status)}</td><td><div class="actions"><a class="btn-icon" href="/admin/orders/show?id=${o.id}" title="View">${BW.icon('eye')}</a></div></td></tr>`).join('');

    /* top selling */
    const soldMap = {}; orders.filter((o) => o.status !== 'Cancelled').forEach((o) => o.items.forEach((i) => { soldMap[i.productId] = soldMap[i.productId] || { qty: 0, rev: 0 }; soldMap[i.productId].qty += i.qty; soldMap[i.productId].rev += i.total; }));
    const top = products.map((p) => ({ ...p, soldQ: (soldMap[p.id]?.qty || 0) + p.sold, rev: (soldMap[p.id]?.rev || 0) + p.sold * (p.salePrice || p.price) })).sort((a, b) => b.rev - a.rev).slice(0, 6);
    document.getElementById('topProducts').innerHTML = top.map((p, i) => `<div class="list-row"><span class="rank">${i + 1}</span><img class="product-thumb" src="${p.image}" alt=""><div class="grow"><strong>${esc(p.name)}</strong><span>${p.soldQ} sold · ${esc(BW.domain.categoryName(cats, p.category))}</span></div><div class="end"><strong>${F.money(p.rev)}</strong><span>revenue</span></div></div>`).join('');

    /* low stock */
    document.getElementById('lowStock').innerHTML = low.length ? low.sort((a, b) => a.stock - b.stock).map((p) => `<tr><td><div class="cell-product"><img class="product-thumb sm" src="${p.image}" alt=""><span class="name">${esc(p.name)}</span></div></td><td class="mono">${p.sku}</td><td class="num fw-600">${p.stock}</td><td>${BW.badge(BW.domain.stockStatus(p))}</td></tr>`).join('') : '<tr><td colspan="4" class="table-empty">All products are well stocked</td></tr>';

    /* status breakdown */
    const byStatus = BW.util.groupBy(orders, (o) => o.status);
    BW.chart.hbars(document.getElementById('statusChart'), { items: BW.domain.ORDER_STATUSES.filter((s) => byStatus[s]).map((s) => ({ label: s, value: byStatus[s].length })) });
  }

  /* ================= REPORTS ================= */
  async function initReports() {
    const [orders, products, customers, cats] = await Promise.all([BW.api.list('orders'), BW.api.list('products'), BW.api.list('customers'), BW.api.list('categories')]);
    const from = document.getElementById('dateFrom'), to = document.getElementById('dateTo');
    const d = new Date(); to.value = BW.fmt.inputDate(d); d.setDate(d.getDate() - 30); from.value = BW.fmt.inputDate(d);
    let period = 'daily';
    document.querySelectorAll('[data-range]').forEach((b) => b.addEventListener('click', () => { const n = Number(b.dataset.range); const t = new Date(), f = new Date(); f.setDate(f.getDate() - n); from.value = BW.fmt.inputDate(f); to.value = BW.fmt.inputDate(t); run(); }));
    document.querySelectorAll('#reportPeriod .tab').forEach((t) => t.addEventListener('click', () => { document.querySelectorAll('#reportPeriod .tab').forEach((x) => x.classList.remove('active')); t.classList.add('active'); period = t.dataset.period; run(); }));
    document.getElementById('applyFilter').addEventListener('click', run);
    document.getElementById('exportCsv').addEventListener('click', () => { const rows = filtered().map((o) => [o.id, o.customer, o.date.slice(0, 10), o.address.city, o.payment, o.status, o.total]); const csv = [['Order', 'Customer', 'Date', 'City', 'Payment', 'Status', 'Total'], ...rows].map((r) => r.map((v) => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n'); const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' })); a.download = `beastwear-sales-${from.value}-to-${to.value}.csv`; a.click(); BW.toast('CSV exported', 'success'); });
    function filtered() { const f = new Date(from.value), t = new Date(to.value); t.setHours(23, 59, 59); return orders.filter((o) => { const x = new Date(o.date); return x >= f && x <= t; }); }
    function run() {
      const list = filtered(); const delivered = list.filter((o) => o.status === 'Delivered'); const rev = BW.util.sum(delivered, (o) => o.total);
      const st = [['Total Revenue', F.money(rev), 'money', true], ['Total Orders', F.num(list.length), 'cart'], ['Avg. Order Value', F.money(list.length ? BW.util.sum(list, (o) => o.total) / list.length : 0), 'trending'], ['Delivered', F.num(delivered.length), 'check'], ['Cancelled', F.num(list.filter((o) => o.status === 'Cancelled').length), 'x'], ['Returned', F.num(list.filter((o) => ['Returned', 'Exchange Requested'].includes(o.status)).length), 'refresh']];
      document.getElementById('reportStats').innerHTML = st.map((s) => `<div class="stat-card ${s[3] ? 'accent' : ''}"><div class="stat-top"><span class="stat-label">${s[0]}</span><span class="stat-icon">${BW.icon(s[2])}</span></div><div class="stat-value">${s[1]}</div></div>`).join('');
      const range = period === 'daily' ? Math.min(60, Math.max(7, Math.round((new Date(to.value) - new Date(from.value)) / 864e5) + 1)) : period === 'weekly' ? 12 : period === 'monthly' ? 12 : 3;
      const s = seriesFor(list.length ? list : orders, period, range);
      BW.chart.bars(document.getElementById('revenueChart'), { labels: s.labels, values: s.values, format: (v) => F.money(v) });
      BW.chart.line(document.getElementById('ordersChart'), { labels: s.labels, values: s.counts, format: (v) => v + ' orders', height: 220 });
      /* tables */
      const pm = {}; list.filter((o) => o.status !== 'Cancelled').forEach((o) => o.items.forEach((i) => { pm[i.productId] = pm[i.productId] || { name: i.name, image: i.image, qty: 0, rev: 0 }; pm[i.productId].qty += i.qty; pm[i.productId].rev += i.total; }));
      const bp = Object.values(pm).sort((a, b) => b.rev - a.rev).slice(0, 8);
      document.getElementById('bestProducts').innerHTML = bp.map((p, i) => `<tr><td><div class="cell-product"><span class="rank">${i + 1}</span><img class="product-thumb sm" src="${p.image}" alt=""><span class="name">${esc(p.name)}</span></div></td><td class="num">${p.qty}</td><td class="amount num">${F.money(p.rev)}</td></tr>`).join('') || '<tr><td colspan="3" class="table-empty">No data in range</td></tr>';
      const cm = {}; delivered.forEach((o) => { cm[o.customerId] = cm[o.customerId] || { name: o.customer, city: o.address.city, n: 0, t: 0, id: o.customerId }; cm[o.customerId].n++; cm[o.customerId].t += o.total; });
      document.getElementById('bestCustomers').innerHTML = Object.values(cm).sort((a, b) => b.t - a.t).slice(0, 8).map((c, i) => `<tr><td><div class="flex"><span class="rank">${i + 1}</span>${BW.avatar(c.name, 'sm')}<a href="/admin/customers/show?id=${c.id}" class="fw-600">${esc(c.name)}</a></div></td><td class="text-muted">${esc(c.city)}</td><td class="num">${c.n}</td><td class="amount num">${F.money(c.t)}</td></tr>`).join('') || '<tr><td colspan="4" class="table-empty">No data in range</td></tr>';
      const cityM = BW.util.groupBy(list.filter((o) => o.status !== 'Cancelled'), (o) => o.address.city);
      const cityRows = Object.keys(cityM).map((c) => ({ label: c, n: cityM[c].length, value: BW.util.sum(cityM[c], (o) => o.total) })).sort((a, b) => b.value - a.value);
      document.getElementById('salesByCity').innerHTML = cityRows.slice(0, 10).map((c) => `<tr><td class="fw-600">${esc(c.label)}</td><td class="num">${c.n}</td><td class="amount num">${F.money(c.value)}</td><td style="width:160px"><div class="progress"><span style="width:${(c.value / cityRows[0].value) * 100}%"></span></div></td></tr>`).join('') || '<tr><td colspan="4" class="table-empty">No data</td></tr>';
      const catM = {}; list.filter((o) => o.status !== 'Cancelled').forEach((o) => o.items.forEach((i) => { const p = products.find((x) => x.id === i.productId); const k = p ? BW.domain.categoryName(cats, p.category) : 'Other'; catM[k] = (catM[k] || 0) + i.total; }));
      const catItems = Object.keys(catM).map((k) => ({ label: k, value: catM[k] })).sort((a, b) => b.value - a.value);
      BW.chart.donut(document.getElementById('salesByCategory'), { items: catItems, centerLabel: 'Revenue', format: (v) => F.money(v) });
      BW.chart.donut(document.getElementById('paymentChart'), { items: Object.entries(BW.util.groupBy(list, (o) => o.payment)).map(([k, v]) => ({ label: k, value: v.length })).sort((a, b) => b.value - a.value), centerLabel: 'Orders' });
      document.getElementById('rangeInfo').textContent = `${F.date(from.value)} → ${F.date(to.value)} · ${list.length} orders`;
    }
    run();
    if (location.hash === '#analytics') document.getElementById('analytics')?.scrollIntoView();
  }

  BW.ready(() => BW.boot(document.body.dataset.page === 'reports' || document.body.dataset.page === 'analytics' ? initReports : initDashboard));
})();
