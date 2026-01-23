<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Menu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Menu</h1>
  <div id="items"></div>

  <script>
    fetch('/api/menu_current.php', { cache: 'no-store' })
      .then(r => r.json())
      .then(data => {
        const el = document.getElementById('items');
        el.innerHTML = '';
        for (const it of data.items) {
          if (!it.is_available) continue;
          const div = document.createElement('div');
          div.textContent = `${it.name} ${it.price ? `- $${Number(it.price).toFixed(2)}` : ''}`;
          el.appendChild(div);
        }
      });
  </script>
</body>
</html>
