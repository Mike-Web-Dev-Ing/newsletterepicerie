// script.js
function getBasePath(){
  const body = document.body;
  if (!body) {
    return '';
  }
  const raw = body.getAttribute('data-base-path') || '';
  if (!raw || raw === '/') {
    return '';
  }
  return raw;
}

function appUrl(path){
  const normalized = '/' + String(path || '').replace(/^\/+/, '');
  const base = getBasePath();
  return base ? base + normalized : normalized;
}

document.addEventListener('DOMContentLoaded', function(){
  // mobile nav
  const navToggle = document.getElementById('nav-toggle');
  const navList = document.getElementById('nav-list');
  navToggle && navToggle.addEventListener('click', () => {
    const open = navList.style.display === 'block';
    navList.style.display = open ? '' : 'block';
    navToggle.setAttribute('aria-expanded', String(!open));
    if (!open) navList.querySelector('a').focus();
  });

  // Product modal
  const modal = document.getElementById('product-modal');
  const modalContent = document.getElementById('modal-content');
  const modalClose = document.getElementById('modal-close');

  let lastFocusedElement;

  function openProductModal(id){
    lastFocusedElement = document.activeElement;
    const prod = (window.__products || []).find(p => p.id == id);
    if(!prod) return;
    modalContent.innerHTML = `
      <div style="display:flex;gap:1rem;flex-wrap:wrap">
        <div style="flex:1 1 240px"><img src="${prod.image}" alt="${prod.name}" style="width:100%;height:auto;border-radius:8px"></div>
        <div style="flex:1 1 240px">
          <h2 id="modal-title" tabindex="-1">${prod.name}</h2>
          <p>${prod.description}</p>
          <p><strong>${prod.price.toFixed(2).replace('.', ',')}€</strong> ${prod.old_price > 0 ? '<span style="text-decoration:line-through;margin-left:.5rem">' + prod.old_price.toFixed(2).replace('.', ',') + '€</span>' : ''}</p>
          <a href="#newsletter" class="btn btn-primary">Réserver / S'inscrire</a>
        </div>
      </div>
    `;
    modal.setAttribute('aria-hidden','false');
    modal.style.display = 'flex'; // Make sure it's visible for focus
    document.body.style.overflow = 'hidden';
    modalClose.focus();

    // Trap focus
    modal.addEventListener('keydown', trapFocus);
  }

  function closeModal() {
    modal.setAttribute('aria-hidden','true');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    modal.removeEventListener('keydown', trapFocus);
    if (lastFocusedElement) {
      lastFocusedElement.focus();
    }
  }

  function trapFocus(e) {
    const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (e.key === 'Tab') {
      if (e.shiftKey && document.activeElement === firstElement) {
        e.preventDefault();
        lastElement.focus();
      } else if (!e.shiftKey && document.activeElement === lastElement) {
        e.preventDefault();
        firstElement.focus();
      }
    }
  }

  modalClose && modalClose.addEventListener('click', () => {
    closeModal();
  });
  modal.addEventListener('click', (e) => {
    if(e.target === modal) closeModal();
  });

  // Newsletter AJAX
  const form = document.getElementById('newsletter-form');
  const msg = document.getElementById('news-msg');
  if(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      msg.textContent = 'Envoi...';
      const data = new FormData(form);
      fetch(form.action, { method:'POST', body: data })
        .then(r => r.json())
        .then(json => {
          if(json.success){
            const mc = json.mailchimp || {};
            if(mc.status === 'ok' && mc.mode === 'pending'){
              msg.textContent = "Merci ! Vérifiez votre email pour confirmer votre inscription.";
            } else if (mc.status === 'ok') {
              msg.textContent = "Merci ! Vous êtes inscrit.";
            } else if (mc.status === 'error') {
              msg.textContent = "Inscription enregistrée. Info: service email indisponible, réessayez plus tard.";
            } else {
              msg.textContent = 'Merci ! Vous êtes inscrit.';
            }
            form.reset();
          } else {
            msg.textContent = json.error || 'Une erreur est survenue.';
          }
        })
        .catch(() => { msg.textContent = 'Erreur réseau.' });
    });
  }
});
(function(){
  const promos = window.__promos || [];
  const list = document.getElementById('promos-list');
  // Si la section promotions n'existe pas dans le DOM, on sort proprement
  if(!list){
    return;
  }

  // helper : format prix FR
  function fmtPrice(n){
    return Number(n).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + '€';
  }

  // build cards
  if(promos.length === 0){
    list.innerHTML = '<p class="text-muted">Aucune promotion pour le moment.</p>';
  } else {
    const fragment = document.createDocumentFragment();
    promos.forEach(p => {
      const col = document.createElement('div');
      col.className = 'col-md-4 mb-4';
      col.innerHTML = `
        <article class="card h-100 shadow-sm promo-card" data-id="${p.id}">
          ${p.product_image ? `<img src="${p.product_image}" class="card-img-top" alt="${escapeHtml(p.title)}" style="height:200px;object-fit:cover;">` : ''}
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">${escapeHtml(p.title)}</h5>
            <p class="card-text text-muted small">${escapeHtml(p.description).replace(/\\n/g,'<br>')}</p>
            <div class="mt-auto">
              <p class="mb-1 fw-bold">${fmtPrice(p.price)} ${p.old_price ? `<span class="text-decoration-line-through text-muted ms-2">${fmtPrice(p.old_price)}</span>` : ''}</p>
            </div>
          </div>
        </article>
      `;
      fragment.appendChild(col);
    });
    list.appendChild(fragment);
  }

  // simple event delegation for opening modal
  list.addEventListener('click', function(e){
    let el = e.target;
    while(el && !el.classList.contains('promo-card')) el = el.parentElement;
    if(!el) return;
    const id = el.getAttribute('data-id');
    const promo = promos.find(x => String(x.id) === String(id));
    if(promo) openModal(promo);
  });

  // modal functions
  const modal = document.getElementById('promo-modal');
  const modalBody = document.getElementById('modal-body');
  const modalClose = document.getElementById('modal-close');
  if(!modal || !modalBody || !modalClose){
    return; // pas de modal dédiée aux promos
  }

  function openModal(p){
    modalBody.innerHTML = `
      <div class="row g-3">
        <div class="col-md-5">${p.product_image ? `<img src="${p.product_image}" class="modal-img" alt="${escapeHtml(p.title)}">` : ''}</div>
        <div class="col-md-7">
          <h2>${escapeHtml(p.title)}</h2>
          <p class="text-muted small">${escapeHtml(p.description).replace(/\\n/g,'<br>')}</p>
          <p class="fw-bold">${fmtPrice(p.price)} ${p.old_price ? `<span class="text-decoration-line-through text-muted ms-2">${fmtPrice(p.old_price)}</span>` : ''}</p>
          ${p.start_date || p.end_date ? `<p class="small text-muted">Valable : ${escapeHtml(p.start_date || '-') } → ${escapeHtml(p.end_date || '-')}</p>` : ''}
          <p class="mt-3"><a class="btn btn-primary" href="${appUrl('admin/login')}">Réserver / Se connecter</a></p>
        </div>
      </div>
    `;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function closeModal(){
    modal.style.display = 'none';
    modalBody.innerHTML = '';
    document.body.style.overflow = '';
  }

  modalClose.addEventListener('click', closeModal);
  modal.addEventListener('click', function(e){
    if(e.target === modal) closeModal();
  });

  // small utility to avoid XSS when injecting text
  function escapeHtml(str){
    if(!str && str !== 0) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  // dev: log promos in console
  console.log('Promotions (window.__promos):', promos);
})();
