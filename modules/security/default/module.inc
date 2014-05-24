<?php

/**
 * Incorporates standard security implementation.
 *
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 *
 *<ul>
 *<li>A password to authenticate</li>
 *<li>A cookie to handle "sessions"</li>
 *<li>Groups to determine permissions</li>
 *<li>A database to store the above three features</li>
 *</ul>
 */
class DefaultSecurity extends SecurityModule
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
        $authcookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : null;
        if ($authcookie) {
            $result = $cameralife->database->Select('users', '*', "cookie='$authcookie'");
            $this->curuser = $result->fetchAssoc()
            or $this->curuser = array('auth' => '0');
        } else {
            $this->curuser = array('auth' => '0');
        }
    }

    /**
     * @param $username
     * @param $post
     * @internal param $password
     * @return bool|string TRUE if successful, or error message
     */
    public function login($username, $post)
    {
        global $cameralife;
        $password = $post['password'];
        $username = strtolower($username);
        $username = preg_replace('/[^0-9a-z_]/', '', $username);
        $password = crypt($password, $username);

        $result = $cameralife->database->Select('users', '*', "username='$username' AND password='$password'");

        if ($user = $result->fetchAssoc()) // Login legal
        {
            $cookie = rand(0, 999999);
            $values['cookie'] = $cookie;
            $values['last_online'] = date('Y-m-d');
            $values['last_ip'] = $_SERVER["REMOTE_ADDR"];
            $cameralife->database->Update('users', $values, "username='$username'");
            setcookie($this->getPref('auth_cookie'), $cookie, time() + 30000000, '/');
            $_COOKIE[$this->getPref('auth_cookie')] = $cookie;
            return true;
        } else // Login illegal
        {
            return "Invalid login";
        }
    }

    public function loginURL()
    {
        global $cameralife;
        return $cameralife->baseURL . "/modules/security/default/login.php";
    }

    /**
     * @param $username
     * @param $post
     * @internal param $password
     * @internal param string $email
     * @return bool|string TRUE if successful or error message
     */
    public function register($username, $post)
    {
        global $cameralife;
        $password = $post['password'];
        $email = $post['email'];
        $username = strtolower($username);
        $username = preg_replace('/[^0-9a-z]/i', '', $username);
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

    /**.
     * Provides a URL, that the user can go to, to administer users and permissions
     *  Or
     * @return FALSE if the user is not allowed to administer.
     */
    public function administerURL()
    {
        global $cameralife;
        if ($this->authorize('admin_file') ||
            $this->authorize('admin_theme') ||
            $this->authorize('admin_customize')
        ) {
            return $cameralife->baseURL . "/modules/security/default/administer.php";
        }
        return false;
    }

    /* Returns the current user's name or the empty string if not logged in */
    /**@return the current user's name or the empty string if not logged in
     */
    public function getName()
    {
        if (!isset ($this->curuser)) {
            $this->getCurUser();
        }
        return isset($this->curuser['username']) ? $this->curuser['username'] : '';
    }

    /* Determines if the user is authoried to performed the named action,
    ** if REQUIRED is set, nonauthorization is a fatal error.
    ** This function must at least support the actions named in the README
    ** file, of the directory of the file you are currently reading at this
    ** time.
    */
    /**Determines if the user is authoried to performed the named action;if REQUIRED is set, nonauthorization is a fatal error.
     * This function must at least support the actions named in the README file, of the directory of the file you are currently reading at this
     * time.
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