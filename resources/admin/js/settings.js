/* BeastWear.pk Admin — Settings, Shipping, Admin Users, Profile */
(function () {
  'use strict';
  const { esc } = BW.util; const F = BW.fmt;
  const fill = (form, obj) => { Object.keys(obj).forEach((k) => { const el = form.elements[k]; if (!el) return; if (el.type === 'checkbox') el.checked = !!obj[k]; else el.value = obj[k] ?? ''; }); };
  const collect = (form) => { const o = {}; [...form.elements].forEach((el) => { if (!el.name) return; o[el.name] = el.type === 'checkbox' ? el.checked : el.type === 'number' ? Number(el.value) : el.value.trim(); }); return o; };

  /* ================= GENERAL SETTINGS ================= */
  async function initSettings() {
    const form = document.getElementById('settingsForm');
    const request = async (method, body = null) => {
      const response = await fetch(method === 'GET' ? '/admin/settings/data' : '/admin/settings', {
        method,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'same-origin',
        body
      });
      const result = await response.json();
      if (!response.ok) {
        const validationMessage = result.errors ? Object.values(result.errors).flat()[0] : null;
        throw new Error(validationMessage || result.message || 'Could not save settings.');
      }
      return result;
    };
    const s = await request('GET');
    fill(form, s);
    const logoPrev = document.getElementById('logoPrev'), favPrev = document.getElementById('favPrev'); let logo = s.logo || '', favicon = s.favicon || '';
    const prev = () => { logoPrev.innerHTML = logo ? `<img src="${logo}" alt="" style="height:48px;border-radius:8px">` : '<div class="brand-mark" style="width:48px;height:48px;font-size:18px">BW</div>'; favPrev.innerHTML = favicon ? `<img src="${favicon}" alt="" style="width:32px;height:32px;border-radius:6px">` : '<div class="brand-mark" style="width:32px;height:32px;font-size:12px;border-radius:6px">BW</div>'; };
    document.getElementById('logoFile').onchange = (e) => { if (e.target.files[0]) { logo = URL.createObjectURL(e.target.files[0]); prev(); } };
    document.getElementById('favFile').onchange = (e) => { if (e.target.files[0]) { favicon = URL.createObjectURL(e.target.files[0]); prev(); } };
    prev();
    document.querySelectorAll('.settings-nav button').forEach((b) => b.addEventListener('click', () => { document.querySelectorAll('.settings-nav button').forEach((x) => x.classList.remove('active')); b.classList.add('active'); document.querySelectorAll('[data-section]').forEach((sec) => sec.classList.toggle('hidden', sec.dataset.section !== b.dataset.target)); window.scrollTo({ top: 0, behavior: 'smooth' }); }));
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!BW.ui.validate(form)) return BW.toast('Please correct the highlighted fields', 'error');
      const data = new FormData();
      [...form.elements].forEach((el) => {
        if (!el.name || el.type === 'file') return;
        data.append(el.name, el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value.trim());
      });
      if (document.getElementById('logoFile').files[0]) data.append('logo', document.getElementById('logoFile').files[0]);
      if (document.getElementById('favFile').files[0]) data.append('favicon', document.getElementById('favFile').files[0]);
      try {
        const saved = await request('POST', data);
        fill(form, saved);
        logo = saved.logo || '';
        favicon = saved.favicon || '';
        document.getElementById('logoFile').value = '';
        document.getElementById('favFile').value = '';
        prev();
        BW.toast('Settings saved successfully');
        if (saved.maintenance) BW.toast('Maintenance mode is ON — storefront is hidden from customers', 'warning');
      } catch (error) {
        BW.toast(error.message || 'Could not save settings.', 'error');
      }
    });
  }

  /* ================= SHIPPING ================= */
  async function initShipping() {
    const form = document.getElementById('shippingForm');
    const request = async (method, payload) => {
      const response = await fetch('/admin/settings/shipping' + (method === 'GET' ? '/data' : ''), {
        method,
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'same-origin',
        body: payload ? JSON.stringify(payload) : undefined
      });
      const result = await response.json();
      if (!response.ok) {
        const validationMessage = result.errors ? Object.values(result.errors).flat()[0] : null;
        throw new Error(validationMessage || result.message || 'Could not save shipping settings.');
      }
      return result;
    };
    const s = await request('GET');
    fill(form, s);
    const calc = () => { const d = collect(form); const sub = Number(document.getElementById('calcAmount').value) || 0; const city = document.getElementById('calcCity').value; const fee = sub >= d.freeThreshold ? 0 : city === 'Karachi' ? d.karachiCharge : city === 'Remote' ? d.remoteAreaCharge : d.otherCityCharge; document.getElementById('calcResult').innerHTML = `Delivery charge: <b>${F.money(fee)}</b> ${fee === 0 ? BW.badge('Free Delivery') : ''}<div class="small text-muted mt-8">Estimated ${city === 'Karachi' ? d.karachiDays : d.otherDays} working days${d.codEnabled ? ' · COD available' + (d.codFee ? ` (+${F.money(d.codFee)})` : '') : ' · Prepaid only'}</div>`; };
    form.addEventListener('input', calc); document.getElementById('calcAmount').addEventListener('input', calc); document.getElementById('calcCity').addEventListener('change', calc); calc();
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!BW.ui.validate(form)) return;
      try {
        fill(form, await request('PUT', collect(form)));
        calc();
        BW.toast('Shipping settings saved');
      } catch (error) {
        BW.toast(error.message || 'Could not save shipping settings.', 'error');
      }
    });
  }

  /* ================= ADMIN USERS ================= */
  async function initAdmins() {
    let admins = await BW.api.list('admins'); const me = BW.auth.session();
    const tbl = BW.table({
      table: document.getElementById('adminsTable'), tbody: document.querySelector('#adminsTable tbody'), pager: document.getElementById('pager'), info: document.getElementById('pageInfo'),
      row: (a) => `<tr><td><div class="flex">${BW.avatar(a.name, '', a.avatar)}<div><div class="fw-600">${esc(a.name)} ${a.id === me.id ? '<span class="badge badge-plain" style="height:18px;font-size:10px">YOU</span>' : ''}</div><div class="small text-muted">${esc(a.phone || '')}</div></div></div></td><td class="text-muted">${esc(a.email)}</td><td>${BW.badge(a.role, a.role === 'Super Admin' ? '' : 'badge-plain')}</td><td>${BW.badge(a.status)}</td><td class="text-muted">${a.lastLogin ? F.dateTime(a.lastLogin) : 'Never'}</td><td><div class="actions"><button class="btn-icon" data-edit="${a.id}">${BW.icon('edit')}</button><button class="btn-icon danger" data-del="${a.id}" ${a.id === me.id || a.role === 'Super Admin' ? 'disabled style="opacity:.3"' : ''}>${BW.icon('trash')}</button></div></td></tr>`,
      afterRender: () => { document.querySelectorAll('[data-edit]').forEach((b) => (b.onclick = () => form(admins.find((a) => String(a.id) === b.dataset.edit)))); document.querySelectorAll('[data-del]:not([disabled])').forEach((b) => (b.onclick = () => { const a = admins.find((x) => String(x.id) === b.dataset.del); BW.ui.confirm(`Remove admin <b>${esc(a.name)}</b>? They will lose access immediately.`, async () => { await BW.api.remove('admins', a.id); BW.toast('Admin removed'); reload(); }); })); }
    });
    const reload = async () => { admins = await BW.api.list('admins'); tbl.setData(admins); };
    document.getElementById('addAdmin').onclick = () => form(null);
    if (me.role !== 'Super Admin') document.getElementById('addAdmin').classList.add('hidden');
    function form(a) {
      const d = a || { name: '', email: '', role: 'Admin', status: 'Active', phone: '' };
      BW.ui.modal({ title: a ? 'Edit Admin User' : 'Create Admin User', body: `<form id="aForm"><div class="form-row"><div class="form-group"><label class="form-label">Full Name<span class="req">*</span></label><input class="form-control" name="name" required value="${esc(d.name)}"></div><div class="form-group"><label class="form-label">Phone</label><input class="form-control" name="phone" data-validate="phone" value="${esc(d.phone || '')}"></div></div><div class="form-group"><label class="form-label">Email<span class="req">*</span></label><input class="form-control" type="email" name="email" required value="${esc(d.email)}"></div><div class="form-row"><div class="form-group"><label class="form-label">Password${a ? '' : '<span class="req">*</span>'}</label><input class="form-control" type="password" name="password" ${a ? '' : 'required'} minlength="6" placeholder="${a ? 'Leave blank to keep current' : 'Min 6 characters'}"></div><div class="form-group"><label class="form-label">Confirm Password</label><input class="form-control" type="password" name="password2" data-match="[name=password]"></div></div><div class="form-row"><div class="form-group"><label class="form-label">Role</label><select class="form-control" name="role">${['Super Admin', 'Admin', 'Manager'].map((r) => `<option ${r === d.role ? 'selected' : ''}>${r}</option>`).join('')}</select><div class="form-hint">Super Admin: full access · Admin: catalog & orders · Manager: orders & customers only</div></div><div class="form-group"><label class="form-label">Status</label><select class="form-control" name="status"><option ${d.status === 'Active' ? 'selected' : ''}>Active</option><option ${d.status === 'Inactive' ? 'selected' : ''}>Inactive</option></select></div></div></form>`, footer: `<button class="btn btn-outline" data-close>Cancel</button><button class="btn btn-primary" id="aSave">${a ? 'Update User' : 'Create User'}</button>`, onOpen: (m) => { m.querySelector('#aSave').onclick = async () => { const f = m.querySelector('#aForm'); if (!BW.ui.validate(f)) return; const data = { name: f.elements.name.value.trim(), email: f.email.value.trim().toLowerCase(), phone: f.phone.value.trim(), role: f.role.value, status: f.status.value }; if (f.password.value) data.password = f.password.value; if (admins.some((x) => x.email === data.email && (!a || x.id !== a.id))) return BW.toast('Email already in use', 'error'); if (a) await BW.api.update('admins', a.id, data); else await BW.api.create('admins', Object.assign(data, { lastLogin: null, avatar: '' })); BW.ui.closeModal(); BW.toast(a ? 'Admin updated' : 'Admin user created'); reload(); }; } });
    }
  }

  /* ================= PROFILE ================= */
  async function initProfile() {
    const me = BW.auth.session(); let a = await BW.api.get('admins', me.id); if (!a) return BW.auth.logout();
    const pf = document.getElementById('profileForm'), pw = document.getElementById('passwordForm'); let avatar = a.avatar || '';
    fill(pf, a);
    const prev = () => { document.getElementById('avatarPrev').innerHTML = BW.avatar(pf.elements.name.value || a.name, 'xl', avatar); document.getElementById('profileHead').innerHTML = `<h2 style="font-size:20px">${esc(a.name)}</h2><div class="flex gap-6 mt-8" style="justify-content:center">${BW.badge(a.role)}${BW.badge(a.status)}</div><p class="small text-muted mt-8">Last login ${F.dateTime(a.lastLogin)}</p>`; };
    document.getElementById('avatarFile').onchange = async (e) => { if (e.target.files[0]) { avatar = await BW.ui.readFile(e.target.files[0]); prev(); } };
    document.getElementById('removeAvatar').onclick = () => { avatar = ''; prev(); };
    prev();
    pf.addEventListener('submit', async (e) => { e.preventDefault(); if (!BW.ui.validate(pf)) return; const admins = await BW.api.list('admins'); if (admins.some((x) => x.email === pf.email.value.trim().toLowerCase() && x.id !== a.id)) return BW.toast('Email already in use by another admin', 'error'); a = await BW.api.update('admins', a.id, { name: pf.elements.name.value.trim(), email: pf.email.value.trim().toLowerCase(), phone: pf.phone.value.trim(), avatar }); BW.auth.updateSession({ name: a.name, email: a.email, avatar: a.avatar }); BW.toast('Profile updated'); setTimeout(() => location.reload(), 700); });
    pw.addEventListener('submit', async (e) => { e.preventDefault(); if (!BW.ui.validate(pw)) return; if (pw.current.value !== a.password) return BW.toast('Current password is incorrect', 'error'); if (pw.newPass.value === a.password) return BW.toast('New password must be different', 'warning'); a = await BW.api.update('admins', a.id, { password: pw.newPass.value }); pw.reset(); BW.toast('Password changed successfully'); });
    const strength = document.getElementById('pwStrength'); pw.newPass.addEventListener('input', () => { const v = pw.newPass.value; let s = 0; if (v.length >= 6) s++; if (v.length >= 10) s++; if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++; if (/\d/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++; strength.innerHTML = `<div class="progress"><span style="width:${(s / 5) * 100}%;background:${s < 2 ? 'var(--danger)' : s < 4 ? 'var(--warning)' : 'var(--success)'}"></span></div><div class="small text-muted mt-8">${['Too weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very strong'][s]}</div>`; });
    const orders = await BW.api.list('orders'); document.getElementById('activity').innerHTML = orders.filter((o) => o.updatedAt).slice(0, 6).map((o) => `<div class="timeline-item done"><strong>Updated ${o.id} → ${o.status}</strong><span>${F.dateTime(o.updatedAt)}</span></div>`).join('') || '<div class="timeline-item current"><strong>Logged in</strong><span>' + F.dateTime(me.loginAt) + '</span></div>';
  }

  BW.ready(() => { const p = document.body.dataset.page; BW.boot(p === 'shipping' ? initShipping : p === 'admins' ? initAdmins : p === 'profile' ? initProfile : initSettings); });
})();
