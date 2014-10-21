<?php
namespace CameraLife;

/**
 * Implements OpenID login
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2011 William Entriken
 * @access public
 */
class OpenIDSecurity extends SecurityModule
{
    public $curuser;
    public $authcookie;

    public function __construct()
    {
        $this->preferences[] = array('name' => 'auth_photo_rename', 'type' => 'number', 'default' => '0');
        $this->preferences[] = array('name' => 'auth_photo_delete', 'type' => 'number', 'default' => '0');
        $this->preferences[] = array('name' => 'auth_photo_modify', 'type' => 'number', 'default' => '3');
        $this->preferences[] = array('name' => 'auth_admin_albums', 'type' => 'number', 'default' => '4');
        $this->preferences[] = array('name' => 'auth_photo_upload', 'type' => 'number', 'default' => '1');
        $this->preferences[] = array('name' => 'auth_admin_file', 'type' => 'number', 'default' => '4');
        $this->preferences[] = array('name' => 'auth_admin_theme', 'type' => 'number', 'default' => '4');
        $this->preferences[] = array('name' => 'auth_admin_customize', 'type' => 'number', 'default' => '5');
        $this->preferences[] = array('name' => 'auth_cookie', 'type' => 'string', 'default' => 'cameralifeauth');
    }

    public function getCurUser()
    {
        global $_COOKIE, $cameralife;
        $cookiename = $this->getPref('auth_cookie');

        if (isset($_COOKIE[$cookiename])) {
            $authcookie = $_COOKIE[$cookiename];
            $result = $cameralife->database->Select('users', '*', "cookie='$authcookie'");
            $this->curuser = $result->fetchAssoc()
            or $this->curuser = array('auth' => '0', 'username' => '');
        } else {
            $this->curuser = array('auth' => '0', 'username' => '');
        }
    }

    /**
 * Logs a user in after they have been authenticated with ID and EMAIL
     */
    public function Login($id, $post)
    {
        global $cameralife;
        $email = $post['email'];
        $id = mysql_real_escape_string($id);
        $email = mysql_real_escape_string($email);

        $cookie = mt_rand(0, 1000000000000);
        setcookie($this->getPref('auth_cookie'), $cookie, time() + 30000000, '/');
        $_COOKIE[$this->getPref('auth_cookie')] = $cookie;

        $result = $cameralife->database->Select('users', '*', "password='$id'");
        if ($user = $result->fetchAssoc()) {
            $values['last_online'] = date('Y-m-d');
            $values['last_ip'] = $_SERVER["REMOTE_ADDR"];
            $values['cookie'] = $cookie;
            $cameralife->database->Update('users', $values, "password='$id'");
            return true;
        } else {
            $result = $cameralife->database->Select('users', '*', "username='$email'");

            if ($user = $result->fetchAssoc()) {
                die ('Email address already in use');
            }

            if ($email == '') {
                die ('Email address empty');
            }

            $values['username'] = $email;
            $values['password'] = $id;
            $values['auth'] = 1;
            $values['cookie'] = $cookie;
            $values['last_online'] = date('Y-m-d');
            $values['last_ip'] = $HTTP_SERVER_VARS["REMOTE_ADDR"];
            $values['email'] = $email;
            $cameralife->database->Insert('users', $values);
            setcookie($this->getPref('auth_cookie'), $cookie, time() + 30000000, '/');
            return true;
        }
    }

    public function loginURL()
    {
        global $cameralife;
        return $cameralife->baseURL . "/modules/security/openid/login.php";
    }

    /**
     * @param $username
     * @param $post
     * @internal param $password
     * @internal param string $email
     * @return bool|string TRUE if successful, or error message
     */
    public function register($username, $post)
    {
        global $cameralife;
        $password = $post['password'];
        $email = $post['email'];

        $username = strtolower($username);
        $username = preg_replace('/[^0-9a-z_]/', '', $username);
        $result = $cameralife->database->Select('users', '*', "username='$username'");
        $password = crypt($password, $username);
        $cookie = rand(0, 999999);

        if ($user = $result->fetchAssoc()) {
            return 'Username already taken';
        }

        if ($username == '') {
            return 'Username empty';
        }

        $values['username'] = $username;
        $values['password'] = $password;
        $values['auth'] = 1;
        $values['cookie'] = $cookie;
        $values['last_online'] = date('Y-m-d');
        $values['last_ip'] = $HTTP_SERVER_VARS["REMOTE_ADDR"];
        $values['email'] = $email;
        $cameralife->database->Insert('users', $values);
        setcookie($this->getPref('auth_cookie'), $cookie, time() + 30000000, '/');
        return true;
    }

    /* Logs the user out. Returns true  or and exit url if you really want to */
    /**Logs the user out
     * @return true | exit URL if you really want to quit the cameralife site
     */
    public function logout()
    {
        setcookie($this->getPref('auth_cookie'), '', time(), '/');
        return true;
    }

    /**
     * Provides a URL, that the user can go to, to administer users and permissions
     *  Or
     * @return FALSE if the user is not allowed to administer.
     */
    public function administerURL()
    {
        global $cameralife;
        if ($this->authorize('admin_file') 
            || $this->authorize('admin_theme') 
            || $this->authorize('admin_customize')
        ) {
            return $cameralife->baseURL . "/modules/security/openid/administer.php";
        }
        return false;
    }

    /**
     * @return mixed the current user's name or '' if not logged in
     */
    public function getName()
    {
        if (!isset($this->curuser)) {
            $this->getCurUser();
        }
        return $this->curuser['username'];
    }

    /**
     * Determines if current user is authorized to perform AUTHNAME
     *
     * AUTHNAME can be:
     *  <li>photo_rename</li>
     *  <li>photo_delete</li>
     *  <li>photo_modify</li>
     *  <li>admin_albums</li>
     *  <li>photo_upload</li>
     *  <li>admin_file</li>
     *  <li>admin_theme </li>
     *  <li>admin_customize</li>
     *
     * @param  $authName
     * @param  bool     $required
     * @return bool
     */
    public function authorize($authName, $required = false)
    {
        global $cameralife;

        $authName = "auth_$authName";
        if (!isset ($this->curuser)) {
            $this->getCurUser();
        }

        if (null == ($this->getPref($authName))) {
            $cameralife->error("The privilege $authName does not exist.");
        }

        if ($this->curuser['auth'] < $this->getPref($authName) && $required) {
            $error = "<span style='font-size:x-large'>You are not authorized to view this page</span>\n";
            $error .= "<p>To view this page, you must have the <strong>$authName</strong> privilege.<br>\n";
            $error .= "This is given to all users of authorization level at least <strong>" . $this->getPref(
                $authName
            ) . "</strong>.</p>\n";
            if ($this->curuser['username']) {
                $error .= "<p>You are logged in as <strong>" . $this->curuser['username'] . "</strong> with authorization level <strong>" . $this->curuser['auth'] . "</strong>.</p>\n";
                $error .= "<p>Either ask an administrator to increase your authorization level, or stop trying to hack into the system.</p>\n";
            } else {
                $error .= "<p>You are not logged in.</p>\n";
                $error .= "<p>You may attempt to <a href='" . $cameralife->baseURL . "/login.php'>log in here,</a>\n";
                $error .= "<p>Do you have cookies disabled?</p>\n";
            }
            $cameralife->error($error);
        }

        return ($this->curuser['auth'] >= $this->getPref($authName));
    }
}
