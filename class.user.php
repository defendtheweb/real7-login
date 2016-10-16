<?php
    class User {
        public $authorized = false;
        public $uid;
        public $username;


        public function __construct($dsn, $db_user, $db_pass) {
            $this->db = new PDO($dsn, $db_user, $db_pass);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

            if (isset($_SESSION['uid'])) {
                $this->authorized = true;
                $this->uid = $_SESSION['uid'];
                $this->username = $_SESSION['username'];
            } else if (isset($_POST['reset'])) {
                $user = $_POST['reset'];
                $this->reset($user);
            } else if (isset($_POST['username']) && isset($_POST['password'])) {
                $this->login($_POST['username'];, $_POST['password'];);
            }
        }


        private function login($user, $pass) {
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


        private function reset($user) {
            $st = $this->db->prepare('SELECT `uid`, `username`, `email`
                    FROM users
                    WHERE username = :u');
            $st->execute(array(':u' => $user));
            $row = $st->fetch();

            if ($row) {
                $token = $this->generateToken();

                $st = $this->db->prepare('UPDATE users SET `reset` = :reset, password = 0 WHERE uid = :uid LIMIT 1');
                $status = $st->execute(array(':uid' => $row->uid, ':reset' => $token));

                $body = "We received a request for your account details.<br/><br/>Username: {$row->username}<br/>To reset your password, click on this link: <a href='http://www.example.org/?reset={$token}'>http://www.example.org/?reset={$token}</a>";

                $to = $row->email;
                $subject = 'Password request';
                $from = 'no-reply@example.org';
                 
                // To send HTML mail, the Content-type header must be set
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                 
                // Create email headers
                $headers .= 'From: '.$from."\r\n".
                            'Reply-To: '.$from."\r\n";

                mail($to, $subject, $body, $headers);
            }
        }

        private function generateToken() {
            $token = md5(openssl_random_pseudo_bytes(32));
            return $token;
        }

    }
?>
