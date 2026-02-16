<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Address Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
body { margin:0; font-family: Arial,sans-serif; background:#f4f6f9; }
.wrapper { display:flex; min-height:100vh; }
.sidebar { width:240px; background:#1e1e2f; color:#fff; padding:20px 0; position:fixed; height:100%; }
.logo { text-align:center; margin-bottom:20px; }
.logo img { width:120px; }
.nav a { display:flex; align-items:center; gap:10px; padding:12px 20px; color:#fff; text-decoration:none; font-size:14px; }
.nav a:hover, .nav a.active { background:#1976d2; }
.content { margin-left:240px; padding:20px; width:100%; }
h1 { margin-bottom:20px; color:#333; }

.summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:15px; margin-bottom:20px; }
.summary-card { background:#fff; border-radius:8px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.08); display:flex; align-items:center; gap:15px; cursor:pointer; transition: transform 0.2s, box-shadow 0.2s; }
.summary-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.summary-card .icon { font-size:36px; color:#1976d2; }
.summary-card h3 { margin:0; font-size:14px; color:#666; }
.summary-card p { margin:4px 0 0; font-size:22px; font-weight:bold; color:#333; }

.actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
.actions button { background:#1976d2; color:#fff; border:none; padding:10px 14px; border-radius:5px; cursor:pointer; font-size:14px; display:flex; align-items:center; gap:5px; }
.search-box input { padding:8px 12px; width:250px; border-radius:5px; border:1px solid #ccc; }

table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.08); border-radius:8px; overflow:hidden; }
th,td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
thead { background:#1976d2; color:#fff; }
tr:hover { background:#f1f1f1; }

.entries-info { margin-top:15px; display:flex; justify-content:space-between; align-items:center; font-size:14px; color:#666; }
.entries-info input { padding:6px 10px; border-radius:5px; border:1px solid #ccc; width:200px; }

/* Modal */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
.modal-content { background:#fff; padding:20px; border-radius:8px; width:400px; max-width:90%; position:relative; }
.modal-content h2 { margin-top:0; }
.modal-content label { display:block; margin-top:10px; font-weight:bold; }
.modal-content select, .modal-content input { width:100%; padding:8px 10px; margin-top:4px; border-radius:5px; border:1px solid #ccc; }
.modal-content button { margin-top:15px; background:#1976d2; color:#fff; border:none; padding:10px 14px; border-radius:5px; cursor:pointer; width:100%; }
.close-btn { position:absolute; top:10px; right:10px; cursor:pointer; font-size:20px; color:#888; }
</style>
</head>
<body>
<div class="wrapper">
<aside class="sidebar">
<div class="logo"><img src="assets/image.png" alt="Paragon Logo"></div>
<nav class="nav">
<nav class="nav">
      <a href="dashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
      <a href="user.php"><span class="material-icons">people</span> User</a>
      <a href="address.php"><span class="material-icons">location_on</span> Address</a>
      <a href="amountpaid.php"><span class="material-icons">checklist</span> Amount Paid</a>
      <a href="installation_fee.html"><span class="material-icons">attach_money</span> Installation Fee</a>
      <a href="call_out_status.html"><span class="material-icons">call</span> Call Out Status</a>
      <a href="pull_out_remarks.html"><span class="material-icons">notes</span> Pull Out Remarks</a>
      <a href="status_input.html"><span class="material-icons">input</span> Status Input Channel</a>
      <a href="sales_category.html"><span class="material-icons">category</span> Sales Category</a>
      <a href="main_remarks.html"><span class="material-icons">edit</span> Main Remarks</a>
      <a href="profile.html"><span class="material-icons">person</span> Profile</a>
      <a href="logout.html"><span class="material-icons">logout</span> Logout</a>
    </nav>
</nav>
</aside>

<main class="content">
<h1>Address Management</h1>

<div class="summary">
  <div class="summary-card" onclick="showLevel('region')">
    <span class="material-icons icon">public</span><div><h3>Regions</h3><p>17</p></div>
  </div>
  <div class="summary-card" onclick="showLevel('province')">
    <span class="material-icons icon">map</span><div><h3>Provinces</h3><p>82</p></div>
  </div>
  <div class="summary-card" onclick="showLevel('municipality')">
    <span class="material-icons icon">location_city</span><div><h3>Municipalities</h3><p>1,488</p></div>
  </div>
</div>

<div class="actions">
  <button onclick="openModal()"> <span class="material-icons">add_location</span> Add Address </button>
  <div class="search-box"><input type="text" id="searchInput" placeholder="Search..."></div>
</div>

<table id="dynamicTable">
<thead><tr id="tableHeader"></tr></thead>
<tbody id="tableBody"></tbody>
</table>

<div class="entries-info">
  <span id="entriesInfo">Showing 0 of 0 entries</span>
  <input type="text" id="filterInput" placeholder="Filter text...">
</div>
</main>
</div>

<!-- Modal -->
<div class="modal" id="addressModal">
<div class="modal-content">
  <span class="close-btn" onclick="closeModal()">&times;</span>
  <h2 id="modalTitle">Add Address</h2>

  <div id="regionDiv">
    <label for="regionInput">Region</label>
    <input type="text" id="regionInput" placeholder="Enter region">
  </div>

  <div id="provinceDiv" style="display:none">
    <label for="regionSelect">Region</label>
    <select id="regionSelect"></select>
    <label for="provinceInput">Province</label>
    <input type="text" id="provinceInput" placeholder="Enter province name">
  </div>

  <div id="municipalityDiv" style="display:none">
    <label for="regionSelectMun">Region</label>
    <select id="regionSelectMun" onchange="updateProvincesMun()"></select>
    <label for="provinceSelectMun">Province</label>
    <select id="provinceSelectMun"></select>
    <label for="municipalityInput">Municipality</label>
    <input type="text" id="municipalityInput" placeholder="Enter municipality name">
  </div>

  <button onclick="submitAddress()">Submit</button>
</div>
</div>

<script>
const data = [
  { name: "Region 1", provinces:[{name:"Province 1-1",municipalities:["Municipality 1-1-1"]}]},
  { name: "Region 2", provinces:[{name:"Province 2-1",municipalities:["Municipality 2-1-1"]}]}
];

const tableHeader = document.getElementById('tableHeader');
const tableBody = document.getElementById('tableBody');
const entriesInfo = document.getElementById('entriesInfo');
const filterInput = document.getElementById('filterInput');

const modal = document.getElementById('addressModal');
const regionDiv = document.getElementById('regionDiv');
const provinceDiv = document.getElementById('provinceDiv');
const municipalityDiv = document.getElementById('municipalityDiv');
const modalTitle = document.getElementById('modalTitle');

const regionInput = document.getElementById('regionInput');
const regionSelect = document.getElementById('regionSelect');
const provinceInput = document.getElementById('provinceInput');
const regionSelectMun = document.getElementById('regionSelectMun');
const provinceSelectMun = document.getElementById('provinceSelectMun');
const municipalityInput = document.getElementById('municipalityInput');

let currentLevel='region';

function showLevel(level){
  currentLevel=level;
  renderTable(data,level);
}

function renderTable(levelData,level){
  tableHeader.innerHTML=''; tableBody.innerHTML='';
  if(level==='region'){
    tableHeader.innerHTML='<th>Region Name</th>';
    levelData.forEach(i=>{ const tr=document.createElement('tr'); tr.innerHTML=`<td>${i.name}</td>`; tableBody.appendChild(tr); });
    entriesInfo.textContent=`Showing 1 of ${levelData.length} entries`;
  }
  if(level==='province'){
    tableHeader.innerHTML='<th>Province Name</th><th>Region</th>';
    const provinces=levelData.flatMap(r=>r.provinces.map(p=>({province:p.name,region:r.name})));
    provinces.forEach(i=>{ const tr=document.createElement('tr'); tr.innerHTML=`<td>${i.province}</td><td>${i.region}</td>`; tableBody.appendChild(tr); });
    entriesInfo.textContent=`Showing 1 of ${provinces.length} entries`;
  }
  if(level==='municipality'){
    tableHeader.innerHTML='<th>Municipality</th><th>Province</th><th>Region</th>';
    const municipalities=levelData.flatMap(r=>r.provinces.flatMap(p=>p.municipalities.map(m=>({mun:m,province:p.name,region:r.name}))));
    municipalities.forEach(i=>{ const tr=document.createElement('tr'); tr.innerHTML=`<td>${i.mun}</td><td>${i.province}</td><td>${i.region}</td>`; tableBody.appendChild(tr); });
    entriesInfo.textContent=`Showing 1 of ${municipalities.length} entries`;
  }
}

function openModal(){
  regionDiv.style.display=provinceDiv.style.display=municipalityDiv.style.display='none';
  if(currentLevel==='region'){ regionDiv.style.display='block'; modalTitle.textContent='Add Region'; regionInput.value=''; }
  if(currentLevel==='province'){ provinceDiv.style.display='block'; modalTitle.textContent='Add Province'; provinceInput.value=''; populateRegions(regionSelect);}
  if(currentLevel==='municipality'){ municipalityDiv.style.display='block'; modalTitle.textContent='Add Municipality'; municipalityInput.value=''; populateRegions(regionSelectMun); updateProvincesMun();}
  modal.style.display='flex';
}

function closeModal(){ modal.style.display='none'; }

function populateRegions(selectElement){
  selectElement.innerHTML='<option value="">Select Region</option>';
  data.forEach(r=>{ const opt=document.createElement('option'); opt.value=r.name; opt.textContent=r.name; selectElement.appendChild(opt); });
}

function updateProvincesMun(){
  const region=data.find(r=>r.name===regionSelectMun.value);
  provinceSelectMun.innerHTML='<option value="">Select Province</option>';
  if(region){ region.provinces.forEach(p=>{ const opt=document.createElement('option'); opt.value=p.name; opt.textContent=p.name; provinceSelectMun.appendChild(opt); }); }
}

function submitAddress(){
  if(currentLevel==='region'){ const r=regionInput.value.trim(); if(!r){alert('Enter region'); return;} data.push({name:r, provinces:[]}); }
  if(currentLevel==='province'){ const r=regionSelect.value,p=provinceInput.value.trim(); if(!r||!p){alert('Select region and enter province'); return;} data.find(x=>x.name===r).provinces.push({name:p,municipalities:[]}); }
  if(currentLevel==='municipality'){
    const r=regionSelectMun.value,p=provinceSelectMun.value,m=municipalityInput.value.trim();
    if(!r||!p||!m){alert('Fill all fields'); return;}
    let regionObj=data.find(x=>x.name===r);
    if(!regionObj){ regionObj={name:r, provinces:[{name:p, municipalities:[m]}]}; data.push(regionObj);}
    else{ let provObj=regionObj.provinces.find(x=>x.name===p); if(!provObj){regionObj.provinces.push({name:p, municipalities:[m]});} else if(!provObj.municipalities.includes(m)){provObj.municipalities.push(m);} }
  }
  alert('Address added successfully!');
  closeModal();
  renderTable(data,currentLevel);
}

filterInput.addEventListener('input',()=>{
  const v=filterInput.value.toLowerCase(); 
  const rows=tableBody.getElementsByTagName('tr'); 
  let c=0; 
  Array.from(rows).forEach(r=>{ const t=r.innerText.toLowerCase(); if(t.includes(v)){ r.style.display=''; c++; } else r.style.display='none'; });
  entriesInfo.textContent=`Showing 1 of ${c} entries`;
});

window.onclick=e=>{ if(e.target===modal) closeModal(); }

renderTable(data,'region');
</script>
</body>
</html>
