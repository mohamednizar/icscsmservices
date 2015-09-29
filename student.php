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
    private function getAllstudent() {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        
        $query = "SELECT * from students";
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
    
    
   

    
    

    private function InsertStudent() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $data= json_decode(file_get_contents("php://input"), true);
        $student = $data['student'];
        $course = $data['course'];
        $admission =$data['admission'];
        $column_names = array('std_id', 'first_name', 'last_name','sex', 'address', 'phone','school','medium','grade', 'dob');
        $keys = array_keys($student);
        $columns = '';
        $values = '';
        //$this->response($this->json($deviceTypeId), 200);
        foreach ($column_names as $desired_key) { // Check the Department received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $student[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO students(" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        
        if (!empty($student)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "student Type Created Successfully.", "data" => $student);
            $stdId =  $this->mysqli->insert_id;
            if (is_array($course)){
            foreach ($course as $crs){
                $coursId = $crs['course_id'];
                $query2 = "INSERT INTO student_course (std_id,course_id) VALUES ('".$stdId."','".$coursId."')";
                $r = $this->mysqli->query($query2) or die($this->mysqli->error . __LINE__);
            }
             $add = $admission;
             $query3 = "INSERT INTO student_admission (std_id,admission_fee) VALUES ('".$stdId."','".$add ."')";
            $r = $this->mysqli->query($query3) or die($this->mysqli->error . __LINE__); 
            
        }
            $this->response($this->json($success), 200);
           
        } else {
            $this->response('', 204);
        } //"No Content" status
    }
    //check outstanding buy runthis query.
    /// SELECT  DATEDIFF ( now() , student_course.started_date ) AS days  ,student_course.std_crs_id,student_course.std_id
//FROM student_course
 //WHERE days/30> 1
        private function InsertStudentCourse() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $data  = json_decode(file_get_contents("php://input"), true);
        
        
        if (is_array($data)){
            foreach ($data as $course){
                $stdId = $course['std_id'];
                $coursId = $course['course_id'];
                $query = "INSERT INTO student_course (std_id,course_id,started_date,fee) VALUES ('".$stdId."','".$coursId."',now(),1)";
                $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            }
            
            
        }
        if (!empty($data)) {
            $success = array('status' => "Success", "msg" => "student Course Type Created Successfully.", "data" => $course);
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
        $column_names = array('std_id', 'first_name', 'last_name', 'address', 'school','doa','dob', 'courses');
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
    
    private function ConformStudent() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $student = json_decode(file_get_contents("php://input"), true);
        $id = (int) $student['Id'];
        $query = "UPDATE  students SET status = '1' WHERE std_id = $id";
        if (!empty($student)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array('status' => "Success", "msg" => "Student Delete Successfully.", "data" => $student);
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    }
    
    private function getStudentCourse() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $student = json_decode(file_get_contents("php://input"), true);
        $id = (int) $student['Id'];
        $query = "SELECT student_course.*,courses.*  from student_course INNER JOIN courses ON student_course.course_id=courses.course_id WHERE std_id= $id";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $success = array('status' => "Success", "msg" => "Student Course.", "data" => $result);
            $this->response($this->json($success), 200);
            }else{
            $this->response('', 204); //"No Content" status
    
            }
            }
    
            private function CollectCoursefee() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $data  = json_decode(file_get_contents("php://input"), true);
        if (is_array($data)){
            foreach ($data as $course){
                $std_crs_Id = $course['std_crs_id'];
                $query = "UPDATE  student_course SET fee = fee + 1  ,lastUpdate = now()  WHERE std_crs_id = $std_crs_Id ";
                //$query.= "UPDATE  student_course SET fee = 1 WHERE std_crs_id = $std_crs_Id and fee != NULL";
                $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            }
            foreach ($data as $course){
                $stdId = $course['std_id'];
                $course_fee =$course['course_fee'];
                $query2 = "INSERT INTO cash_floow (cash_in_flow,std_id) VALUES($course_fee,$stdId)";
                $r = $this->mysqli->query($query2) or die($this->mysqli->error . __LINE__);
            }
        }
        
        if (!empty($data)) {
            
            $success = array('status' => "Success", "msg" => "student Course Type Created Successfully.", "data" => $course);
            $this->response($this->json($success), 200);
        } else {
            $this->response('', 204);
        } //"No Content" status
    }
  
    
    private function SearchStudent() {

        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $student = json_decode(file_get_contents("php://input"), true);
        $id = (int) $student['Id'];
        $query = "SELECT students.* FROM  students WHERE std_id= $id";
        
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $success = array('status' => "Success", "msg" => "Student Details.", "data" => $result);
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
