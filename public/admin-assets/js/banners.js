/* BeastWear.pk Admin — Homepage & Banners */
(function () {
  'use strict';
  const { esc } = BW.util;
  async function init() {
    let hp = await BW.api.getObject('homepage'); const cats = await BW.api.list('categories'); const products = await BW.api.list('products');
    const bannerData = await request('/admin/banners/data');
    hp.hero = bannerData.hero || { heading: '', description: '', buttonText: '', buttonLink: '/shop', desktopImage: '', mobileImage: '', active: false };
    hp.promos = bannerData.promos || [];
    hp.featuredCategories = hp.featuredCategories || [];
    hp.featuredProducts = hp.featuredProducts || [];
    hp.newArrivals = hp.newArrivals || [];
    hp.bestSellers = hp.bestSellers || [];
    const $ = (s) => document.getElementById(s); const form = $('heroForm');
    const save = async (msg = 'Homepage saved') => {
      const settings = Object.assign({}, hp);
      delete settings.hero;
      delete settings.promos;
      await BW.api.saveObject('homepage', settings);
      BW.toast(msg);
    };
    async function request(url, method = 'GET', body = null) {
      const response = await fetch(url, {
        method,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body,
        credentials: 'same-origin'
      });
      const result = await response.json();
      if (!response.ok) {
        const validationMessage = result.errors ? Object.values(result.errors).flat()[0] : null;
        throw new Error(validationMessage || result.message || 'Could not save banner.');
      }
      return result;
    }
    const reportError = (error) => BW.toast(error.message || 'Could not save banner.', 'error');
    /* hero */
    ['heading', 'description', 'buttonText', 'buttonLink'].forEach((k) => (form[k].value = hp.hero[k] || ''));
    form.active.checked = !!hp.hero.active;
    let desktopPreview = '';
    let mobilePreview = '';
    const prev = () => {
      const desktop = desktopPreview || hp.hero.desktopImage;
      const mobile = mobilePreview || hp.hero.mobileImage || desktop;
      $('heroPrev').innerHTML = `${desktop ? `<img src="${esc(desktop)}" alt="">` : ''}<div class="hp-text"><h3>${esc(form.heading.value || 'Heading')}</h3><p>${esc(form.description.value || '')}</p><b>${esc(form.buttonText.value || 'SHOP NOW')}</b></div>`;
      $('mobPrev').src = mobile || '';
    };
    form.addEventListener('input', prev);
    $('heroDesktop').addEventListener('change', (e) => { if (e.target.files[0]) { desktopPreview = URL.createObjectURL(e.target.files[0]); prev(); } });
    $('heroMobile').addEventListener('change', (e) => { if (e.target.files[0]) { mobilePreview = URL.createObjectURL(e.target.files[0]); prev(); } });
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!BW.ui.validate(form)) return;
      const data = new FormData();
      data.append('heading', form.heading.value.trim());
      data.append('description', form.description.value.trim());
      data.append('buttonText', form.buttonText.value.trim());
      data.append('buttonLink', form.buttonLink.value.trim());
      data.append('active', form.active.checked ? '1' : '0');
      if ($('heroDesktop').files[0]) data.append('desktopImage', $('heroDesktop').files[0]);
      if ($('heroMobile').files[0]) data.append('mobileImage', $('heroMobile').files[0]);
      try {
        const result = await request('/admin/banners/hero', 'POST', data);
        hp.hero = result.hero;
        desktopPreview = '';
        mobilePreview = '';
        $('heroDesktop').value = '';
        $('heroMobile').value = '';
        prev();
        BW.toast('Hero banner updated');
      } catch (error) {
        reportError(error);
      }
    });
    prev();
    /* promos */
    const renderPromos = () => {
      $('promoList').innerHTML = hp.promos.length ? hp.promos.map((p) => `<div class="banner-row"><img src="${esc(p.image)}" alt=""><div class="grow" style="flex:1;min-width:0"><strong>${esc(p.title)}</strong><div class="small text-muted">${esc(p.subtitle || '')} · ${esc(p.position)} · <span class="mono">${esc(p.link)}</span></div></div>${BW.badge(p.active ? 'Active' : 'Inactive')}<div class="actions"><button class="btn-icon" data-pe="${p.id}">${BW.icon('edit')}</button><button class="btn-icon danger" data-pd="${p.id}">${BW.icon('trash')}</button></div></div>`).join('') : '<div class="empty-state">No promotional banners</div>';
      $('promoList').querySelectorAll('[data-pe]').forEach((b) => (b.onclick = () => promoForm(hp.promos.find((p) => String(p.id) === b.dataset.pe))));
      $('promoList').querySelectorAll('[data-pd]').forEach((b) => (b.onclick = () => BW.ui.confirm('Delete this promotional banner?', async () => {
        try {
          await request(`/admin/banners/promos/${encodeURIComponent(b.dataset.pd)}`, 'DELETE');
          hp.promos = hp.promos.filter((p) => String(p.id) !== b.dataset.pd);
          BW.toast('Banner deleted');
          renderPromos();
        } catch (error) {
          reportError(error);
        }
      })));
    };
    const promoForm = (p) => {
      const d = p || { title: '', subtitle: '', image: '', link: '/shop', position: 'Below Hero', active: true };
      let previewImage = d.image;
      BW.ui.modal({
        title: p ? 'Edit Promotional Banner' : 'Add Promotional Banner',
        body: `<form id="pf"><div class="form-group"><label class="form-label">Title<span class="req">*</span></label><input class="form-control" name="title" required value="${esc(d.title)}"></div><div class="form-row"><div class="form-group"><label class="form-label">Subtitle</label><input class="form-control" name="subtitle" value="${esc(d.subtitle)}"></div><div class="form-group"><label class="form-label">Position</label><select class="form-control" name="position">${['Below Hero', 'Mid Page', 'Above Footer', 'Footer'].map((x) => `<option ${x === d.position ? 'selected' : ''}>${x}</option>`).join('')}</select></div></div><div class="form-group"><label class="form-label">Link<span class="req">*</span></label><input class="form-control" name="link" required value="${esc(d.link)}"></div><div class="form-group"><label class="form-label">Banner Image</label><img class="img-preview-wide mb-16" id="pfPrev" src="${esc(previewImage || 'https://picsum.photos/seed/newpromo/900/400')}" alt=""><div class="upload-box"><input type="file" accept="image/*" id="pfImg">${BW.icon('upload')}<strong>Upload banner image</strong><small>Recommended 1200×500px</small></div></div><label class="switch"><input type="checkbox" name="active" ${d.active ? 'checked' : ''}><span class="track"></span><span class="switch-label">Show on homepage</span></label></form>`,
        footer: `<button class="btn btn-outline" data-close>Cancel</button><button class="btn btn-primary" id="pfSave">Save Banner</button>`,
        onOpen: (m) => {
          m.querySelector('#pfImg').onchange = (e) => {
            if (e.target.files[0]) {
              previewImage = URL.createObjectURL(e.target.files[0]);
              m.querySelector('#pfPrev').src = previewImage;
            }
          };
          m.querySelector('#pfSave').onclick = async () => {
            const f = m.querySelector('#pf');
            if (!BW.ui.validate(f)) return;
            const data = new FormData();
            data.append('title', f.title.value.trim());
            data.append('subtitle', f.subtitle.value.trim());
            data.append('position', f.position.value);
            data.append('link', f.link.value.trim());
            data.append('active', f.active.checked ? '1' : '0');
            if (m.querySelector('#pfImg').files[0]) data.append('image', m.querySelector('#pfImg').files[0]);
            try {
              const result = p
                ? await request(`/admin/banners/promos/${encodeURIComponent(p.id)}`, 'POST', methodOverride(data, 'PUT'))
                : await request('/admin/banners/promos', 'POST', data);
              if (p) Object.assign(p, result.promo);
              else hp.promos.push(result.promo);
              BW.ui.closeModal();
              BW.toast(p ? 'Banner updated' : 'Banner added');
              renderPromos();
            } catch (error) {
              reportError(error);
            }
          };
        }
      });
    };
    function methodOverride(data, method) {
      data.append('_method', method);
      return data;
    }
    $('addPromo').addEventListener('click', () => promoForm(null)); renderPromos();
    /* featured categories */
    $('catSelect').innerHTML = cats.map((c) => `<label class="select-card"><input type="checkbox" value="${c.id}" ${hp.featuredCategories.includes(c.id) ? 'checked' : ''}><img src="${esc(c.image)}" alt=""><strong>${esc(c.name)}</strong><span>${products.filter((p) => p.category === c.slug).length} products</span></label>`).join('');
    $('saveCats').addEventListener('click', () => { hp.featuredCategories = [...$('catSelect').querySelectorAll('input:checked')].map((i) => Number(i.value)); save('Featured categories saved'); });
    /* product sections */
    const sections = [['featuredProducts', 'featuredSel', 'saveFeatured', 'featuredCount', 8], ['newArrivals', 'newSel', 'saveNew', 'newCount', 8], ['bestSellers', 'bestSel', 'saveBest', 'bestCount', 8]];
    sections.forEach(([key, selId, btnId, cntId, max]) => {
      const el = $(selId); const search = el.previousElementSibling.querySelector('input');
      const render = () => { const q = (search.value || '').toLowerCase(); el.innerHTML = products.filter((p) => !q || (p.name + p.sku).toLowerCase().includes(q)).map((p) => `<label class="select-card row"><input type="checkbox" value="${p.id}" ${hp[key].includes(p.id) ? 'checked' : ''}><img src="${esc(p.image)}" alt=""><div style="min-width:0"><strong>${esc(p.name)}</strong><span>${p.sku} · ${BW.fmt.money(p.salePrice || p.price)}</span></div></label>`).join(''); el.querySelectorAll('input').forEach((i) => (i.onchange = () => { const id = Number(i.value); if (i.checked) { if (hp[key].length >= max) { i.checked = false; return BW.toast(`Maximum ${max} products in this section`, 'warning'); } hp[key].push(id); } else hp[key] = hp[key].filter((x) => x !== id); $(cntId).textContent = hp[key].length; })); $(cntId).textContent = hp[key].length; };
      search.addEventListener('input', BW.util.debounce(render, 120)); $(btnId).addEventListener('click', () => save('Section saved')); render();
    });
  }
  BW.ready(() => BW.boot(init));
})();
