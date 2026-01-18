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
                    <h3>Teacher-Parent Messages</h3>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#composeModal">
                        <i class='bx bx-plus'></i> New Message
                    </button>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compose Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="composeForm">
                    <div class="mb-3">
                        <label>Select Child</label>
                        <select class="form-select" id="childSelectMsg" required>
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>To (Teacher)</label>
                        <select class="form-select" id="teacherSelect" required>
                            <option value="">Select teacher...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Subject</label>
                        <input type="text" class="form-control" id="msgSubject" required>
                    </div>
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea class="form-control" id="msgBody" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadMessages();
    loadChildrenForMsg();
    loadTeachers();
});

function loadMessages() {
    fetch('../assets/manageParentMessages.php?action=fetch_messages')
    .then(response => response.json())
    .then(data => {
        const content = document.getElementById('messagesContent');
        
        if (data.status === 'success' && data.messages && data.messages.length > 0) {
            let html = '<div class="list-group">';
            
            data.messages.forEach(msg => {
                const isRead = msg.is_read == 1;
                const bgClass = isRead ? '' : 'bg-light';
                
                html += `
                    <div class="list-group-item ${bgClass}" onclick="viewMessage(${msg.message_id})">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${msg.subject}</h6>
                            <small>${msg.created_at}</small>
                        </div>
                        <p class="mb-1"><small><strong>From:</strong> ${msg.sender_name || 'Teacher'}</small></p>
                        <small>${msg.message.substring(0, 100)}...</small>
                    </div>
                `;
            });
            
            html += '</div>';
            content.innerHTML = html;
        } else {
            content.innerHTML = `<div class="text-center text-muted"><i class='bx bx-message-x' style='font-size: 60px;'></i><p class="mt-2">No messages yet</p></div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('messagesContent').innerHTML = '<p class="text-center text-danger">Error loading messages</p>';
    });
}

function loadChildrenForMsg() {
    fetch('../assets/parentPortalHandler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_children'
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('childSelectMsg');
        if (data.status === 'success' && data.children.length > 0) {
            let html = '<option value="">-- Select Child --</option>';
            data.children.forEach(child => {
                html += `<option value="${child.id}">${child.name}</option>`;
            });
            select.innerHTML = html;
        }
    });
}

function loadTeachers() {
    fetch('../assets/fetchTeachers.php')
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('teacherSelect');
        let html = '<option value="admin">School Admin</option>';
        if (data && data.length > 0) {
            data.forEach(teacher => {
                html += `<option value="${teacher.id}">${teacher.fname || ''} ${teacher.lname || ''} - ${teacher.subject || ''}</option>`;
            });
        }
        select.innerHTML = html;
    })
    .catch(err => {
        console.error('Error loading teachers:', err);
        document.getElementById('teacherSelect').innerHTML = '<option value="admin">School Admin</option>';
    });
}

function sendMessage() {
    const studentId = document.getElementById('childSelectMsg').value;
    const receiverId = document.getElementById('teacherSelect').value || 'admin';
    const subject = document.getElementById('msgSubject').value;
    const message = document.getElementById('msgBody').value;
    
    if (!subject || !message) {
        alert('Please fill in all required fields');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'send_message');
    formData.append('student_id', studentId || '');
    formData.append('receiver_type', receiverId === 'admin' ? 'admin' : 'teacher');
    formData.append('receiver_id', receiverId);
    formData.append('receiver_name', receiverId === 'admin' ? 'School Admin' : 'Teacher');
    formData.append('subject', subject);
    formData.append('message', message);
    
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
</script>

<style>
.full-width { width: 100%; }
.list-group-item { cursor: pointer; transition: all 0.2s; }
.list-group-item:hover { background-color: #f0f0f0; }
</style>

<?php include('partials/_footer.php') ?>
