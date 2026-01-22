<?php include('partials/_header.php') ?>

<!-- Sidebar -->
<?php include('partials/_sidebar.php') ?>

<div class="content">
    <?php include("partials/_navbar.php"); ?>

    <main>
        <div class="header">
            <div class="left">
                <h1>Message Detail</h1>
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active">View</a></li>
                </ul>
            </div>
            <a href="messages.php" class="btn btn-secondary">
                <i class='bx bx-arrow-back'></i> Back to Messages
            </a>
        </div>

        <div class="bottom-data">
            <div class="orders full-width">
                <div id="messageContent" class="p-4">
                    <div class="text-center">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading message...</p>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const messageId = urlParams.get('id');
    
    if (messageId) {
        loadMessage(messageId);
    } else {
        document.getElementById('messageContent').innerHTML = 
            '<p class="text-center text-danger">Invalid message ID</p>';
    }
});

function loadMessage(messageId) {
    const formData = new FormData();
    formData.append('action', 'get_message_thread');
    formData.append('message_id', messageId);
    
    fetch('../assets/manageParentMessages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.data) {
            displayMessage(data.data);
            // Mark as read if we're the recipient
            if (data.data.recipient_type === 'parent' && data.data.is_read == 0) {
                markAsRead(messageId);
            }
        } else {
            document.getElementById('messageContent').innerHTML = 
                '<p class="text-center text-danger">Message not found</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('messageContent').innerHTML = 
            '<p class="text-center text-danger">Error loading message</p>';
    });
}

function displayMessage(msg) {
    const content = document.getElementById('messageContent');
    const isReceiver = msg.recipient_type === 'parent';
    
    let html = `
        <div class="message-thread">
            <!-- Main Message -->
            <div class="card mb-3">
                <div class="card-header ${isReceiver ? 'bg-primary text-white' : 'bg-secondary text-white'}">
                    <h5 class="mb-0">${escapeHtml(msg.subject)}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>From:</strong> ${escapeHtml(msg.sender_name || 'Unknown')}
                            ${msg.sender_type !== 'parent' ? `<span class="badge bg-info ms-2">${msg.sender_type}</span>` : ''}
                        </div>
                        <div class="col-md-6 text-end">
                            <strong>Date:</strong> ${formatFullDate(msg.sent_date || msg.created_at)}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>To:</strong> ${escapeHtml(msg.recipient_name || 'Unknown')}
                        </div>
                        ${msg.student_fname ? `
                        <div class="col-md-6 text-end">
                            <strong>Regarding:</strong> ${escapeHtml(msg.student_fname + ' ' + msg.student_lname)}
                        </div>
                        ` : ''}
                    </div>
                    <hr>
                    <div class="message-body" style="white-space: pre-wrap;">${escapeHtml(msg.message_body || msg.message)}</div>
                    
                    ${msg.attachments && msg.attachments.length > 0 ? `
                    <hr>
                    <h6>Attachments:</h6>
                    <ul class="list-unstyled">
                        ${msg.attachments.map(att => `
                            <li>
                                <i class='bx bx-file'></i> 
                                <a href="${att.file_path}" target="_blank">${escapeHtml(att.file_name)}</a>
                                <small class="text-muted">(${formatFileSize(att.file_size)})</small>
                            </li>
                        `).join('')}
                    </ul>
                    ` : ''}
                </div>
            </div>
            
            <!-- Replies -->
            ${msg.replies && msg.replies.length > 0 ? `
            <div class="replies-section mb-3">
                <h5 class="mb-3"><i class='bx bx-message-rounded-dots'></i> Replies (${msg.replies.length})</h5>
                ${msg.replies.map(reply => `
                    <div class="card mb-2 ms-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>
                                    ${escapeHtml(reply.sender_name || 'Unknown')}
                                    ${reply.sender_type !== 'parent' ? `<span class="badge bg-info ms-2">${reply.sender_type}</span>` : ''}
                                </strong>
                                <small class="text-muted">${formatFullDate(reply.sent_date || reply.created_at)}</small>
                            </div>
                            <p class="mb-0" style="white-space: pre-wrap;">${escapeHtml(reply.message_body || reply.message)}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
            ` : ''}
            
            <!-- Reply Form -->
            ${isReceiver ? `
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class='bx bx-reply'></i> Reply to this message</h6>
                </div>
                <div class="card-body">
                    <form id="replyForm">
                        <div class="mb-3">
                            <textarea class="form-control" id="replyMessage" rows="4" placeholder="Type your reply..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-send'></i> Send Reply
                        </button>
                    </form>
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    content.innerHTML = html;
    
    // Add reply form handler if it exists
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendReply(msg.message_id, msg.sender_id, msg.sender_type, msg.student_id);
        });
    }
}

function sendReply(parentMessageId, recipientId, recipientType, studentId) {
    const replyText = document.getElementById('replyMessage').value.trim();
    
    if (!replyText) {
        alert('Please enter a reply message');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'send_message');
    formData.append('recipient_id', recipientId);
    formData.append('recipient_type', recipientType);
    formData.append('student_id', studentId || '');
    formData.append('subject', 'Re: Reply to your message');
    formData.append('message_body', replyText);
    formData.append('parent_message_id', parentMessageId);
    
    fetch('../assets/manageParentMessages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ Reply sent successfully!');
            location.reload();
        } else {
            alert('❌ Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Failed to send reply');
    });
}

function markAsRead(messageId) {
    const formData = new FormData();
    formData.append('action', 'mark_as_read');
    formData.append('message_id', messageId);
    
    fetch('../assets/manageParentMessages.php', {
        method: 'POST',
        body: formData
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatFullDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}
</script>

<style>
.full-width { width: 100%; }
.message-body {
    font-size: 1rem;
    line-height: 1.6;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
}
.replies-section .card {
    border-left: 3px solid #007bff;
}
</style>

<?php include('partials/_footer.php') ?>
