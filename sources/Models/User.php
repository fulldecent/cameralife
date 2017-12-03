<?php
namespace CameraLife\Models;

/**
 * Class User provides information about the current logged-in user
 *
 * @author    William Entriken <cameralife@phor.net>
 * @access    public
 * @version
 * @copyright 2001-2014 William Entriken
 */
class User extends IndexedModel
{
    /**
     * isLoggedIn
     *
     * (default value: false)
     *
     * @var    bool
     * @access public
     */
    public $isLoggedIn = false;

    /**
     * name
     *
     * (default value: null)
     *
     * @var    mixed
     * @access public
     */
    public $name = null;

    /**
     * email
     *
     * (default value: null)
     *
     * @var    mixed
     * @access public
     */
    public $email = null;

    /**
     * authorizationLevel
     *
     * (default value: 0)
     *
     * @var    int
     * @access public
     */
    public $authorizationLevel = 0;

    /**
     * remoteAddr IP address
     *
     * (default value: null)
     *
     * @var    mixed
     * @access public
     */
    public $remoteAddr = null;

    /**
     * lastOnline
     *
     * (default value: null)
     *
     * @var    mixed
     * @access public
     */
    public $lastOnline = null;

    public function __construct($modelId = null)
    {
        if (is_numeric($modelId)) {
            $result = Database::select('users', '*', "id=$modelId");
            $row = $result->fetchAssoc();
            if ($row) {
                $this->id = $row['id'];
                $this->isLoggedIn = true;
                $this->name = $row['username'];
                $this->remoteAddr = $row['last_ip'];
                $this->authorizationLevel = $row['auth'];
                $this->email = $row['email'];
                $this->lastOnline = $row['last_online'];
            }
        }
    }

    public static function userWithOpenId($identity, $email)
    {
        global $_SERVER;
        $cookie = mt_rand(0, 1000000000000);
        $result = Database::select('users', '*', "email=:email AND password=:password", null, null, ['email'=>$email,'password'=>$identity]);
        $row = $result->fetchAssoc();
        if ($row) {
            $retval = new User;
            $retval->id = $row['id'];
            $retval->isLoggedIn = true;
            $retval->name = $row['username'];
            $retval->remoteAddr = $row['last_ip'];
            $retval->authorizationLevel = $row['auth'];
            $retval->email = $row['email'];
            $retval->lastOnline = $row['last_online'];

            Database::update('users', ['cookie'=>$cookie], 'id=' . $retval->id);
            setcookie('cameralifeauth', $cookie, time() + 30000000, '/');
            $_COOKIE['cameralifeauth'] = $cookie;
            return $retval;
        }

        //TODO: breaks MVC
        setcookie('cameralifeauth', $cookie, time() + 30000000, '/');
        $_COOKIE['cameralifeauth'] = $cookie;
        $values['username'] = $email;
        $values['password'] = $identity;
        $values['auth'] = 1;
        $values['cookie'] = $cookie;
        $values['last_online'] = date('Y-m-d H:i:s');
        $values['last_ip'] = $_SERVER["REMOTE_ADDR"];
        $values['email'] = $email;
        $insertId = Database::insert('users', $values);

        $retval = new User;
        $retval->id = $insertId;
        $retval->isLoggedIn = true;
        $retval->name = $email;
        $retval->remoteAddr = $_SERVER["REMOTE_ADDR"];
        $retval->authorizationLevel = 1;
        $retval->email = $email;
        $retval->lastOnline = date('Y-m-d H:i:s');
        return $retval;
    }

    public static function logoutCurrentUser()
    {
        //TODO: breaks MVC
        setcookie('cameralifeauth', null, -1);
    }

    public static function currentUser($cookies)
    {
        global $_SERVER;
        $retval = new User;
        $retval->remoteAddr = $_SERVER['REMOTE_ADDR'];
        $cookiename = 'cameralifeauth';

        if (isset($cookies[$cookiename])) {
            $authcookie = $cookies[$cookiename];
            $result = Database::select('users', '*', "cookie='$authcookie'");
            $row = $result->fetchAssoc();
            if ($row) {
                $retval->id = $row['id'];
                $retval->isLoggedIn = true;
                $retval->name = $row['username'];
                $retval->authorizationLevel = $row['auth'];
                $retval->email = $row['email'];
                $retval->lastOnline = $row['last_online'];
            }
        }
        return $retval;
    }

    public function gravitarUrl()
    {
        $md5 = md5($this->email);
        return "//www.gravatar.com/avatar/$md5?s=16&d=identicon";
    }

    public function isAuthorizedForAction($action)
    {
        return false;
    }
}
