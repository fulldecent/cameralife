<?php
namespace CameraLife\Controllers;
use CameraLife\Models as Models;
use CameraLife\Views as Views;

/**
 * Base class for all Controller objects, each controller corresponds to a
 * part of the site referenced by a URL of the format index.php?page=XXX
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access    public
 */

abstract class Controller
{
    private static $rewriteEnabled = null;

    /*****************************************
     * OPEN GRAPH METADATA, see ogp.me
     *****************************************/

    /**
     * REQUIRED
     * og:title
     * The title of your object as it should appear within the graph, e.g., "The Rock".
     *
     * @var    string
     * @access public
     */
    public $title;

    /**
     * REQUIRED
     * og:type
     * The type of your object, e.g., "video.movie". Depending on the type you specify,
     * other properties may also be required.
     * Will usually by "website"
     *
     * @var    string
     * @access public
     */
    public $type;

    /**
     * REQUIRED
     * og:url
     * The canonical URL of your object that will be used as its permanent ID
     * in the graph, e.g., "http://www.imdb.com/title/tt0117500/".
     *
     * @var    string
     * @access public
     */
    public $url;

    /**
     * REQUIRED
     * og:image
     * An image URL which should represent your object within the graph.
     *
     * @var    string
     * @access public
     */
    public $image;

    /**
     * OPTIONAL
     * og:description
     * A one to two sentence description of your object.
     *
     * @var    string
     * @access public
     */
    public $description;

    /**
     * OPTIONAL
     * og:determiner
     * The word that appears before this object's title in a sentence. An enum of
     * (a, an, the, "", auto). If auto is chosen, the consumer of your data should
     * chose between "a" or "an". Default is "" (blank).
     *
     * @var    string
     * @access public
     */
    public $determiner;

    /**
     * OPTIONAL
     * og:site_name
     * If your object is part of a larger web site, the name which should
     * be displayed for the overall site. e.g., "IMDb".
     *
     * @var    string
     * @access public
     */
    public $siteName;

    /**
     * OPTIONAL
     * og:image:secure_url
     * An alternate url to use if the webpage requires HTTPS.
     *
     * @var    string
     * @access public
     */
    public $imageSecureUrl;

    /**
     * OPTIONAL
     * og:image:type
     * A MIME type for this image.
     *
     * @var    string
     * @access public
     */
    public $imageType;

    /**
     * OPTIONAL
     * og:image:width
     * The number of pixels wide.
     *
     * @var    string
     * @access public
     */
    public $imageWidth;

    /**
     * OPTIONAL
     * og:image:height
     * The number of pixels high.
     *
     * @var    string
     * @access public
     */
    public $imageHeight;

    /**
     * OPTIONAL
     * NOT AN OPENGRAPH standard
     * A font-awesome icon representing this view
     *
     * @var    string
     * @access public
     */
    public $icon;

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct($id = null)
    {
        $preferences = new Models\Preferences;
        $this->siteName = $preferences->valueForModuleWithKey('CameraLife', 'sitename');
        $this->title = $this->siteName;
        $this->type = 'website';
        $this->image = constant('BASE_URL') . '/assets/main.png';
        $this->imageType = 'image/png';
        $this->url = self::getUrlForID($id);
    }

    public static function getUrl()
    {
        return self::getUrlForIDWithParameters(null, array());
    }

    public static function getUrlForID($object)
    {
        return self::getUrlForIDWithParameters($object, array());
    }

    public static function getUrlForIDWithParameters($id, $parameters)
    {
        $reflection = new \ReflectionClass(get_called_class());
        $shortName = $reflection->getShortName();
        $page = lcfirst(basename($shortName, 'Controller'));
        $query = http_build_query($parameters);
        $id = ltrim($id, '/');

        // TODO, use this http://stackoverflow.com/a/14375686/300224
        if (self::$rewriteEnabled === null) {
            self::$rewriteEnabled = Models\Preferences::valueForModuleWithKey('CameraLife', 'rewrite') == 'yes';
        }

        if (!self::$rewriteEnabled) {
            return constant('BASE_URL') . '/index.php?page=' . $page . '&id=' . $id . ($query ? '&' . $query : '');
        }
        return constant('BASE_URL') . '/' . $page . '/' . $id . ($query ? '?' . $query : '');
    }

    /**
     * Handles and routes any HTTP request using a subclass that can handles it. URLs are like:
     *
     *   index.php?page=Photo&id=1243&...
     *
     * But usually the user will see pretty URLs that rewrite to the above
     *
     * @access public
     * @param  array $get
     * @param  array $post
     * @param  array $files
     * @param  array $cookies
     * @param  array $server
     * @return void
     */
    public static function handleRequest($get, $post, $files, $cookies, $server)
    {
        try {
            $page = isset($get['page']) ? $get['page'] : 'mainPage';
            $dbIsCurrent = Models\Database::installedSchemaIsCorrectVersion();

            if (!$dbIsCurrent && substr($page, 0, 5) !== 'setup') {
                if (!Models\Database::connectionParametersAreSet() && $page != 'setupInstall') {
                    header('Location: ' . SetupInstallController::getUrl());
                    return;
                }
                if ($page != 'setupUpgrade' && $page != 'setupInstall') {
                    header('Location: ' . SetupUpgradeController::getUrl());
                    return;
                }
            }

            $controllerClass = 'CameraLife\\Controllers\\' . ucfirst($page) . 'Controller';
            if (!class_exists($controllerClass)) {
                throw new \Exception('Page not found');
            }
            if (isset($get['id'])) {
                $controller = new $controllerClass($get['id']);
            } else {
                $controller = new $controllerClass;
            }
            $method = 'handle' . ucfirst(strtolower($server['REQUEST_METHOD']));
            $controller->$method($get, $post, $files, $cookies);
        } catch (\Exception $e) {
            self::handleException($e);
        }
    }

    /**
     * Handle the HTTP request, emits HTTP headers as necessary and
     * output page content, HTML or otherwise
     *
     * @access public
     * @param  array $get
     * @param  array $post
     * @param  array $files
     * @return void
     */
    public abstract function handleGet($get, $post, $files, $cookies);

    /**
     * Default implementation redirects to same page for get
     *
     * @access public
     * @param  mixed $get
     * @param  mixed $post
     * @param  mixed $files
     * @param  mixed $cookies
     * @return void
     */
    public function handlePost($get, $post, $files, $cookies)
    {
        header('Location: ' . $this->getUrlForID($get['id']));
    }

    public static function handleException(\Exception $exception, $showDebuggingInformation = true)
    {
        $view = new Views\ExceptionView;
        $view->exception = $exception;
        $view->showDebuggingInformation = $showDebuggingInformation;
        $view->render();
    }
}
