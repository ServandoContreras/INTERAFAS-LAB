(() => {
  const el = document.querySelector('[data-autorefresh]');
  if (el) setInterval(() => location.reload(), 15000);
  const dlg=document.getElementById('finishDialog');
  document.querySelectorAll('[data-open-finish]').forEach(b=>b.addEventListener('click',()=>dlg?.showModal()));
  document.getElementById('confirmFinish')?.addEventListener('click',async(e)=>{
    e.preventDefault();

    const btn=e.currentTarget;
    const form=document.getElementById('finishForm');
    if(!form)return;

    btn.disabled=true;
    const original=btn.textContent;
    btn.textContent='Generando ZIP y restableciendo…';

    try{
      const body=new URLSearchParams(new FormData(form));
      const response=await fetch(form.action||'finish.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
        body,
        credentials:'same-origin',
        cache:'no-store'
      });

      if(!response.ok){
        let message='No fue posible finalizar el laboratorio.';
        try{
          const data=await response.json();
          if(data?.message)message=data.message;
        }catch(_){}
        throw new Error(message);
      }

      const blob=await response.blob();
      const disposition=response.headers.get('Content-Disposition')||'';
      const match=disposition.match(/filename="?([^";]+)"?/i);
      const filename=match?.[1]||'INTERAFAS.zip';

      const url=URL.createObjectURL(blob);
      const a=document.createElement('a');
      a.href=url;
      a.download=filename;
      document.body.appendChild(a);
      a.click();
      a.remove();

      setTimeout(()=>URL.revokeObjectURL(url),4000);
      dlg?.close();

      const resetState=response.headers.get('X-INTERAFAS-Lab-Reset')||'unknown';
      if(resetState==='partial'){
        alert('El ZIP fue generado, pero una parte del restablecimiento automático reportó una advertencia. Revisa restablecimiento.json dentro del archivo.');
      }

      setTimeout(()=>{
        window.location.href='login.php?reset=1';
      },900);
    }catch(err){
      btn.disabled=false;
      btn.textContent=original;
      alert(String(err?.message||err));
    }
  });
  const backToTop=document.getElementById('backToTop');
  if(backToTop){
    const syncBackToTop=()=>backToTop.classList.toggle('visible',window.scrollY>320);
    window.addEventListener('scroll',syncBackToTop,{passive:true});
    syncBackToTop();
    backToTop.addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
  }
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
