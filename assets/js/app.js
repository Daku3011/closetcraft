// Variables, arrays, functions, conditions, loops
const TYPES = ['top', 'bottom', 'dress', 'shoes', 'outerwear', 'accessory'];
const OCCASIONS = ['casual', 'formal', 'party', 'sports', 'ethnic'];
let closetCache = []; // array

// Inbuilt functions + DOM
document.addEventListener('DOMContentLoaded', function () {
  loadClosetCache();          // JSON + callbacks
  attachValidation();         // validations + regex
  attachPreview();            // File API
  const genBtn = document.getElementById('ajaxGenerate');
  if (genBtn) genBtn.addEventListener('click', onGenerateClick); // Event handling
  const lastVisit = localStorage.getItem('cc_last_visit');
  if (lastVisit) console.log('Last visit: ' + lastVisit);
  localStorage.setItem('cc_last_visit', new Date().toISOString());
});

function fetchJSON(url, onSuccess, onError) {
  url += (url.includes('?') ? '&' : '?') + '_=' + Date.now(); // no cached shuffles
  fetch(url, { cache: 'no-store' }).then(r => r.json()).then(onSuccess).catch(onError || console.error);
}
function loadClosetCache() {
  fetchJSON('api.php?action=list',
    function (data) { closetCache = data.items || []; renderCount(); },  // callback
    function () { console.warn('API offline'); });
}
function renderCount() {
  const el = document.getElementById('closetCount');
  if (!el) return;
  let casual = 0; // loops + conditions
  for (let i = 0; i < closetCache.length; i++) {
    if (closetCache[i].occasion === 'casual') casual++;
  }
  el.textContent = `You own ${closetCache.length} items (${casual} casual).`;
}

function onGenerateClick() {
  const occ = document.getElementById('genOccasion')?.value || 'casual';
  const style = document.getElementById('genStyle')?.value || 'unisex';
  if (!confirm('Generate a random "' + occ + '" outfit (' + style + ')?')) return; // popup
  fetchJSON('api.php?action=random&occasion=' + encodeURIComponent(occ) + '&style=' + encodeURIComponent(style),
    function (res) {
      alert(res.message || 'Outfit ready!'); // popup
      showOutfit(res.outfit, res.items);
      prompt('Like a name for this outfit? Type it, then Save below.', res.outfit?.name || '');
    });
}
function showOutfit(outfit, items) {
  const box = document.getElementById('ajaxResult');
  if (!box) return;
  // Full fit view: photo cards for each piece
  let html = '<h4>' + escapeHtml(outfit.name) + ' <small>(' + escapeHtml(outfit.occasion) + ')</small></h4>' +
    '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-top:8px">';
  items.forEach(function (i) {
    // Offline-safe SVG placeholder (same palette as PHP helper) — works in both themes
    let img = i.image_path;
    if (!img) {
      const pairs = { top: ['#f3c6d3','#df9dbe'], bottom: ['#bcd3f5','#8fb0ea'], dress: ['#d9c2f0','#b394e0'], shoes: ['#f6d9b8','#ecb877'], outerwear: ['#bfe3d8','#87c9b6'], accessory: ['#f5e6a8','#e9cc72'] };
      const p = pairs[i.type] || ['#ddd6f0','#b8aede'];
      const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='400' height='300'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='${p[0]}'/><stop offset='1' stop-color='${p[1]}'/></linearGradient></defs><rect width='400' height='300' fill='url(#g)'/><text x='200' y='168' font-size='100' text-anchor='middle' fill='rgba(255,255,255,.92)' font-family='Arial' font-weight='bold'>${(i.type||'I')[0].toUpperCase()}</text><text x='200' y='218' font-size='26' text-anchor='middle' fill='rgba(255,255,255,.9)' font-family='Arial'>${i.type}</text></svg>`;
      img = 'data:image/svg+xml,' + encodeURIComponent(svg);
    }
    html += `<div class="item-card" style="display:block;margin:0">
      <img src="${escapeHtml(img)}" alt="${escapeHtml(i.name)}" style="width:100%;border-radius:14px">
      <br><b>${escapeHtml(i.type)}</b><br>${escapeHtml(i.name)} <small>(${escapeHtml(i.color)})</small></div>`;
  });
  html += '</div>';
  if (!items.length) html += '<p>No items found for this occasion — add some in My Closet first.</p>';
  box.innerHTML = html;
  Object.keys(outfit).forEach(k => {
    if (!k.endsWith('_id')) return;
    const hid = document.getElementById('hid_' + k);
    if (hid) hid.value = outfit[k] ?? '';
  });
  const nm = document.getElementById('outfitName');
  if (nm) nm.value = outfit.name;
  const st = document.getElementById('hid_style');
  if (st && outfit.style) st.value = outfit.style;
}
function escapeHtml(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

function attachValidation() {
  const form = document.getElementById('uploadForm');
  if (!form) return;
  form.addEventListener('submit', function (ev) {
    const name = form.name.value.trim();
    const color = form.color.value.trim();
    const nameRe = /^[A-Za-z0-9 ]{3,40}$/;          // regex
    const colorRe = /^[A-Za-z#0-9]{3,20}$/;
    if (!nameRe.test(name)) { alert('Item name: 3-40 letters/numbers only.'); ev.preventDefault(); return false; }
    if (!colorRe.test(color)) { alert('Color is invalid.'); ev.preventDefault(); return false; }
    return true;
  });
}
function attachPreview() {
  const inp = document.getElementById('imageInput');
  if (!inp) return;
  inp.addEventListener('change', function () {
    const f = inp.files[0]; if (!f) return;
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('imgPreview').src = e.target.result; };
    reader.readAsDataURL(f);
  });
}

if (window.jQuery) {
  $(function () {
    $('#liveSearch').on('keyup', function () {
      const q = $(this).val().toLowerCase();
      $('.item-card').each(function () {
        $(this).toggle($(this).text().toLowerCase().includes(q));
      });
    });
  });
}
