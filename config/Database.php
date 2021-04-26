<?php
class Database
{
    // Database Details
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "ecommerce";
    // conn variable
    protected $conn = null;

    // Constructor Function
    public function __construct()
    {
        // $conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname) or die("Connection failed: " . mysqli_connect_error());

        // /* check connection */
        // if (mysqli_connect_errno()) {
        //     printf("Connect failed: %s\n", mysqli_connect_error());
        //     exit();
        // } else {
        //     $this->conn = $conn;
        // }
        // //$this->conn->autocommit(false);
        // return $this->conn;
    }

    public function getConnection()
    {
        $conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname) or die("Connection failed: " . mysqli_connect_error());

        /* check connection */
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        } else {
            $this->conn = $conn;
        }
        //$this->conn->autocommit(false);
        return $this->conn;
    }

    // Sanitize Inputs
    public function test_input($data)
    {
        $data = strip_tags($data);
        $data = htmlspecialchars($data);
        $data = stripslashes($data);
        $data = trim($data);
        return $data;
    }

    // JSON Format Converter Function
    public function message($content, $status)
    {
        return json_encode(['message' => $content, 'error' => $status]);
    }
}
