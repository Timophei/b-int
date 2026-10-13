/* Байкал Интеграция: учёт нажатий и откуда пришёл автор заявки */
(function () {
  var store = {
    get: function (k) { try { return JSON.parse(sessionStorage.getItem(k)); } catch (e) { return null; } },
    set: function (k, v) { try { sessionStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
  };

  // Запоминаем первую страницу визита: с неё видно источник и UTM-метки для заявки
  if (!store.get('bi_entry')) {
    store.set('bi_entry', { p: location.pathname, q: location.search, r: document.referrer });
  }

  function send(url, data) {
    var body = JSON.stringify(data);
    try {
      if (navigator.sendBeacon) { navigator.sendBeacon(url, new Blob([body], { type: 'application/json' })); }
      else { fetch(url, { method: 'POST', body: body, keepalive: true, headers: { 'Content-Type': 'application/json' } }); }
    } catch (e) {}
  }

  document.addEventListener('click', function (e) {
    var link = e.target.closest('[data-track]');
    if (!link) return;
    send('/a/click', { k: link.dataset.track, l: link.dataset.label || link.textContent.trim().slice(0, 150), p: location.pathname });
  });

  var form = document.querySelector('[data-lead-form]');
  if (form) {
    var entry = store.get('bi_entry') || {};
    form.elements.from.value = entry.p || location.pathname;
    form.elements.q.value = entry.q || '';
    form.elements.r.value = entry.r || '';
    form.addEventListener('submit', function () {
      var button = form.querySelector('button[type=submit]');
      if (button) { button.disabled = true; }
    });
  }
})();
