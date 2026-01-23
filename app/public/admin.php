<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Menu Admin</title>
</head>
<body>
  <h1>Menu Admin</h1>
  <button id="reload">Reload</button>
  <button id="save">Save</button>

  <table id="tbl" border="1" cellpadding="6" style="margin-top:10px;">
    <thead>
      <tr>
        <th>ID</th><th>Name</th><th>Style</th><th>ABV</th><th>Price</th><th>Available</th><th>Order</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <script>
    const API_GET = '/api/menu_current.php';
    const API_SAVE = '/api/menu_save.php';
    function esc(s){ return String(s ?? '').replace(/"/g,'&quot;'); }

    async function load() {
      const res = await fetch(API_GET, { cache: 'no-store' });
      const data = await res.json();
      const tbody = document.querySelector('#tbl tbody');
      tbody.innerHTML = '';
      for (const it of data.items) {
        const tr = document.createElement('tr');
        tr.dataset.id = it.id;
        tr.innerHTML = `
          <td>${it.id}</td>
          <td><input data-k="name" value="${esc(it.name)}"></td>
          <td><input data-k="style" value="${esc(it.style)}"></td>
          <td><input data-k="abv" value="${esc(it.abv)}" size="4"></td>
          <td><input data-k="price" value="${esc(it.price)}" size="6"></td>
          <td><input data-k="is_available" type="checkbox" ${it.is_available ? 'checked' : ''}></td>
          <td><input data-k="sort_order" value="${esc(it.sort_order)}" size="4"></td>
        `;
        tbody.appendChild(tr);
      }
    }

    async function save() {
      const rows = [...document.querySelectorAll('#tbl tbody tr')];
      const items = rows.map(tr => {
        const get = (k) => tr.querySelector(`[data-k="${k}"]`);
        return {
          id: Number(tr.dataset.id),
          name: get('name').value,
          style: get('style').value || null,
          abv: get('abv').value === '' ? null : Number(get('abv').value),
          price: get('price').value === '' ? null : Number(get('price').value),
          is_available: get('is_available').checked ? 1 : 0,
          sort_order: Number(get('sort_order').value || 0),
        };
      });

      const res = await fetch(API_SAVE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Admin-Pin': '1234' },
        body: JSON.stringify({ items })
      });

      alert(JSON.stringify(await res.json()));
    }

    document.getElementById('reload').onclick = load;
    document.getElementById('save').onclick = save;
    load();
  </script>
</body>
</html>
