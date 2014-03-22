<?php
 /** Incorporates standard security implementaion. 
 *
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
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
    var $curuser;
    var $authcookie;

    function DefaultSecurity()
    { 
      $this->preferences[] = array('name'=>'auth_photo_rename', 'type'=>'number', 'default'=>'0');
      $this->preferences[] = array('name'=>'auth_photo_delete', 'type'=>'number', 'default'=>'0');
      $this->preferences[] = array('name'=>'auth_photo_modify', 'type'=>'number', 'default'=>'3');
      $this->preferences[] = array('name'=>'auth_admin_albums', 'type'=>'number', 'default'=>'4');
      $this->preferences[] = array('name'=>'auth_photo_upload', 'type'=>'number', 'default'=>'1');
      $this->preferences[] = array('name'=>'auth_admin_file', 'type'=>'number', 'default'=>'4');
      $this->preferences[] = array('name'=>'auth_admin_theme', 'type'=>'number', 'default'=>'4');
      $this->preferences[] = array('name'=>'auth_admin_customize', 'type'=>'number', 'default'=>'5');
      $this->preferences[] = array('name'=>'auth_cookie', 'type'=>'string', 'default'=>'cameralifeauth');
    }

    function GetCurUser()
    {
      global $_COOKIE, $cameralife;

      $cookiename = $this->GetPref('auth_cookie');
      $authcookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : NULL;

      if ($authcookie)
      {
        $result = $cameralife->Database->Select('users','*',"cookie='$authcookie'");
        $this->curuser = $result->FetchAssoc()
          or $this->curuser = array('auth'=>'0');
      }
      else
      {
        $this->curuser = array('auth'=>'0');
      }
    }
  
    /* Return TRUE or a string that is an error message */
    /**@return TRUE | a string that is an error message
    */
    function Login ($username, $password)
    {
      global $cameralife;

      $username = strtolower($username);
      $username = preg_replace('/[^0-9a-z_]/','',$username);
      $password = crypt($password,$username);

      $result = $cameralife->Database->Select('users','*',"username='$username' AND password='$password'");

      if ($user = $result->FetchAssoc()) // Login legal
      {
        $cookie = rand(0,999999);
        $values['cookie'] = $cookie;
        $values['last_online'] = date('Y-m-d');
        $values['last_ip'] =  $_SERVER["REMOTE_ADDR"];
        $cameralife->Database->Update('users',$values,"username='$username'");
        setcookie($this->GetPref('auth_cookie'),$cookie,time()+30000000,'/');
        $_COOKIE[$this->GetPref('auth_cookie')] = $cookie;
        return true;
      }
      else // Login illegal
        return "Invalid login";
    }


    function LoginURL () 
    { 
      global $cameralife;
      return $cameralife->base_url."/modules/security/default/login.php";
    }

    /* Return TRUE + cookie or a string that is an error message */
    /**@return TRUE,cookie|an error message string
    */
    function Register ($username, $password, $email = '')
    {
      global $cameralife;

      $username = strtolower($username);
      $username = eregi_replace('[^0-9a-z_]','',$username);
      $result = $cameralife->Database->Select('users','*',"username='$username'");
      $password = crypt($password,$username);
      $cookie = rand(0,999999);

      if ($user = $result->FetchAssoc())
        return 'Username already taken';

      if ($username == '')
        return 'Username empty';

      $values['username'] = $username;
      $values['password'] = $password;
      $values['auth'] = 1;
      $values['cookie'] = $cookie;
      $values['last_online'] = date('Y-m-d');
      $values['last_ip'] = $HTTP_SERVER_VARS["REMOTE_ADDR"];
      $values['email'] = $email;
      $cameralife->Database->Insert('users',$values);
      setcookie($this->GetPref('auth_cookie'),$cookie,time()+30000000,'/');
      return true;
    }

    /* Logs the user out. Returns true  or and exit url if you really want to */
    /**Logs the user out 
    *@return true | exit URL if you really want to quit the cameralife site
    */
    
    function Logout ()
    {
      global $cameralife;
      setcookie($this->GetPref('auth_cookie'),'',time(),'/');
      return true;
    }

    /**.
     * Provides a URL, that the user can go to, to administer users and permissions    
     *  Or 
     *@return FALSE if the user is not allowed to administer.
     */
    function AdministerURL () 
    { 
      global $cameralife;
      if ($this->authorize('admin_file') ||
          $this->authorize('admin_theme') ||
          $this->authorize('admin_customize'))
      return $cameralife->base_url."/modules/security/default/administer.php";
      return FALSE;
    }

    /* Returns the current user's name or the empty string if not logged in */
    /**@return the current user's name or the empty string if not logged in 
    */
    function GetName () 
    { 
      if (!isset ($this->curuser))
        $this->GetCurUser();
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
    function Authorize ($auth_name, $required=false) 
    {
      global $cameralife;

      $auth_name = "auth_$auth_name";
      if (!isset ($this->curuser))
        $this->GetCurUser();

      if (NULL == ($this->GetPref($auth_name)))
        $cameralife->Error("The privilege $auth_name does not exist.", __FILE__, __LINE__);

      if ($this->curuser['auth'] < $this->GetPref($auth_name) && $required)
      {
        $error = "<span style='font-size:x-large'>You are not authorized to view this page</span>\n";
        $error.= "<p>To view this page, you must have the <strong>$auth_name</strong> privilege.<br>\n";
        $error.="This is given to all users of authorization level at least <strong>".$this->GetPref($auth_name)."</strong>.</p>\n";
        if ($this->curuser['username'])
        {
          $error.= "<p>You are logged in as <strong>".$this->curuser['username']."</strong> with authorization level <strong>".$this->curuser['auth']."</strong>.</p>\n";
          $error.= "<p>Either ask an administrator to increase your authorization level, or stop trying to hack into the system.</p>\n";
        }
        else
        {
          $error.= "<p>You are not logged in.</p>\n";
          $error.= "<p>You may attempt to <a href='".$cameralife->base_url."/login.php'>log in here,</a>\n";
          $error.= "<p>Do you have cookies disabled?</p>\n";
        }
        $cameralife->Error($error);
      }

      return ($this->curuser['auth'] >= $this->GetPref($auth_name));
    }
  }

?>
