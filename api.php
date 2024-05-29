<?php
    // Set the content type to JSON for the HTTP response
    header("Content-Type: application/json");

    // Database connection variables
    $host = 'localhost';
    $db = 'iskat';
    $user = 'root';
    $pass = ''; // Ensure this is correct
    $charset = 'utf8mb4';

    // Data Source Name (DSN) for connecting to the MySQL database
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        // Set error mode to exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Fetch mode to associative arrays
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Disable emulation of prepared statements
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        // Attempt to create a new PDO instance for database connection
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // If connection fails, return a JSON error message and exit
        echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
        exit;
    }

    // Check if the request method is GET and the 'action' parameter is set
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
        if ($_GET['action'] === 'getUsers') {
            // Fetch user details along with department name and total salary
            $stmt = $pdo->query("
                SELECT 
                    a.userid, a.username, a.pass, a.email, 
                    d.dname, s.totalsalary
                FROM 
                    accounts a
                JOIN 
                    department d ON a.dept_no = d.dnumber
                JOIN 
                    deptsal s ON a.sal_no = s.dnumber
            ");
            $users = $stmt->fetchAll();
            // Return the user details as JSON
            echo json_encode($users);
        } elseif ($_GET['action'] === 'getDepartments') {
            // Fetch department numbers and names
            $stmt = $pdo->query("SELECT dnumber, dname FROM department");
            $departments = $stmt->fetchAll();
            // Return the department details as JSON
            echo json_encode($departments);
        } elseif ($_GET['action'] === 'getTotalSalary' && isset($_GET['dnumber'])) {
            // Fetch the total salary for a specific department
            $dnumber = intval($_GET['dnumber']);
            $stmt = $pdo->prepare("SELECT totalsalary FROM deptsal WHERE dnumber = ?");
            $stmt->execute([$dnumber]);
            $totalsalary = $stmt->fetch();
            // Return the total salary as JSON
            echo json_encode($totalsalary);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle POST requests to add a new user
        $input = json_decode(file_get_contents('php://input'), true);
        // Insert a new user into the accounts table
        $sql = "INSERT INTO accounts (username, pass, email, dept_no, sal_no) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['username'], $input['pass'], $input['email'], $input['dept_no'], $input['sal_no']]);
        // Return a success message as JSON
        echo json_encode(['message' => 'User added successfully']);
    }
?>
