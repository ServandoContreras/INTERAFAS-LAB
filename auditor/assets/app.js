(() => {
  const el = document.querySelector('[data-autorefresh]');
  if (el) setInterval(() => location.reload(), 15000);
  const dlg=document.getElementById('finishDialog');
  document.querySelectorAll('[data-open-finish]').forEach(b=>b.addEventListener('click',()=>dlg?.showModal()));
  document.getElementById('confirmFinish')?.addEventListener('click',(e)=>{
    e.preventDefault(); dlg?.close(); document.getElementById('finishForm')?.submit();
  });
})();

// v0.5.2 — checklist persistente por intento
for (const box of document.querySelectorAll('.challenge-check')) {
  box.addEventListener('change', async () => {
    const row = box.closest('.checklist-row');
    const body = new URLSearchParams({
      csrf: window.INTERAFAS_CSRF || '',
      flag_number: box.dataset.flag || '',
      item_index: box.dataset.item || '',
      checked: box.checked ? '1' : '0'
    });
    box.disabled = true;
    try {
      const r = await fetch('/checklist-toggle.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body});
      if (!r.ok) throw new Error('save failed');
      row?.classList.toggle('checked', box.checked);
    } catch (e) {
      box.checked = !box.checked;
      row?.classList.toggle('checked', box.checked);
      alert('No fue posible guardar el avance del checklist.');
    } finally { box.disabled = false; }
  });
}
