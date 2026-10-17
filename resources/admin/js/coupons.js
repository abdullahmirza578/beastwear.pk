/* BeastWear.pk Admin — Coupons */
(function () {
  'use strict';
  const { esc } = BW.util; const F = BW.fmt;
  const effective = (c) => (c.status === 'Inactive' ? 'Inactive' : new Date(c.expiry) < new Date() || (c.usageLimit && c.used >= c.usageLimit) ? 'Expired' : 'Active');

  async function init() {
    let coupons = await BW.api.list('coupons'); let active = '';
    const search = document.getElementById('cpSearch');
    const tbl = BW.table({
      table: document.getElementById('couponsTable'), tbody: document.querySelector('#couponsTable tbody'), pager: document.getElementById('pager'), info: document.getElementById('pageInfo'), pageSize: 10,
      row: (c) => { const st = effective(c); const pct = c.usageLimit ? Math.min(100, (c.used / c.usageLimit) * 100) : 0; return `<tr><td><span class="mono fw-600" style="font-size:13px;letter-spacing:.06em">${esc(c.code)}</span><button class="btn-icon" style="width:26px;height:26px;margin-left:6px;vertical-align:middle" data-copy="${esc(c.code)}" title="Copy">${BW.icon('download')}</button></td><td class="fw-600">${c.type === 'percentage' ? c.amount + '%' : F.money(c.amount)}${c.maxDiscount ? `<div class="small text-muted">max ${F.money(c.maxDiscount)}</div>` : ''}</td><td>${BW.badge(c.type === 'percentage' ? 'Percentage' : 'Fixed', 'badge-plain')}</td><td>${F.money(c.minOrder)}</td><td><div class="stock-bar"><span>${c.used}${c.usageLimit ? ' / ' + c.usageLimit : ''}</span>${c.usageLimit ? `<div class="progress"><span style="width:${pct}%"></span></div>` : '<span class="small text-dim">unlimited</span>'}</div></td><td class="${new Date(c.expiry) < new Date() ? 'text-danger' : 'text-muted'}">${F.date(c.expiry)}</td><td>${BW.badge(st)}</td><td><div class="actions"><label class="switch" title="Toggle active"><input type="checkbox" data-toggle="${c.id}" ${c.status !== 'Inactive' ? 'checked' : ''}><span class="track"></span></label><button class="btn-icon" data-edit="${c.id}">${BW.icon('edit')}</button><button class="btn-icon danger" data-del="${c.id}">${BW.icon('trash')}</button></div></td></tr>`; },
      afterRender: () => {
        document.querySelectorAll('[data-edit]').forEach((b) => (b.onclick = () => form(coupons.find((c) => String(c.id) === b.dataset.edit))));
        document.querySelectorAll('[data-del]').forEach((b) => (b.onclick = () => { const c = coupons.find((x) => String(x.id) === b.dataset.del); BW.ui.confirm(`Delete coupon <b>${c.code}</b>? Used ${c.used} times.`, async () => { await BW.api.remove('coupons', c.id); BW.toast('Coupon deleted'); reload(); }); }));
        document.querySelectorAll('[data-toggle]').forEach((t) => (t.onchange = async () => { await BW.api.update('coupons', t.dataset.toggle, { status: t.checked ? 'Active' : 'Inactive' }); BW.toast(t.checked ? 'Coupon activated' : 'Coupon deactivated', 'info'); reload(); }));
        document.querySelectorAll('[data-copy]').forEach((b) => (b.onclick = () => { navigator.clipboard?.writeText(b.dataset.copy); BW.toast(`${b.dataset.copy} copied`, 'info'); }));
      }
    });
    const tabs = document.getElementById('cpTabs');
    const renderTabs = () => { const c = { '': coupons.length, Active: 0, Expired: 0, Inactive: 0 }; coupons.forEach((x) => c[effective(x)]++); tabs.innerHTML = Object.keys(c).map((k) => `<button class="tab ${active === k ? 'active' : ''}" data-s="${k}">${k || 'All'}<span class="count">${c[k]}</span></button>`).join(''); tabs.querySelectorAll('.tab').forEach((t) => (t.onclick = () => { active = t.dataset.s; renderTabs(); apply(); })); document.getElementById('sActive').textContent = c.Active; document.getElementById('sUsed').textContent = F.num(BW.util.sum(coupons, (x) => x.used)); };
    const apply = () => { const q = search.value.trim().toLowerCase(); tbl.setData(coupons.filter((c) => (!active || effective(c) === active) && (!q || c.code.toLowerCase().includes(q)))); };
    const reload = async () => { coupons = await BW.api.list('coupons'); renderTabs(); apply(); };
    search.addEventListener('input', BW.util.debounce(apply, 150));
    document.getElementById('addCoupon').addEventListener('click', () => form(null));
    renderTabs(); apply();

    function form(c) {
      const d = c || { code: '', type: 'percentage', amount: 10, minOrder: 2000, maxDiscount: '', start: BW.util.today(), expiry: BW.fmt.inputDate(Date.now() + 30 * 864e5), usageLimit: 100, status: 'Active' };
      BW.ui.modal({ title: c ? 'Edit Coupon' : 'Create Coupon', body: `<form id="cpForm"><div class="form-row"><div class="form-group"><label class="form-label">Coupon Code<span class="req">*</span></label><div class="input-group"><input class="form-control mono" name="code" required minlength="3" value="${esc(d.code)}" placeholder="BEAST10" style="text-transform:uppercase;border-radius:10px 0 0 10px"><button type="button" class="btn btn-outline" id="genCode" style="border-radius:0 10px 10px 0;border-left:0">Generate</button></div></div><div class="form-group"><label class="form-label">Discount Type</label><select class="form-control" name="type"><option value="percentage" ${d.type === 'percentage' ? 'selected' : ''}>Percentage (%)</option><option value="fixed" ${d.type === 'fixed' ? 'selected' : ''}>Fixed Amount (Rs.)</option></select></div></div><div class="form-row cols-3"><div class="form-group"><label class="form-label">Discount Amount<span class="req">*</span></label><input type="number" class="form-control" name="amount" required min="1" value="${d.amount}"></div><div class="form-group"><label class="form-label">Minimum Order (Rs.)</label><input type="number" class="form-control" name="minOrder" min="0" value="${d.minOrder}"></div><div class="form-group"><label class="form-label">Maximum Discount (Rs.)</label><input type="number" class="form-control" name="maxDiscount" min="0" value="${d.maxDiscount || ''}" placeholder="No cap"></div></div><div class="form-row cols-3"><div class="form-group"><label class="form-label">Start Date<span class="req">*</span></label><input type="date" class="form-control" name="start" required value="${String(d.start).slice(0, 10)}"></div><div class="form-group"><label class="form-label">Expiry Date<span class="req">*</span></label><input type="date" class="form-control" name="expiry" required value="${String(d.expiry).slice(0, 10)}"></div><div class="form-group"><label class="form-label">Usage Limit</label><input type="number" class="form-control" name="usageLimit" min="0" value="${d.usageLimit}" placeholder="0 = unlimited"></div></div><label class="switch"><input type="checkbox" name="active" ${d.status !== 'Inactive' ? 'checked' : ''}><span class="track"></span><span class="switch-label">Coupon is active<small>Customers can apply this code at checkout</small></span></label></form>`, footer: `<button class="btn btn-outline" data-close>Cancel</button><button class="btn btn-primary" id="cpSave">${c ? 'Update Coupon' : 'Create Coupon'}</button>`, onOpen: (m) => {
        const f = m.querySelector('#cpForm');
        m.querySelector('#genCode').onclick = () => (f.code.value = 'BW' + Math.random().toString(36).slice(2, 8).toUpperCase());
        m.querySelector('#cpSave').onclick = async () => {
          if (!BW.ui.validate(f)) return; if (f.expiry.value < f.start.value) return BW.toast('Expiry must be after the start date', 'warning');
          if (f.type.value === 'percentage' && Number(f.amount.value) > 100) return BW.toast('Percentage cannot exceed 100', 'warning');
          const data = { code: f.code.value.trim().toUpperCase(), type: f.type.value, amount: Number(f.amount.value), minOrder: Number(f.minOrder.value || 0), maxDiscount: f.maxDiscount.value ? Number(f.maxDiscount.value) : null, start: f.start.value, expiry: f.expiry.value, usageLimit: Number(f.usageLimit.value || 0), status: f.active.checked ? 'Active' : 'Inactive' };
          if (coupons.some((x) => x.code === data.code && (!c || x.id !== c.id))) return BW.toast('Coupon code already exists', 'error');
          if (c) await BW.api.update('coupons', c.id, data); else await BW.api.create('coupons', Object.assign(data, { used: 0 }));
          BW.ui.closeModal(); BW.toast(c ? 'Coupon updated' : 'Coupon created'); reload();
        };
      } });
    }
  }
  BW.ready(() => BW.boot(init));
})();
