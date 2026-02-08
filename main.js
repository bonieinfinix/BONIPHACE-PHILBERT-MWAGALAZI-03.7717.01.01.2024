// public/js/main.js
async function fetchJSON(path){
  const r = await fetch('/api/' + path);
  return r.json();
}

async function loadCategories(){
  const cats = await fetchJSON('foods.php'); // we'll extract categories from returned foods
  const catSet = new Map();
  cats.forEach(f=>catSet.set(f.category_id, f.category_name));
  const sel = document.getElementById('category');
  if (!sel) return;
  sel.innerHTML = '<option value="">All categories</option>';
  catSet.forEach((v,k)=>{
    const o = document.createElement('option'); o.value=k; o.textContent=v; sel.appendChild(o);
  });
  // also populate admin select if present
  const adminSel = document.getElementById('catSelect');
  if (adminSel) { adminSel.innerHTML = ''; catSet.forEach((v,k)=>{ const o=document.createElement('option');o.value=k;o.textContent=v;adminSel.appendChild(o); }); }
}

async function renderFoods(q='',category=''){
  const params = new URLSearchParams(); if (q) params.append('q',q); if (category) params.append('category',category);
  const foods = await fetchJSON('foods.php?'+params.toString());
  const container = document.getElementById('foods'); if(!container) return;
  container.innerHTML='';
  const t = document.getElementById('food-card');
  foods.forEach(f=>{
    const node = t.content.cloneNode(true);
    const img = node.querySelector('img'); img.src = f.image || 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"></svg>';
    node.querySelector('h3').textContent = f.name;
    node.querySelector('.cat').textContent = f.category_name;
    node.querySelector('.desc').textContent = f.description || '';
    node.querySelector('.price').textContent = f.price + ' TZS';
    const add = node.querySelector('.add');
    add.addEventListener('click', ()=>{
      const qty = parseInt(node.querySelector('.qty').value)||1;
      addToCart({food_id:f.id, name:f.name, price_each:f.price, quantity:qty});
      alert('Added to cart');
    });
    container.appendChild(node);
  });
}

function addToCart(item){
  const cart = JSON.parse(localStorage.getItem('cart')||'[]');
  const existing = cart.find(c=>c.food_id===item.food_id);
  if (existing) existing.quantity += item.quantity; else cart.push(item);
  localStorage.setItem('cart', JSON.stringify(cart));
}

function renderCart(){
  const container = document.getElementById('cartItems'); if(!container) return;
  const cart = JSON.parse(localStorage.getItem('cart')||'[]');
  container.innerHTML=''; let total=0;
  cart.forEach((it,idx)=>{
    const div = document.createElement('div');
    div.textContent = `${it.name} x ${it.quantity} = ${it.quantity*it.price_each} TZS`;
    const btn = document.createElement('button'); btn.textContent='Remove'; btn.addEventListener('click', ()=>{ cart.splice(idx,1); localStorage.setItem('cart',JSON.stringify(cart)); renderCart(); });
    div.appendChild(btn);
    container.appendChild(div);
    total += it.quantity*(it.price_each||it.price);
  });
  const totalSpan = document.getElementById('cartTotal'); if (totalSpan) totalSpan.textContent = total;
}

async function placeOrder(){
  const cart = JSON.parse(localStorage.getItem('cart')||'[]');
  if (!cart.length){ alert('Cart empty'); return; }
  // normalize item fields
  const items = cart.map(it=>({food_id:it.food_id,quantity:it.quantity,price_each:it.price_each||it.price}));
  const res = await fetch('/api/orders.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({items})});
  const j = await res.json();
  if (j.success){ alert('Order placed'); localStorage.removeItem('cart'); renderCart(); } else alert(j.error||'Order failed');
}

document.addEventListener('DOMContentLoaded', ()=>{
  if (document.getElementById('foods')){
    loadCategories().then(()=>renderFoods());
    document.getElementById('searchBtn').addEventListener('click', ()=>{
      const q = document.getElementById('q').value; const cat = document.getElementById('category').value; renderFoods(q,cat);
    });
  }
  if (document.getElementById('cartItems')){
    renderCart();
    document.getElementById('placeOrder').addEventListener('click', placeOrder);
  }
});
