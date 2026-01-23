<!doctype html>
<html>

<head>
  <meta charset="utf-8" />
  <title>Gnarly Cedar Tap Menu</title>
  <link rel="stylesheet" href="/assets/app.css">
</head>

<body class="display">
  <div class="top">
    <h1 id="menuTitle">Gnarly Cedar Tap Menu</h1>
    <div class="meta">
      <div id="updatedAt"></div>
      <div id="qr"></div>
    </div>
  </div>
  <div id="items" class="items"></div>

  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <script>
    const WS_URL = (() => {
      const proto = location.protocol === 'https:' ? 'wss' : 'ws';
      return `${proto}://${location.host}/ws/`;
    })();

    const MENU_API = '/api/menu_current.php';
    const PUBLIC_MENU_URL = "http://3.137.136.13/menu.php";

    let currentVersion = null;

    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
      }[c]));
    }

    async function loadAndRender() {
      const res = await fetch(MENU_API, { cache: 'no-store' });
      const data = await res.json();
      const menu = data.menu;
      const items = data.items || [];
      currentVersion = menu.version;

      // document.getElementById('menuTitle').textContent = menu.name;
      document.getElementById('updatedAt').textContent = `Updated: ${menu.updated_at} (v${menu.version})`;

      const container = document.getElementById('items');
      container.innerHTML = `
        <div class="menu-grid">
          <section class="col col-house">
            <div class="col-heading sr-only">House</div>
            <div class="col-items" id="houseItems"></div>
          </section>

          <section class="col col-guest">
            <div class="col-heading">GUEST TAPS</div>
            <div class="col-items" id="guestItems"></div>
          </section>
        </div>
      `;

      const house = document.getElementById('houseItems');
      const guest = document.getElementById('guestItems');

      for (const it of items) {
        if (!it.is_available) continue;

        const row = document.createElement('div');
        row.className = 'item';

        const left = document.createElement('div');
        left.innerHTML = `<div class="name">${escapeHtml(it.name)}</div>
                          <div class="sub">${escapeHtml(it.style || '')}${it.abv ? ` - ${it.abv}%` : ''}</div>`;

        const right = document.createElement('div');
        right.className = 'right';
        // poster shows numbers, not $7.00 – keep yours if you want dollars
        right.textContent = it.price ? `${Number(it.price).toFixed(0)}` : '';

        row.appendChild(left);
        row.appendChild(right);

        // IMPORTANT: this assumes API returns non_guest_tap (1=house, 0=guest)
        (Number(it.non_guest_tap) === 1 ? house : guest).appendChild(row);
      }

      const qrEl = document.getElementById('qr');
      qrEl.innerHTML = '';
      new QRCode(qrEl, { text: `${PUBLIC_MENU_URL}?v=${menu.version}`, width: 128, height: 128 });
    }

    function connectWs() {
      const ws = new WebSocket(WS_URL);
      ws.onclose = () => setTimeout(connectWs, 1000);
      ws.onerror = () => ws.close();
      ws.onmessage = async (ev) => {
        try {
          const msg = JSON.parse(ev.data);
          if (msg.type === 'menu_updated' && msg.version !== currentVersion) {
            await loadAndRender();
          }
        } catch { }
      };
    }

    (async () => { await loadAndRender(); connectWs(); })();
  </script>
</body>

</html>