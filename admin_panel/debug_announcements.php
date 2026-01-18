<!DOCTYPE html>
<html>
<head>
    <title>Announcement System Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        pre { background: #fff; padding: 10px; overflow: auto; }
        h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Announcement System Debug Tool</h1>
    
    <div class="section">
        <h2>Step 1: Check Database Tables</h2>
        <button onclick="checkTables()">Check Tables</button>
        <div id="tableResult"></div>
    </div>
    
    <div class="section">
        <h2>Step 2: Test Create Announcement (Admin)</h2>
        <p>This will create a test announcement</p>
        <button onclick="testCreateAnnouncement()">Create Test Announcement</button>
        <div id="createResult"></div>
    </div>
    
    <div class="section">
        <h2>Step 3: Test Fetch Announcements (Student)</h2>
        <p>This will fetch announcements for current user</p>
        <button onclick="testFetchAnnouncements()">Fetch Announcements</button>
        <div id="fetchResult"></div>
    </div>
    
    <div class="section">
        <h2>Step 4: Test Get All Announcements (Admin)</h2>
        <button onclick="testGetAllAnnouncements()">Get All Announcements</button>
        <div id="getAllResult"></div>
    </div>

    <script>
        function checkTables() {
            const resultDiv = document.getElementById('tableResult');
            resultDiv.innerHTML = '<p class="info">Checking...</p>';
            
            fetch('test_announcements_db.php')
                .then(response => response.text())
                .then(text => {
                    console.log('Tables check:', text);
                    resultDiv.innerHTML = '<pre>' + text + '</pre>';
                })
                .catch(error => {
                    resultDiv.innerHTML = '<p class="error">Error: ' + error.message + '</p>';
                });
        }
        
        function testCreateAnnouncement() {
            const resultDiv = document.getElementById('createResult');
            resultDiv.innerHTML = '<p class="info">Creating test announcement...</p>';
            
            const formData = new FormData();
            formData.append('action', 'create_announcement');
            formData.append('title', 'Debug Test Announcement');
            formData.append('content', 'This is a test announcement created by the debug tool at ' + new Date().toLocaleString());
            formData.append('announcement_type', 'general');
            formData.append('priority', 'normal');
            formData.append('target_audience', 'students');
            
            // Set dates
            const now = new Date();
            const future = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000); // 7 days from now
            formData.append('display_from', now.toISOString().slice(0, 19).replace('T', ' '));
            formData.append('display_until', future.toISOString().slice(0, 19).replace('T', ' '));
            
            formData.append('is_pinned', '0');
            formData.append('allow_comments', '1');
            formData.append('status', 'published');
            formData.append('published_by', '<?php echo $_SESSION["uid"] ?? "1001"; ?>');
            
            fetch('../assets/manageAnnouncements.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Create response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        resultDiv.innerHTML = '<p class="success">✅ Success!</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    } else {
                        resultDiv.innerHTML = '<p class="error">❌ Error!</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    }
                } catch (e) {
                    resultDiv.innerHTML = '<p class="error">❌ Parse Error!</p><p>Response is not valid JSON:</p><pre>' + text + '</pre>';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                resultDiv.innerHTML = '<p class="error">❌ Fetch Error: ' + error.message + '</p>';
            });
        }
        
        function testFetchAnnouncements() {
            const resultDiv = document.getElementById('fetchResult');
            resultDiv.innerHTML = '<p class="info">Fetching announcements...</p>';
            
            fetch('../assets/fetchStudentAnnouncements.php')
                .then(response => response.text())
                .then(text => {
                    console.log('Fetch response:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.status === 'success') {
                            resultDiv.innerHTML = '<p class="success">✅ Success! Found ' + data.count + ' announcements</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                        } else {
                            resultDiv.innerHTML = '<p class="error">❌ Error!</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                        }
                    } catch (e) {
                        resultDiv.innerHTML = '<p class="error">❌ Parse Error!</p><p>Response is not valid JSON:</p><pre>' + text + '</pre>';
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    resultDiv.innerHTML = '<p class="error">❌ Fetch Error: ' + error.message + '</p>';
                });
        }
        
        function testGetAllAnnouncements() {
            const resultDiv = document.getElementById('getAllResult');
            resultDiv.innerHTML = '<p class="info">Getting all announcements...</p>';
            
            const formData = new FormData();
            formData.append('action', 'get_announcements');
            
            fetch('../assets/manageAnnouncements.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Get all response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        resultDiv.innerHTML = '<p class="success">✅ Success! Found ' + data.data.length + ' announcements</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    } else {
                        resultDiv.innerHTML = '<p class="error">❌ Error!</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    }
                } catch (e) {
                    resultDiv.innerHTML = '<p class="error">❌ Parse Error!</p><p>Response is not valid JSON:</p><pre>' + text + '</pre>';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                resultDiv.innerHTML = '<p class="error">❌ Fetch Error: ' + error.message + '</p>';
            });
        }
    </script>
</body>
</html>

<?php
session_start();
// Show session info for debugging
echo "<div class='section'>";
echo "<h2>Session Information</h2>";
echo "<pre>";
echo "Session Started: " . (session_status() === PHP_SESSION_ACTIVE ? "Yes" : "No") . "\n";
echo "User ID: " . ($_SESSION['uid'] ?? 'Not set') . "\n";
echo "User Role: " . ($_SESSION['role'] ?? 'Not set') . "\n";
echo "</pre>";
echo "</div>";
?>
