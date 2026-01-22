<?php
error_reporting(0);
ini_set('display_errors', 0);

include("config.php");

// Handle GET request for simple student list (used by messages page)
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $query = "SELECT id, fname, lname, class, section FROM students ORDER BY fname, lname ASC";
    $result = @mysqli_query($conn, $query);
    
    $students = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($students);
    mysqli_close($conn);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if it's form data (for grades page) or JSON data (for student management page)
    if (isset($_POST['class']) && !isset($_POST['name'])) {
        // Simple class-based query for grades page
        $class = mysqli_real_escape_string($conn, $_POST['class']);
        
        $query = "SELECT id, fname, lname, class, section, roll FROM students WHERE class = ? ORDER BY fname, lname ASC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $class);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $students = array();
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $students[] = $row;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($students);
        mysqli_close($conn);
        exit;
    }

    // Original JSON-based request handling for student management
    $postData = file_get_contents("php://input");
    $data = json_decode($postData, true);

    $name = $data['name'];
    $class = $data['as'];
    $section = $data['a'];

    $query = "";
    $resultOutput = array();

    if ($name == "") {
        $query = "SELECT * FROM students  WHERE class=? AND section=?  ORDER BY fname, lname ASC;";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $class, $section);
    } else {
        if (stripos($name, " ") !== false) {
            $array = explode(' ', $name, 2);

            $query = "SELECT *
            FROM (
                SELECT *
                FROM students
                WHERE class=? AND section=?
            ) AS temp_table
            WHERE (fname LIKE ? AND lname LIKE ?)
                OR (fname LIKE ? AND lname LIKE ?)
            ORDER BY fname, lname ASC;";

            $stmt = mysqli_prepare($conn, $query);
            $param1 = $array[0] . '%';
            $param2 = '%' . $array[1] . "%";
            $param3 = $array[1] . '%';
            $param4 = '%' . $array[0] . "%";
            mysqli_stmt_bind_param($stmt, "ssssss", $class, $section, $param1, $param2, $param3, $param4);
        } else {
            $query = "SELECT *
            FROM students
            WHERE class=? AND section=?
                AND (fname LIKE ? OR lname LIKE ?)
            ORDER BY fname, lname ASC;";

            $stmt = mysqli_prepare($conn, $query);
            $param = '%' . $name . '%';
            mysqli_stmt_bind_param($stmt, "ssss", $class, $section, $param, $param);
        }
    }

    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $count = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                $fname = $row["fname"];
                $lname = $row["lname"];
                $tid = $row['id'];
                $image = '../studentUploads/'.$row['image'];
                $image = file_exists($image) ? $image : "../images/user.png";

                $resultOutput[$count - 1] = "<tr>
               <td>&nbsp;&nbsp;".$count.".&nbsp;&nbsp;</td>
                <td>".$tid."</td>
                <td class='user'>
                    <img src='".$image."'>
                    <p>". ucfirst(strtolower($fname)) ." ". strtolower($lname)."</p>
                </td>
                <td class='flex-center'>
                    <div class='edit-delete'>
                        <a onclick='editStudent(`".$tid."`)'   class='edit' >
                            <i class='bx bxs-edit'></i>
                            <span>&nbsp;Edit</span>
                        </a>
                        <a onclick='deleteStudentWithId(`".$tid."`)'  class='delete'>
                            &nbsp;&nbsp;<i class='bx bxs-trash'></i>
                            <span>&nbsp;Delete</span>
                            &nbsp;&nbsp;
                        </a>
                    </div>
                </td>
            </tr>";

                $count = $count + 1;
            }
        } else {
            $resultOutput[0] = "No_Record";
        }

        mysqli_stmt_close($stmt);
    } else {
        $resultOutput[0] = "Error in preparing statement";
    }
} else {
    $resultOutput[0] = "Error";
}

$jsonData = json_encode($resultOutput);
echo $jsonData;

?>
