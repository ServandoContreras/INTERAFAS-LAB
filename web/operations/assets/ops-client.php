<?php
require __DIR__.'/../common.php';
require_operational_network(true);

header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store');
?>
window.INTERAFAS_OPS = Object.freeze({
  telemetryEndpoint: '/operations/telemetry.php',
  firmwareStatusEndpoint: '/operations/firmware-status.php',
  refreshInterval: 5000,
  firmwareRefreshInterval: 30000
});

(function () {
  function updateClock() {
    const node = document.querySelector('[data-ops-clock]');
    if (!node) return;
    node.textContent = new Intl.DateTimeFormat('es-MX', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    }).format(new Date());
  }

  document.addEventListener('DOMContentLoaded', function () {
    updateClock();
    if (document.querySelector('[data-ops-clock]')) {
      window.setInterval(updateClock, 1000);
    }

    const form = document.querySelector('[data-ops-login]');
    if (form) {
      form.addEventListener('submit', function () {
        const button = form.querySelector('button[type="submit"]');
        if (!button) return;
        button.disabled = true;
        button.textContent = 'Validando acceso…';
      });
    }
  });
})();
