// Simple helpers
function post(url, data) {
  return fetch(url, { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams(data) }).then(r => r.text());
}
