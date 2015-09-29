<?php

require_once("Rest.inc.php");

class API extends REST {

    public $data = "";
    private $db = NULL;
    private $mysqli = NULL;

    public function __construct() {
        parent::__construct();    // Init parent contructor
        $this->dbConnect();     // Initiate Database connection
    }

    /*
     *  Connect to Database
     */

    private function dbConnect() {
        $this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
    }

    /*
     * Dynmically call the method based on the query string
     */

    public function processApi() {
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['x'])));
        if ((int) method_exists($this, $func) > 0) {
            $this->$func();
        } else {
            $this->response('', 404);
        } // If the method not exist with in this class "Page not found".
    }

    //Employee
    private function getAllstaff() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $query = "SELECT * from teachers";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $this->response($this->json($result), 200); // send user details
        }
        $this->response('', 204); // If no records "No Content" status
    }

    private function InsertStaff() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $obj = json_decode(file_get_contents("php://input"), true);
        $teacher = $obj['teacher'];
        $column_names = array('staff_id', 'first_name', 'last_name', 'address', 'phone', 'courses', 'fee_posstion', 'doj');
        $keys = array_keys($teacher);
        $columns = '';
        $values = '';

        foreach ($column_names as $desired_key) { // Check the Department received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $teacher[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO teachers(" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        if (!empty($teacher)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

            $success = array('status' => "Success", "msg" => "Staff Created Successfully.", "data" => $teacher, $phone);
            $this->response($this->json($success), 200);
        } else {
            $this->response('', 204);
        } //"No Content" status
    }
    
     private function InsertStaffCourse() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $data  = json_decode(file_get_contents("php://input"), true);
        
        
        if (is_array($data)){
            foreach ($data as $course){
                $stfId = $course['staff_id'];
                $coursId = $course['course_id'];
                $query = "INSERT INTO staff_course (staff_id,course_id) VALUES ('".$stfId."','".$coursId."')";
                $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            
            }
        }
        if (!empty($data)) {
            $success = array('status' => "Success", "msg" => "Staff Course Type Created Successfully.", "data" => $course);
            $this->response($this->json($success), 200);
        } else {
            $this->response('', 204);
        } //"No Content" status
    }

    private function createStaffPhone() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $phoneNumber = json_decode(file_get_contents("php://input"), true);
        $column_names = array('staff_id', 'phone_type', 'phone_number');
        $keys = array_keys($phoneNumber);
        $columns = '';
        $values = '';
        //$this->response($this->json($keys), 200);
        foreach ($column_names as $desired_key) { // Check the Department received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $phoneNumber[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }

        $query = ("INSERT INTO phone (" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")");
        if (!empty($phoneNumber)) {
            foreach ($phoneNumber as $row) {
                $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            }

            $success = array('status' => "Success", "msg" => "Telephone Created Successfully.", "data" => $phoneNumber);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204);
        echo"not enterd"; //"No Content" status
    }

    private function DeleteStaff() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $staffType = json_decode(file_get_contents("php://input"), true);
        $id = (int) $staffType['Id'];

        $query = "DELETE FROM teachers WHERE staff_id = $id";
        if (!empty($staffType)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Staff Delete Successfully.", "data" => $staffType);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    }
    
    private function getStaffCourse() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $staff = json_decode(file_get_contents("php://input"), true);
        $id = (int) $staff['Id'];
        //$query = "SELECT staff_course.*,courses.*   from staff_course INNER JOIN courses ON staff_course.course_id = courses.course_id  WHERE staff_id= $id";
        $query = "SELECT  student_course.*,staff_course.* ,courses.*  from student_course INNER JOIN staff_course ON student_course.course_id = staff_course.course_id INNER JOIN courses ON courses.course_id = student_course.course_id WHERE staff_id = $id  ORDER BY staff_course.course_id ASC  ";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $success = array('status' => "Success", "msg" => "Staff Course.", "data" => $result);
            $this->response($this->json($success), 200);
            }else{
            $this->response('', 204); //"No Content" status
    
            }
            }

    private function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

}

$api = new API;
$api->processApi();
