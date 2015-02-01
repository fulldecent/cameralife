<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * Simple view for rendering a list of configurable preferences
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class AdminSecurityView extends View
{

    /**
     * users
     *
     * @var    Models\User[]
     * @access public
     */
    public $users;

    /**
     * securityPolicies
     *
     * array of arrays: [name, key, security level]
     *
     * @var    array
     * @access public
     */
    public $securityPolicies;

    public function render()
    {
        echo "<h2>Registered users</h2>";

        echo "<table class=\"table\">";
        echo "<tr><th>User<th>Group<th>Last online<th>IP address<th>Actions<th>Uploads<th>Action";
        foreach ($this->users as $user) {
            echo "<tr><td>";
            echo "<img src=\"". $user->gravitarUrl() ."\"> ";
            echo htmlspecialchars($user->name);
            echo "<td>" . $user->authorizationLevel;
            echo "<td>" . $user->lastOnline;
            echo "<td>" . $user->remoteAddr;
            echo "<td>" . 77;
            echo "<td>" . 88;
            echo "<td>";
        }
        echo "</table>";

        var_dump($this->securityPolicies);

    }
}
