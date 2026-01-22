<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>Messages</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">Messages</a></li>
                </ul>
            </div>
        </div>

        <div class="bottom-data">
            <div class="orders full-width">
                <div class="header">
                    <i class='bx bx-message-dots'></i>
                    <h3>Parent-Admin Messages</h3>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#composeModal">
                        <i class='bx bx-plus'></i> New Message
                    </button>
                </div>
                
                <!-- Filter tabs -->
                <div class="p-3 border-bottom">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" onclick="filterMessages('all')">
                            All Messages
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="filterMessages('unread')">
                            Unread
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="filterMessages('sent')">
                            Sent
                        </button>
                    </div>
                </div>
                
                <div id="messagesContent" class="p-3">
                    <div class="text-center">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading messages...</p>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Compose Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compose Message to Parent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="composeForm">
                    <div class="mb-3">
                        <label>Select Student</label>
                        <select class="form-select" id="studentSelect" required onchange="loadParentForStudent()">
                            <option value="">Loading students...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Parent/Guardian</label>
                        <select class="form-select" id="parentSelect" required>
                            <option value="">Select student first...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Subject</label>
                        <input type="text" class="form-control" id="msgSubject" required>
                    </div>
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea class="form-control" id="msgBody" rows="5" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendMessage()">
                    <i class='bx bx-send'></i> Send
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let allMessages = [];
let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    loadMessages();
    loadStudents();
});

function loadMessages() {
    fetch('../assets/manageParentMessages.php?action=fetch_messages')
    .then(response => response.json())
    .then(data => {
        const content = document.getElementById('messagesContent');
        
        if (data.status === 'success' && data.messages && data.messages.length > 0) {
            allMessages = data.messages;
            displayMessages(allMessages);
        } else {
            content.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class='bx bx-message-x' style='font-size: 60px;'></i>
                    <p class="mt-2">No messages yet</p>
                </div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('messagesContent').innerHTML = 
            '<p class="text-center text-danger">Error loading messages</p>';
    });
}

function displayMessages(messages) {
    const content = document.getElementById('messagesContent');
    
    if (messages.length === 0) {
        content.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class='bx bx-message-x' style='font-size: 60px;'></i>
                <p class="mt-2">No messages found for this filter</p>
            </div>`;
        return;
    }
    
    let html = '<div class="list-group">';
    
    messages.forEach(msg => {
        const isRead = msg.is_read == 1;
        const bgClass = isRead ? '' : 'bg-light border-start border-primary border-3';
        const isSent = msg.sender_type === 'admin';
        const displayName = isSent ? msg.recipient_name : msg.sender_name;
        const direction = isSent ? 'To:' : 'From:';
        
        html += `
            <div class="list-group-item ${bgClass}" onclick="viewMessage(${msg.message_id})" style="cursor: pointer;">
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 ${!isRead && !isSent ? 'fw-bold' : ''}">${escapeHtml(msg.subject)}</h6>
                        <p class="mb-1">
                            <small class="text-muted">
                                <strong>${direction}</strong> ${escapeHtml(displayName || 'Unknown')}
                                ${msg.student_fname ? ' - <em>Re: ' + escapeHtml(msg.student_fname + ' ' + msg.student_lname) + '</em>' : ''}
                            </small>
                        </p>
                        <small class="text-muted">${escapeHtml(msg.message.substring(0, 100))}${msg.message.length > 100 ? '...' : ''}</small>
                    </div>
                    <div class="text-end ms-3">
                        <small class="text-muted">${formatDate(msg.sent_date || msg.created_at)}</small>
                        ${!isRead && !isSent ? '<br><span class="badge bg-primary">New</span>' : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    content.innerHTML = html;
}

function filterMessages(type) {
    currentFilter = type;
    
    // Update button states
    document.querySelectorAll('.btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    let filteredMessages = allMessages;
    
    if (type === 'unread') {
        filteredMessages = allMessages.filter(msg => msg.is_read == 0 && msg.sender_type !== 'admin');
    } else if (type === 'sent') {
        filteredMessages = allMessages.filter(msg => msg.sender_type === 'admin');
    }
    
    displayMessages(filteredMessages);
}

function loadStudents() {
    fetch('../assets/fetchStudents.php')
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('studentSelect');
        if (data && data.length > 0) {
            let html = '<option value="">-- Select Student --</option>';
            data.forEach(student => {
                html += `<option value="${student.id}">${student.fname || ''} ${student.lname || ''} - Class ${student.class || ''}</option>`;
            });
            select.innerHTML = html;
        } else {
            select.innerHTML = '<option value="">No students found</option>';
        }
    })
    .catch(err => {
        console.error('Error loading students:', err);
        document.getElementById('studentSelect').innerHTML = '<option value="">Error loading students</option>';
    });
}

function loadParentForStudent() {
    const studentId = document.getElementById('studentSelect').value;
    const parentSelect = document.getElementById('parentSelect');
    
    if (!studentId) {
        parentSelect.innerHTML = '<option value="">Select student first...</option>';
        return;
    }
    
    parentSelect.innerHTML = '<option value="">Loading...</option>';
    
    // Fetch guardian for this student
    fetch('../assets/fetchStudentInfo.php?id=' + studentId)
    .then(response => response.json())
    .then(data => {
        if (data && data.guardian_id) {
            parentSelect.innerHTML = `<option value="${data.guardian_id}">${data.guardian_fname || ''} ${data.guardian_lname || ''} (${data.guardian_relation || 'Guardian'})</option>`;
        } else {
            parentSelect.innerHTML = '<option value="">No guardian found for this student</option>';
        }
    })
    .catch(err => {
        console.error('Error loading parent:', err);
        parentSelect.innerHTML = '<option value="">Error loading parent</option>';
    });
}

function sendMessage() {
    const studentId = document.getElementById('studentSelect').value;
    const parentId = document.getElementById('parentSelect').value;
    const subject = document.getElementById('msgSubject').value.trim();
    const message = document.getElementById('msgBody').value.trim();
    
    if (!studentId || !parentId || !subject || !message) {
        alert('Please fill in all required fields');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'send_message');
    formData.append('sender_id', 'admin');
    formData.append('sender_type', 'admin');
    formData.append('recipient_id', parentId);
    formData.append('recipient_type', 'parent');
    formData.append('student_id', studentId);
    formData.append('subject', subject);
    formData.append('message_body', message);
    
    fetch('../assets/manageParentMessages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Message sent successfully!');
            document.getElementById('composeForm').reset();
            const modalEl = document.getElementById('composeModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            loadMessages();
        } else {
            alert('❌ Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Failed to send message');
    });
}

function viewMessage(messageId) {
    window.location.href = `message_detail.php?id=${messageId}`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // seconds
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
    if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
    
    return date.toLocaleDateString();
}
</script>

<style>
.full-width { width: 100%; }
.list-group-item { transition: all 0.2s; }
.list-group-item:hover { background-color: #f8f9fa; transform: translateX(5px); }
</style>

<?php include('partials/_footer.php') ?>
