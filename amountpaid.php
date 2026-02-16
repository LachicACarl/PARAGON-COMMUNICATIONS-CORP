<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Amount Paid</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f4f6f9;
    }

    .wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* ===== Sidebar ===== */
    .sidebar {
      width: 240px;
      background: #1e1e2f;
      color: #fff;
      padding: 20px 0;
      position: fixed;
      height: 100%;
    }

    .logo {
      text-align: center;
      margin-bottom: 20px;
    }

    .logo img {
      width: 120px;
    }

    .nav a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      color: #fff;
      text-decoration: none;
      font-size: 14px;
    }

    .nav a:hover,
    .nav a.active {
      background: #1976d2;
    }

    /* ===== Main Content ===== */
    .content {
      margin-left: 240px;
      padding: 20px;
      width: 100%;
    }

    h1 {
      margin-bottom: 20px;
      color: #333;
    }

    /* ===== Summary Cards ===== */
    .summary {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .summary-card {
      background: #fff;
      border-radius: 8px;
      padding: 16px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .summary-card .icon {
      font-size: 36px;
      color: #1976d2;
    }

    .summary-card h3 {
      margin: 0;
      font-size: 14px;
      color: #666;
    }

    .summary-card p {
      margin: 4px 0 0;
      font-size: 22px;
      font-weight: bold;
      color: #333;
    }

    /* ===== Actions & Search ===== */
    .actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .actions button {
      background: #1976d2;
      color: #fff;
      border: none;
      padding: 10px 14px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .search-box input {
      padding: 8px 12px;
      width: 250px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    /* ===== Table ===== */
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      border-radius: 8px;
      overflow: hidden;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    thead {
      background: #1976d2;
      color: #fff;
    }

    tr:hover {
      background: #f1f1f1;
    }

    .entries-info {
      margin-top: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 14px;
      color: #666;
    }

    .entries-info input {
      padding: 6px 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
      width: 200px;
    }

    /* ===== Modal ===== */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      width: 400px;
      max-width: 90%;
    }

    .modal-content h3 {
      margin-top: 0;
    }

    .modal-content label {
      display: block;
      margin: 10px 0 5px;
    }

    .modal-content input {
      width: 100%;
      padding: 8px 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .modal-content button {
      margin-top: 15px;
      padding: 10px 14px;
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .close-btn {
      background: #e53935;
      float: right;
      margin-top: -5px;
    }
  </style>
</head>
<body>

<div class="wrapper">

  <!-- ===== Sidebar ===== -->
  <aside class="sidebar">
    <div class="logo">
      <img src="assets/image.png" alt="Paragon Logo">
    </div>

    <nav class="nav">
      <a href="dashboard.php"><span class="material-icons">dashboard</span>Dashboard</a>
      <a href="user.php"><span class="material-icons">people</span>User</a>
      <a href="address.php"><span class="material-icons">location_on</span>Address</a>
      <a href="amount_paid.php" class="active"><span class="material-icons">checklist</span>Amount Paid</a>
      <a href="installation_fee.html"><span class="material-icons">attach_money</span>Installation Fee</a>
      <a href="call_out_status.html"><span class="material-icons">call</span>Call Out Status</a>
      <a href="pull_out_remarks.html"><span class="material-icons">notes</span>Pull Out Remarks</a>
      <a href="status_input.html"><span class="material-icons">input</span>Status Input</a>
      <a href="sales_category.html"><span class="material-icons">category</span>Sales Category</a>
      <a href="main_remarks.html"><span class="material-icons">edit</span>Main Remarks</a>
      <a href="profile.html"><span class="material-icons">person</span>Profile</a>
      <a href="logout.html"><span class="material-icons">logout</span>Logout</a>
    </nav>
  </aside>

  <!-- ===== Main Content ===== -->
  <main class="content">
    <h1>Amount Paid</h1>

    <!-- ===== Summary Cards ===== -->
    <div class="summary">
      <div class="summary-card">
        <span class="material-icons icon">people</span>
        <div>
          <h3>Total Clients</h3>
          <p>50</p>
        </div>
      </div>

      <div class="summary-card">
        <span class="material-icons icon">check_circle</span>
        <div>
          <h3>Total Paid</h3>
          <p>35</p>
        </div>
      </div>

      <div class="summary-card">
        <span class="material-icons icon">cancel</span>
        <div>
          <h3>Total Unpaid</h3>
          <p>15</p>
        </div>
      </div>
    </div>

    <!-- ===== Actions & Search ===== -->
    <div class="actions">
      <button id="addPaymentBtn">
        <span class="material-icons">add</span>
        Add Payment
      </button>

      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search client...">
      </div>
    </div>

    <!-- ===== Payment Table ===== -->
    <table id="paymentTable">
      <thead>
        <tr>
          <th>Client Name</th>
          <th>Amount Paid</th>
          <th>Payment Date</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Juan Dela Cruz</td>
          <td>₱5,000</td>
          <td>2026-02-01</td>
        </tr>
        <tr>
          <td>Maria Santos</td>
          <td>₱3,500</td>
          <td>2026-02-03</td>
        </tr>
      </tbody>
    </table>

    <div class="entries-info">
      <span id="entriesInfo">Showing 2 of 2 entries</span>
    </div>

  </main>
</div>

<!-- ===== Modal ===== -->
<div class="modal" id="addPaymentModal">
  <div class="modal-content">
    <button class="close-btn" onclick="closeModal()">X</button>
    <h3>Add Payment</h3>
    <label>Client Name</label>
    <input type="text" id="clientName">
    <label>Amount Paid</label>
    <input type="number" id="amountPaid">
    <label>Payment Date</label>
    <input type="date" id="paymentDate">
    <button onclick="addPayment()">Submit</button>
  </div>
</div>

<script>
  const addPaymentBtn = document.getElementById('addPaymentBtn');
  const modal = document.getElementById('addPaymentModal');
  const paymentTable = document.getElementById('paymentTable').getElementsByTagName('tbody')[0];
  const entriesInfo = document.getElementById('entriesInfo');
  const searchInput = document.getElementById('searchInput');

  addPaymentBtn.addEventListener('click', () => {
    modal.style.display = 'flex';
  });

  function closeModal() {
    modal.style.display = 'none';
  }

  function addPayment() {
    const name = document.getElementById('clientName').value;
    const amount = document.getElementById('amountPaid').value;
    const date = document.getElementById('paymentDate').value;

    if(name && amount && date) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${name}</td><td>₱${amount}</td><td>${date}</td>`;
      paymentTable.appendChild(tr);

      updateEntries();
      closeModal();

      // clear inputs
      document.getElementById('clientName').value = '';
      document.getElementById('amountPaid').value = '';
      document.getElementById('paymentDate').value = '';
    }
  }

  function updateEntries() {
    const rows = paymentTable.getElementsByTagName('tr');
    entriesInfo.textContent = `Showing ${rows.length} of ${rows.length} entries`;
  }

  // Search functionality
  searchInput.addEventListener('input', () => {
    const filter = searchInput.value.toLowerCase();
    const rows = paymentTable.getElementsByTagName('tr');
    let count = 0;
    Array.from(rows).forEach(row => {
      const text = row.innerText.toLowerCase();
      if(text.includes(filter)) {
        row.style.display = '';
        count++;
      } else {
        row.style.display = 'none';
      }
    });
    entriesInfo.textContent = `Showing ${count} of ${rows.length} entries`;
  });
</script>

</body>
</html>
