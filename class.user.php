<?php
    class User {
        public $authorized = false;
        public $uid;
        public $username;
    }


    public function __construct() {
        $this->db = new PDO($dsn, $db_user, $db_pass);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        if (isset($_SESSION['uid'])) {
                $this->authorizsed = true;
                $this->uid = $_SESSION['uid'];
                $this->username = $_SESSION['username'];
            }
        } else if (isset($_POST['username']) && isset($_POST['password'])) {
            $user = $_POST['username'];
            $pass = $_POST['password'];
            $this->login($user, $pass);
        }
    }


    public function login($user, $pass) {
        $st = $this->db->prepare('SELECT `uid`, `username`, `password`
                FROM users
                WHERE username = :u');
        $st->execute(array(':u' => $user));
        $row = $st->fetch();

        if ($row && $row->password == sha1($pass)) {
            $this->authorized = true;

            $this->uid = $row->uid;
            $_SESSION['uid'] = $this->uid;
            
            $this->username = $row->username;
            $_SESSION['username'] = $this->username;

            return true;
        } else {
            return false;
        }
    }
?>