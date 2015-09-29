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
    private function getAllcourses() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $query = "SELECT * FROM courses inner join grade on courses.grade = grade.id  inner join mediam on courses.mediam = mediam.medium_id";
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

    private function getAllcoursesbyGrade() {

        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $course = json_decode(file_get_contents("php://input"), true);
        $column_names = array('grade',  'mediam');
        $keys = array_keys($course);
        $columns = '';
        $values = '';

        foreach ($column_names as $desired_key) { // Check the Department received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $course[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "SELECT * FROM courses inner join grade on courses.grade = grade.id inner join mediam on courses.mediam = mediam.medium_id where courses.grade = grade and courses.mediam = mediam ";
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

    private function InsertCourse() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $course = json_decode(file_get_contents("php://input"), true);
        $column_names = array('course_id', 'grade', 'subject', 'mediam', 'teacher_id', 'course_fee');
        $keys = array_keys($course);
        $columns = '';
        $values = '';
        //$this->response($this->json($deviceTypeId), 200);
        foreach ($column_names as $desired_key) { // Check the Department received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $course[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO courses(" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        if (!empty($course)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "student Type Created Successfully.", "data" => $course);
            $this->response($this->json($success), 200);
        } else {
            $this->response('', 204);
        } //"No Content" status
    }

    private function changeStudentStatus() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $studentType = json_decode(file_get_contents("php://input"), true);
        $column_names = array('std_id', 'first_name', 'last_name', 'address', 'school', 'dob', 'courses');
        $keys = array_keys($studentType);
        $columns = '';
        $values = '';
        //$this->response($this->json($deviceTypeId), 200);
        foreach ($column_names as $desired_key) { // Check the Department received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $studentType[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "UPDATE students SET(" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        if (!empty($studentType)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "student Type Created Successfully.", "data" => $studentType);
            $this->response($this->json($success), 200);
        } else {
            $this->response('', 204);
        } //"No Content" status
    }

    private function DeleteStudent() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $student = json_decode(file_get_contents("php://input"), true);
        $id = (int) $student['Id'];

        $query = "DELETE FROM students WHERE std_id = $id";
        if (!empty($student)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Student Delete Successfully.", "data" => $student);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    }

    private function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

}

$api = new API;
$api->processApi();
