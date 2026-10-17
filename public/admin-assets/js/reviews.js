/* BeastWear.pk Admin — Reviews moderation */
(function () {
  'use strict';
  const { esc } = BW.util;
  const F = BW.fmt;

  async function api(url, method, body) {
    const response = await fetch(url, {
      method,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: body ? JSON.stringify(body) : undefined
    });
    const result = response.status === 204 ? null : await response.json();
    if (!response.ok) throw new Error(result && result.message ? result.message : 'Unable to update review.');
    return result;
  }

  async function getReviews() {
    const response = await fetch('/admin/reviews/data', { headers: { Accept: 'application/json' } });
    const reviews = await response.json();
    if (!response.ok) throw new Error(reviews.message || 'Unable to load reviews.');
    return reviews;
  }

  async function init() {
    let reviews;
    let active = '';
    let rating = '';
    const search = document.getElementById('rSearch');
    const list = document.getElementById('reviewList');
    const tabs = document.getElementById('rTabs');

    try {
      reviews = await getReviews();
    } catch (error) {
      BW.toast(error.message, 'error');
      return;
    }

    const renderTabs = () => {
      const counts = { '': reviews.length, Pending: 0, Approved: 0, Rejected: 0 };
      reviews.forEach((review) => counts[review.status]++);
      tabs.innerHTML = Object.keys(counts).map((status) =>
        `<button class="tab ${active === status ? 'active' : ''}" data-s="${status}">${status || 'All'}<span class="count">${counts[status]}</span></button>`
      ).join('');
      tabs.querySelectorAll('.tab').forEach((tab) => {
        tab.onclick = () => {
          active = tab.dataset.s;
          renderTabs();
          render();
        };
      });
      const approved = reviews.filter((review) => review.status === 'Approved');
      const average = approved.length
        ? BW.util.sum(approved, (review) => review.rating) / approved.length
        : 0;
      document.getElementById('sAvg').innerHTML = average.toFixed(1) + ' <span class="small text-muted">/ 5</span>';
      document.getElementById('sPending').textContent = counts.Pending;
      document.getElementById('sTotal').textContent = reviews.length;
      BW.chart.hbars(document.getElementById('ratingDist'), {
        items: [5, 4, 3, 2, 1].map((value) => ({
          label: '★'.repeat(value),
          value: approved.filter((review) => review.rating === value).length
        }))
      });
    };

    const reload = async () => {
      try {
        reviews = await getReviews();
        renderTabs();
        render();
      } catch (error) {
        BW.toast(error.message, 'error');
      }
    };

    const render = () => {
      const query = search.value.trim().toLowerCase();
      const rows = reviews
        .filter((review) =>
          (!active || review.status === active)
          && (!rating || review.rating === Number(rating))
          && (!query || (review.customer + review.product + review.text).toLowerCase().includes(query))
        )
        .sort((a, b) => new Date(b.date) - new Date(a.date));

      list.innerHTML = rows.length ? rows.map((review) =>
        `<div class="review-card">
          <div class="flex-between flex-wrap">
            <div class="flex">${BW.avatar(review.customer)}<div>
              <strong>${esc(review.customer)}</strong>
              <div class="small text-muted">on <a href="/admin/products/edit?id=${encodeURIComponent(review.productId)}" class="fw-600" style="color:var(--text)">${esc(review.product)}</a> · ${F.date(review.date)}</div>
            </div></div>
            <div class="flex">${BW.stars(review.rating)}${BW.badge(review.status)}</div>
          </div>
          <p class="review-text">“${esc(review.text || 'No written comment.') }”</p>
          <div class="actions">
            ${review.status !== 'Approved' ? `<button class="btn btn-sm btn-success" data-act="Approved" data-id="${review.id}">${BW.icon('check')} Approve</button>` : ''}
            ${review.status !== 'Rejected' ? `<button class="btn btn-sm btn-outline" data-act="Rejected" data-id="${review.id}">${BW.icon('ban')} Reject</button>` : ''}
            <button class="btn btn-sm btn-danger" style="margin-left:auto" data-del="${review.id}">${BW.icon('trash')} Delete</button>
          </div>
        </div>`
      ).join('') : '<div class="empty-state">' + BW.icon('star') + 'No reviews match the current filter</div>';

      list.querySelectorAll('[data-act]').forEach((button) => {
        button.onclick = async () => {
          button.disabled = true;
          try {
            await api(`/admin/reviews/${button.dataset.id}`, 'PATCH', { status: button.dataset.act });
            BW.toast(`Review ${button.dataset.act.toLowerCase()}`, button.dataset.act === 'Approved' ? 'success' : 'warning');
            await reload();
          } catch (error) {
            button.disabled = false;
            BW.toast(error.message, 'error');
          }
        };
      });
      list.querySelectorAll('[data-del]').forEach((button) => {
        button.onclick = () => BW.ui.confirm('Permanently delete this review?', async () => {
          try {
            await api(`/admin/reviews/${button.dataset.del}`, 'DELETE');
            BW.toast('Review deleted');
            await reload();
          } catch (error) {
            BW.toast(error.message, 'error');
          }
        });
      });
    };

    search.addEventListener('input', BW.util.debounce(render, 150));
    document.getElementById('rRating').addEventListener('change', (event) => {
      rating = event.target.value;
      render();
    });
    document.getElementById('approveAll').addEventListener('click', () => {
      const pending = reviews.filter((review) => review.status === 'Pending');
      if (!pending.length) return BW.toast('No pending reviews', 'info');
      BW.ui.confirm(`Approve all ${pending.length} pending reviews?`, async () => {
        try {
          for (const review of pending) {
            await api(`/admin/reviews/${review.id}`, 'PATCH', { status: 'Approved' });
          }
          BW.toast(`${pending.length} reviews approved`);
          await reload();
        } catch (error) {
          BW.toast(error.message, 'error');
          await reload();
        }
      }, { yes: 'Approve All', danger: false });
    });

    renderTabs();
    render();
  }

  BW.ready(() => BW.boot(init));
})();
