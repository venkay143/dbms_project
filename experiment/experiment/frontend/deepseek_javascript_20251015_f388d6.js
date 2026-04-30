// Add this to your existing JavaScript in index.html

// API Configuration
const API_BASE = '';
const ENDPOINTS = {
    login: 'login.php',
    transactions: 'transactions.php',
    reports: 'reports.php',
    analytics: 'analytics.php'
};

// Updated Login Function
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch(ENDPOINTS.login, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();
        
        if (data.success) {
            currentUser = data.user;
            initializeDashboard();
            showPage('dashboard-page');
        } else {
            alert(data.message || 'Login failed!');
        }
    } catch (error) {
        alert('Network error. Please try again.');
    }
});

// Updated Send Money Function
document.getElementById('send-money-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const recipient = document.getElementById('recipient').value;
    const amount = parseFloat(document.getElementById('amount').value);
    const description = document.getElementById('description').value;

    try {
        const response = await fetch(ENDPOINTS.transactions, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'create',
                sender_id: currentUser.id,
                recipient_email: recipient,
                amount: amount,
                description: description
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showTransactionResult(data.transaction);
            document.getElementById('send-money-form').reset();
            loadDashboardData();
        } else {
            alert(data.message || 'Transaction failed!');
        }
    } catch (error) {
        alert('Network error. Please try again.');
    }
});

// Updated Load Transactions Function
async function loadTransactions() {
    try {
        const viewRole = currentUser.role === 'admin' ? currentRole : currentUser.role;
        let action = viewRole === 'user' ? 'get_user' : 'get_all';
        let user_id = currentUser.id;

        const response = await fetch(ENDPOINTS.transactions, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                user_id: user_id
            })
        });

        const data = await response.json();
        
        if (data.success) {
            displayTransactions(data.transactions, viewRole);
        } else {
            console.error('Failed to load transactions:', data.message);
        }
    } catch (error) {
        console.error('Network error loading transactions:', error);
    }
}

function displayTransactions(transactions, viewRole) {
    let transactionsHTML = '';

    if (transactions.length === 0) {
        transactionsHTML = '<tr><td colspan="6" class="text-center text-muted">No transactions found</td></tr>';
    } else {
        transactions.forEach(tx => {
            transactionsHTML += `
                <tr>
                    <td>${tx.transaction_id}</td>
                    <td>${viewRole === 'user' ? tx.receiver_email : (tx.sender_name || 'Unknown')}</td>
                    <td>$${parseFloat(tx.amount).toFixed(2)}</td>
                    <td><span class="status-badge status-${tx.status}">${tx.status}</span></td>
                    <td>${new Date(tx.timestamp).toLocaleDateString()}</td>
                    <td>
                        ${(tx.status === 'fraud' || tx.status === 'refund_pending') && viewRole === 'user' ? 
                            `<button class="btn btn-sm btn-warning" onclick="showReportModal(${tx.transaction_id})">
                                <i class="fas fa-flag"></i> Report
                            </button>` : ''
                        }
                    </td>
                </tr>
            `;
        });
    }

    document.getElementById('transactions-table').innerHTML = transactionsHTML;
}

// Updated Report Function
async function submitReport() {
    const reason = document.getElementById('report-reason').value;
    
    if (!reason.trim()) {
        alert('Please provide a reason for reporting');
        return;
    }

    try {
        const response = await fetch(ENDPOINTS.reports, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                transaction_id: currentTransactionId,
                reporter_id: currentUser.id,
                reason: reason
            })
        });

        const data = await response.json();
        
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
            modal.hide();
            document.getElementById('report-reason').value = '';
            alert('Report submitted successfully!');
            loadDashboardData();
        } else {
            alert(data.message || 'Failed to submit report');
        }
    } catch (error) {
        alert('Network error submitting report');
    }
}

// Updated Analytics Function
async function loadAnalytics() {
    try {
        const response = await fetch(ENDPOINTS.analytics);
        const data = await response.json();
        
        if (data.success) {
            updateAnalyticsDashboard(data.analytics);
        }
    } catch (error) {
        console.error('Error loading analytics:', error);
    }
}

function updateAnalyticsDashboard(analytics) {
    const viewRole = currentUser.role === 'admin' ? currentRole : currentUser.role;
    let analyticsHTML = '';

    if (viewRole === 'user') {
        analyticsHTML = `
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Sent</h6>
                                <h3>$${analytics.total_transactions * 100}</h3>
                            </div>
                            <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Successful</h6>
                                <h3>${analytics.success_transactions}</h3>
                            </div>
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Pending</h6>
                                <h3>${analytics.refund_transactions}</h3>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Reported</h6>
                                <h3>${analytics.fraud_transactions}</h3>
                            </div>
                            <i class="fas fa-flag fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        analyticsHTML = `
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Users</h6>
                                <h3>${analytics.total_users}</h3>
                            </div>
                            <i class="fas fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Transactions</h6>
                                <h3>${analytics.total_transactions}</h3>
                            </div>
                            <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Pending Reports</h6>
                                <h3>${analytics.pending_reports}</h3>
                            </div>
                            <i class="fas fa-flag fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">System Health</h6>
                                <h3>100%</h3>
                            </div>
                            <i class="fas fa-heartbeat fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    document.getElementById('analytics-cards').innerHTML = analyticsHTML;
}
