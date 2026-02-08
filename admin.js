// public/js/admin.js
async function api(path, opts={}){ const r = await fetch('/api/'+path, opts); return r.json(); }

async function loadAdminFoods(){
  const foods = await api('foods.php');
  const list = document.getElementById('foodsList'); list.innerHTML='';
  foods.forEach(f=>{
    const div = document.createElement('div');
    div.innerHTML = `<strong>${f.name}</strong> - ${f.price} TZS <button data-id="${f.id}" class="del">Delete</button>`;
    list.appendChild(div);
  });
  document.querySelectorAll('.del').forEach(b=>b.addEventListener('click', async (e)=>{
    const id = e.target.dataset.id;
    const form = new FormData(); form.append('action','delete'); form.append('id',id);
    const res = await fetch('/api/foods.php',{method:'POST',body:form}); const j = await res.json(); if (j.success) loadAdminFoods(); else alert(j.error||'Failed');
  }));
}

if (document.getElementById('foodForm')){
  document.getElementById('foodForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const f = e.target; const data = {name:f.name.value, description:f.description.value, price:parseInt(f.price.value), category_id:parseInt(f.category_id.value), image:f.image.value};
    const res = await fetch('/api/foods.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
    const j = await res.json(); if (j.success){ alert('Created'); f.reset(); loadAdminFoods(); } else alert(j.error||'Failed');
  });
}

window.addEventListener('DOMContentLoaded', ()=>{ loadAdminFoods(); });
