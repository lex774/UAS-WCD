<!DOCTYPE html>
<html>
<head>
    <title>Test Button</title>
    <style>
        .test-btn {
            background: #6366f1;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }
    </style>
</head>
<body>
    <h1>Test Button Click</h1>
    
    <button class="test-btn" onclick="testClick()">Test Click</button>
    <button class="test-btn" onclick="testAjax()">Test AJAX</button>
    
    <div id="result"></div>
    
    <script>
        function testClick() {
            console.log('Button clicked!');
            document.getElementById('result').innerHTML = '<p>Button clicked at: ' + new Date().toLocaleString() + '</p>';
        }
        
        function testAjax() {
            console.log('Testing AJAX...');
            fetch('test-ajax.php?test=1')
                .then(res => res.json())
                .then(data => {
                    console.log('AJAX result:', data);
                    document.getElementById('result').innerHTML = '<p>AJAX success: ' + JSON.stringify(data) + '</p>';
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                    document.getElementById('result').innerHTML = '<p>AJAX error: ' + error.message + '</p>';
                });
        }
    </script>
</body>
</html> 