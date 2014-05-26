<?php
namespace CameraLife;

/**
 * This "API" is subject to change; specifically the URL part will be altered.
 * The following named security actions must be supported by authorize():
 * <ul>
 *  <li>photo_rename</li>
 *  <li>photo_delete</li>
 *  <li>photo_modify</li>
 *  <li>admin_albums</li>
 *  <li>photo_upload</li>
 *  <li>admin_file</li>
 *  <li>admin_theme </li>
 *  <li>admin_customize</li>
 * </ul>
 * @author William Entriken <cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 William Entriken
 */
abstract class SecurityModule extends CameraLifeModule
{
    /* Provides a URL, relative to $cameralife->baseURL that the user can
    ** login at
    */
    abstract public function loginURL();

    /* Logs the user out. Returns true or and exit url if you really want to */
    abstract public function logout();

    /* Provides a URL, relative to $cameralife->baseURL that the user can
    ** go to, to administer users and permissions. Or returns FALSE, if the
    ** user is not allowed to administer.
    */
    abstract public function administerURL();

    /* Returns the current user's name or the empty string if not logged in */
    abstract public function getName();

    /**Determines if the user is authoried to performed the named action,
     * if REQUIRED is set, nonauthorization is a fatal error.
     * This function must at least support the follwing actions
     * <ul>
     *  <li>photo_rename</li>
     *  <li>photo_delete</li>
     *  <li>photo_modify</li>
     *  <li>admin_albums</li>
     *  <li>photo_upload</li>
     *  <li>admin_file</li>
     *  <li>admin_theme </li>
     *  <li>admin_customize</li>
     * </ul>
     */
    abstract public function authorize($action, $required = false);
}