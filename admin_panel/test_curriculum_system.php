<!DOCTYPE html>
<html>
<head>
    <title>Curriculum System Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin: 15px 0; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .info { color: #3b82f6; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; background: #667eea; color: white; border: none; border-radius: 5px; }
        button:hover { background: #5568d3; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow: auto; }
        h2 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .status { display: inline-block; padding: 5px 10px; border-radius: 5px; font-size: 12px; }
        .status.pass { background: #10b981; color: white; }
        .status.fail { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <h1>🎓 Curriculum System Test Page</h1>
    
    <div class="test-section">
        <h2>Test 1: Check Database Column</h2>
        <button onclick="testDatabaseColumn()">Check file_path Column</button>
        <div id="dbTest"></div>
    </div>
    
    <div class="test-section">
        <h2>Test 2: Check Upload Folder</h2>
        <button onclick="testUploadFolder()">Check Folder Permissions</button>
        <div id="folderTest"></div>
    </div>
    
    <div class="test-section">
        <h2>Test 3: Fetch Curriculum Data</h2>
        <button onclick="testFetchCurriculum()">Fetch All Curriculum</button>
        <div id="fetchTest"></div>
    </div>
    
    <div class="test-section">
        <h2>Test 4: Upload Test (Manual)</h2>
        <p class="info">To test file upload:</p>
        <ol>
            <li>Go to <a href="curriculum.php" target="_blank">Curriculum Management</a></li>
            <li>Click "Add Curriculum"</li>
            <li>Fill in the form</li>
            <li>Upload a test PDF file</li>
            <li>Save and check if file appears</li>
        </ol>
    </div>
    
    <div class="test-section">
        <h2>Test 5: Student Download View</h2>
        <p class="info">To test student view:</p>
        <ol>
            <li>Create curriculum with file for a specific class</li>
            <li>Login as student of that class</li>
            <li>Go to <a href="../student_panel/index.php" target="_blank">Student Dashboard</a></li>
            <li>Scroll to "My Curriculum" section</li>
            <li>Check if "Download Curriculum" button appears</li>
            <li>Click to download and verify file</li>
        </ol>
    </div>

    <script>
        function testDatabaseColumn() {
            const div = document.getElementById('dbTest');
            div.innerHTML = '<p class="info">Checking database...</p>';
            
            fetch('test_curriculum_db.php')
                .then(r => r.text())
                .then(text => {
                    console.log('DB Test:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.has_column) {
                            div.innerHTML = `
                                <span class="status pass">PASS</span>
                                <p class="success">✅ file_path column exists in curriculum_master table</p>
                                <pre>${JSON.stringify(data, null, 2)}</pre>
                            `;
                        } else {
                            div.innerHTML = `
                                <span class="status fail">FAIL</span>
                                <p class="error">❌ file_path column missing!</p>
                                <p>Run this SQL:</p>
                                <pre>ALTER TABLE curriculum_master ADD COLUMN file_path VARCHAR(500) NULL AFTER total_credits;</pre>
                            `;
                        }
                    } catch(e) {
                        div.innerHTML = `<p class="error">Error: ${e.message}</p><pre>${text}</pre>`;
                    }
                })
                .catch(err => {
                    div.innerHTML = `<p class="error">❌ Error: ${err.message}</p>`;
                });
        }
        
        function testUploadFolder() {
            const div = document.getElementById('folderTest');
            div.innerHTML = '<p class="info">Checking folder...</p>';
            
            fetch('test_curriculum_folder.php')
                .then(r => r.text())
                .then(text => {
                    console.log('Folder Test:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.exists && data.writable) {
                            div.innerHTML = `
                                <span class="status pass">PASS</span>
                                <p class="success">✅ Upload folder exists and is writable</p>
                                <p>Path: ${data.path}</p>
                            `;
                        } else {
                            div.innerHTML = `
                                <span class="status fail">FAIL</span>
                                <p class="error">❌ Folder issue:</p>
                                <p>Exists: ${data.exists ? 'Yes' : 'No'}</p>
                                <p>Writable: ${data.writable ? 'Yes' : 'No'}</p>
                            `;
                        }
                    } catch(e) {
                        div.innerHTML = `<p class="error">Error: ${e.message}</p><pre>${text}</pre>`;
                    }
                })
                .catch(err => {
                    div.innerHTML = `<p class="error">❌ Error: ${err.message}</p>`;
                });
        }
        
        function testFetchCurriculum() {
            const div = document.getElementById('fetchTest');
            div.innerHTML = '<p class="info">Fetching curriculum...</p>';
            
            fetch('../assets/fetchCurriculum.php')
                .then(r => r.text())
                .then(text => {
                    console.log('Fetch Test:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.status === 'success') {
                            const withFiles = data.data.filter(c => c.file_path).length;
                            div.innerHTML = `
                                <span class="status pass">PASS</span>
                                <p class="success">✅ Found ${data.data.length} curriculum records</p>
                                <p class="info">📎 ${withFiles} with attached files</p>
                                <pre>${JSON.stringify(data.data.slice(0, 3), null, 2)}</pre>
                            `;
                        } else {
                            div.innerHTML = `
                                <p class="error">Error: ${data.message}</p>
                            `;
                        }
                    } catch(e) {
                        div.innerHTML = `<p class="error">Parse Error: ${e.message}</p><pre>${text}</pre>`;
                    }
                })
                .catch(err => {
                    div.innerHTML = `<p class="error">❌ Error: ${err.message}</p>`;
                });
        }
        
        // Auto-run tests on load
        window.onload = function() {
            testDatabaseColumn();
            testUploadFolder();
            testFetchCurriculum();
        };
    </script>
</body>
</html>
