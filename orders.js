// public/js/orders.js
async function api(path, opts={}){ const r = await fetch('/api/'+path, opts); return r.json(); }

async function loadOrders(){
  const list = document.getElementById('ordersList'); list.innerHTML='Loading...';
  const res = await api('orders.php');
  if (res.error){ list.innerHTML = '<p>'+res.error+'</p>'; return; }
  const orders = res;
  list.innerHTML='';
  if (!orders.length) { list.innerHTML='<p>No orders</p>'; return; }
  orders.forEach(o=>{
    const div = document.createElement('div'); div.className='card';
    const header = document.createElement('div'); header.innerHTML = `<strong>Order #${o.id}</strong> - ${o.status} - ${o.total_price} TZS`;
    div.appendChild(header);
    if (o.items && o.items.length){
      const ul = document.createElement('ul');
      o.items.forEach(it=>{ const li = document.createElement('li'); li.textContent = `${it.food_name} x ${it.quantity} = ${it.quantity*it.price_each} TZS`; ul.appendChild(li); });
      div.appendChild(ul);
    }
    // If admin, fetch admin data to show controls
    const maybeControls = document.createElement('div');
    maybeControls.innerHTML = `<label>Change status: <select data-order-id="${o.id}"><option>pending</option><option>processing</option><option>completed</option><option>cancelled</option></select></label> <button class="saveStatus">Save</button>`;
    div.appendChild(maybeControls);
    list.appendChild(div);
  });

  // wire controls (attempt, will error if not admin)
  document.querySelectorAll('.saveStatus').forEach(b=>b.addEventListener('click', async (e)=>{
    const sel = e.target.previousElementSibling.querySelector('select');
    const orderId = sel.dataset.orderId;
    const status = sel.value;
    const res2 = await api('admin.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'update_order_status',order_id:orderId,status})});
    if (res2.success) { alert('Updated'); loadOrders(); } else alert(res2.error||'Failed');
  }));
}

document.addEventListener('DOMContentLoaded', ()=>{ loadOrders(); });
