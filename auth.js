// public/js/auth.js
async function api(path, opts={}){
  const res = await fetch('/api/' + path, opts);
  return res.json();
}

async function checkAuthUI(){
  try{
    const r = await api('auth.php?action=me');
    const authLink = document.getElementById('auth-link');
    const adminLink = document.getElementById('admin-link');
    if (r.user){
      if (authLink) authLink.textContent = 'Logout';
      if (adminLink && r.user.role==='admin') adminLink.style.display = 'inline';
    } else {
      if (authLink) authLink.textContent = 'Login';
      if (adminLink) adminLink.style.display = 'none';
    }
  }catch(e){console.error(e)}
}

if (document.getElementById('loginForm')){
  document.getElementById('loginForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const f = e.target;
    const data = {email:f.email.value, password:f.password.value};
    const r = await api('auth.php?action=login', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
    if (r.success){ location.href = '/'; } else { alert(r.error||'Login failed'); }
  });
}

if (document.getElementById('regForm')){
  document.getElementById('regForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const f = e.target;
    const data = {name:f.name.value, email:f.email.value, password:f.password.value};
    const r = await api('auth.php?action=register',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
    if (r.success){ alert('Registered. Please login.'); location.href='login.html'; } else { alert(r.error||'Registration failed'); }
  });
}

// Logout link behaviour
document.addEventListener('click', async (e)=>{
  if (e.target && e.target.id === 'auth-link'){
    e.preventDefault();
    if (e.target.textContent.trim().toLowerCase() === 'logout'){
      await api('auth.php?action=logout');
      location.href = '/';
    } else { location.href = '/login.html'; }
  }
});

checkAuthUI();
