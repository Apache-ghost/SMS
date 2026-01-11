<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>Fee Status</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Fee Status</a></li>
                </ul>
            </div>
        </div>

        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-user'></i>
                    <h3>Select Child</h3>
                </div>
                <div class="p-3">
                    <select class="form-select" id="childSelect" onchange="loadFees()">
                        <option value="">Loading children...</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bottom-data mt-3">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-money'></i>
                    <h3>Fee Details</h3>
                </div>
                
                <div id="feesContent" class="p-3">
                    <p class="text-center text-muted">Select a child to view fee status</p>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadChildren();
});

function loadChildren() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_children'
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('childSelect');
        if (data.status === 'success' && data.children.length > 0) {
            let html = '<option value="">-- Select Child --</option>';
            data.children.forEach(child => {
                html += `<option value="${child.id}">${child.name} (Class ${child.class})</option>`;
            });
            select.innerHTML = html;
        } else {
            select.innerHTML = '<option value="">No children found</option>';
        }
    });
}

function loadFees() {
    const studentId = document.getElementById('childSelect').value;
    const content = document.getElementById('feesContent');
    
    if (!studentId) {
        content.innerHTML = '<p class="text-center text-muted">Select a child to view fee status</p>';
        return;
    }
    
    // Sample fee data - replace with actual API
    const feeData = {
        total_annual_fee: 50000,
        paid_amount: 30000,
        pending_amount: 20000,
        installments: [
            { month: 'April 2026', amount: 10000, status: 'Paid', date: '2026-04-05' },
            { month: 'May 2026', amount: 10000, status: 'Paid', date: '2026-05-03' },
            { month: 'June 2026', amount: 10000, status: 'Paid', date: '2026-06-02' },
            { month: 'July 2026', amount: 10000, status: 'Pending', date: '-' },
            { month: 'August 2026', amount: 10000, status: 'Pending', date: '-' }
        ]
    };
    
    let html = `
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <h4>₹${feeData.total_annual_fee.toLocaleString()}</h4>
                        <p>Total Annual Fee</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h4>₹${feeData.paid_amount.toLocaleString()}</h4>
                        <p>Amount Paid</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <h4>₹${feeData.pending_amount.toLocaleString()}</h4>
                        <p>Pending Amount</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Month</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    feeData.installments.forEach(inst => {
        const statusClass = inst.status === 'Paid' ? 'success' : 'warning';
        const action = inst.status === 'Pending' ? 
            `<button class="btn btn-sm btn-primary" onclick="payFee('${inst.month}')">Pay Now</button>` :
            `<button class="btn btn-sm btn-secondary" disabled>Paid</button>`;
        
        html += `
            <tr>
                <td>${inst.month}</td>
                <td>₹${inst.amount.toLocaleString()}</td>
                <td><span class="badge bg-${statusClass}">${inst.status}</span></td>
                <td>${inst.date}</td>
                <td>${action}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    content.innerHTML = html;
}

function payFee(month) {
    alert('Payment gateway integration for ' + month + ' will be implemented here');
}
</script>

<style>.full-width { width: 100%; }</style>

<?php include('partials/_footer.php') ?>
