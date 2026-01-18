<!DOCTYPE html>
<html>
<head>
    <title>Curriculum Add Button Debug</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; }
        pre { background: white; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <h1>🐛 Curriculum Add Button Debug</h1>
    
    <div class="test">
        <h3>Test 1: Check if JavaScript loads</h3>
        <button onclick="testJS()">Test JavaScript</button>
        <div id="jsTest"></div>
    </div>
    
    <div class="test">
        <h3>Test 2: Check Save Button Exists</h3>
        <button onclick="testButton()">Check Button</button>
        <div id="btnTest"></div>
    </div>
    
    <div class="test">
        <h3>Test 3: Test Form Submission</h3>
        <form id="testForm">
            <input type="text" name="curriculum_name" value="Test Curriculum" required><br><br>
            <select name="grade_level" required>
                <option value="10">Class 10</option>
            </select><br><br>
            <input type="text" name="academic_year" value="2026" required><br><br>
            <input type="text" name="department" value="Science"><br><br>
            <select name="subject_code[]" required>
                <option value="test123">Test Subject</option>
            </select><br><br>
            <input type="number" name="subject_name[]" value="4"><br><br>
            <input type="number" name="hours_per_week[]" value="2"><br><br>
            <button type="button" onclick="testSubmit()">Test Submit</button>
        </form>
        <div id="submitTest"></div>
    </div>
    
    <div class="test">
        <h3>Instructions</h3>
        <ol>
            <li>First, run all tests above</li>
            <li>Then open: <a href="curriculum.php" target="_blank">Curriculum Page</a></li>
            <li>Open Browser Console (Press F12)</li>
            <li>Click "Add Curriculum" button</li>
            <li>Fill the form</li>
            <li>Click "Save Curriculum"</li>
            <li>Check console for any errors</li>
        </ol>
    </div>

    <script>
        function testJS() {
            document.getElementById('jsTest').innerHTML = '<p class="success">✅ JavaScript is working!</p>';
        }
        
        function testButton() {
            const div = document.getElementById('btnTest');
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'curriculum.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const hasButton = xhr.responseText.includes('saveCurriculumBtn');
                    if (hasButton) {
                        div.innerHTML = '<p class="success">✅ Save button exists in page</p>';
                    } else {
                        div.innerHTML = '<p class="error">❌ Save button NOT found</p>';
                    }
                } else {
                    div.innerHTML = '<p class="error">❌ Failed to load page</p>';
                }
            };
            xhr.send();
        }
        
        function testSubmit() {
            const div = document.getElementById('submitTest');
            div.innerHTML = '<p>Testing submission...</p>';
            
            const form = document.getElementById('testForm');
            const formData = new FormData(form);
            formData.append('action', 'add_curriculum');
            
            console.log('Form data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            fetch('../assets/addCurriculum.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        div.innerHTML = '<p class="success">✅ Backend working! ' + data.message + '</p>';
                    } else {
                        div.innerHTML = '<p class="error">❌ Error: ' + data.message + '</p>';
                    }
                } catch (e) {
                    div.innerHTML = '<p class="error">❌ Parse error</p><pre>' + text + '</pre>';
                }
            })
            .catch(error => {
                div.innerHTML = '<p class="error">❌ Fetch error: ' + error.message + '</p>';
            });
        }
    </script>
</body>
</html>
