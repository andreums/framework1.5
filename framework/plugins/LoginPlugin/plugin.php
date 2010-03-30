<?php
class LoginPlugin extends BasePlugin {

    public function logout() {
        try {
            @session_destroy();
        }
        catch (Exception $ex) {
            @session_start();
            @session_destroy();
        }

        if (!headers_sent()) {
            $location = "Location: ".BASE_URL;
            header($location);
            return;
        }
    }

    public function login() {

        $status = 0;
        $message = "";

        $filter = Filter::getInstance();
        $username = Request::getPostParam("username");
        $password = Request::getPostParam("password");

        if ($filter->isString($username)) {
            if ( ($username==null) || (strlen($username)<6) ) {
                $status = 403;
                $message =  "<strong>Error:</strong> Combinación incorrecta de usuario y contraseña";
            }
        }

        if ($filter->isString($password)) {
            if ( ($password==null) || (strlen($password)<6) ) {
                $status = 403;
                $message =  "<strong>Error:</strong> Combinación incorrecta de usuario y contraseña";
            }
        }

        if ( $status!=403 ) {
            $db = DataBase::getInstance();
            $passSHA1 = sha1($password);
            $query = "SELECT * FROM User WHERE username='{$username}' AND password='{$passSHA1}' ";
            $db->query($query);
            $rows = $db->numRows();
            if ($rows==0) {
                $status = 403;
                $message =  "<strong>Error:</strong> Combinación incorrecta de usuario y contraseña";
            }
            else {
                $user = $db->fetchObject();
                if ( intval($user->isBlocked)==1 ) {
                    $status = 402;
                    $message =  "<strong>Error:</strong> Usuario bloqueado, contacte con su administrador";
                }
                else {
                    $status = 200;
                    try {
                        @session_destroy();
                        @session_start();
                        Session::set("status",true,"login");
                        Session::set("username",$user->username,"login");
                        Session::set("role",$user->role,"login");
                    }
                    catch (Exception $ex) {
                        $status = 500;
                        $message =  "<strong>Error:</strong> Error del servidor, inténtelo de nuevo más tarde.";
                    }
                }
            }
        }



        header("Content-Type: text/xml",true);
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        print "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n ";
        print "\t<reply>\n";
        print "\t\t<value>\n";
        print "\t\t\t{$status}\n";
        print "\t\t</value>\n";
        print "\t\t<message>\n";
        print "\t\t\t{$message}\n";
        print "\t\t</message>\n";
        print "\t</reply>\n";
        return;
    }


    public function restLogin($username,$password) {

        $status = 0;

        $filter = Filter::getInstance();

        if ($filter->isString($username)) {
            if ( ($username==null) || (strlen($username)<6) ) {
                $status = 403;
            }
        }

        if ($filter->isString($password)) {
            if ( ($password==null) || (strlen($password)<6) ) {
                $status = 403;
            }
        }

        if ( $status!=403 ) {
            $db = DataBase::getInstance();
            $passSHA1 = sha1($password);
            $query = "SELECT * FROM User WHERE username='{$username}' AND password='{$passSHA1}' ";
            $db->query($query);
            $rows = $db->numRows();
            if ($rows==0) {
                $status = 403;
            }
            else {
                $user = $db->fetchObject();
                if ( intval($user->isBlocked)==1 ) {
                    $status = 402;
                }
                else {
                    $status = 200;
                    try {
                        @session_destroy();
                        @session_start();
                        Session::set("authentication",true,"REST");
                        Session::set("username",$user->username,"REST");
                        Session::set("role",$user->role,"REST");
                    }
                    catch (Exception $ex) {
                        $status = 500;
                    }
                }
            }
            return $status;
        }



        header("Content-Type: text/xml",true);
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        print "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n ";
        print "\t<reply>\n";
        print "\t\t<value>\n";
        print "\t\t\t{$status}\n";
        print "\t\t</value>\n";
        print "\t\t<message>\n";
        print "\t\t\t{$message}\n";
        print "\t\t</message>\n";
        print "\t</reply>\n";
        return;
    }

    public function SOAPLogin($username,$password) {

        $status = 0;

        $filter = Filter::getInstance();

        if ($filter->isString($username)) {
            if ( ($username==null) || (strlen($username)<6) ) {
                $status = 403;
            }
        }

        if ($filter->isString($password)) {
            if ( ($password==null) || (strlen($password)<6) ) {
                $status = 403;
            }
        }

        if ( $status!=403 ) {
            $db = DataBase::getInstance();
            $passSHA1 = sha1($password);
            $query = "SELECT * FROM User WHERE username='{$username}' AND password='{$passSHA1}' ";
            $db->query($query);
            $rows = $db->numRows();
            if ($rows==0) {
                $status = 403;
            }
            else {
                $user = $db->fetchObject();
                if ( intval($user->isBlocked)==1 ) {
                    $status = 402;
                }
                else {
                    $status = 200;
                    try {
                        @session_destroy();
                        @session_start();

                        $username = $user->username;
                        $role = $user->role;

                        Session::set("authentication",true,"SOAP");
                        Session::set("username",$username,"SOAP");
                        Session::set("role",$role,"SOAP");
                        Session::set("foo","BAR","SOAP");
                    }
                    catch (Exception $ex) {
                        $status = 500;
                    }
                }
            }
            return $status;
        }
        return $status;
    }

    public function getLoginForm() {
		$this->renderView("loginForm");
    }


}

?>