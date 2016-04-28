<?php
namespace CameraLife\Controllers;

use CameraLife\Views as Views;
use CameraLife\Models as Models;

abstract class HtmlController extends Controller
{
    public $footerJavascript;

    /**
     * htmlHeader function.
     *
     * @access protected
     * @param  array $cookies (default: array())
     * @return void
     */
    protected function htmlHeader($cookies = array())
    {
        $view = new Views\HeaderView;
        $view->openGraphObject = $this;
        $view->currentUser = Models\User::currentUser($cookies);
        $view->searchUrl = SearchController::getUrl();
        $view->adminUrl = AdminController::getUrl();
        $view->logoutUrl = LogoutController::getUrl();
        $view->favoritesUrl = FavoritesController::getUrl();
        $view->loginUrl = LoginController::getUrl();
        $view->numFavorites = Models\Favorites::favoritesForCurrentUser($cookies)->getPhotoCount();
        $view->ownerEmail = Models\Preferences::valueForModuleWithKey('CameraLife', 'owner_email');
        $view->render();
    }

	//TODO: should probably pass a user
	protected function renderNavbar($cookies = array())
	{
        $navbarView = new Views\NavbarView;
        $navbarView->openGraphObject = $this;
        $navbarView->currentUser = Models\User::currentUser($cookies);
        $navbarView->searchUrl = SearchController::getUrl();
        $navbarView->adminUrl = AdminController::getUrl();
        $navbarView->logoutUrl = LogoutController::getUrl();
        $navbarView->favoritesUrl = FavoritesController::getUrl();
        $navbarView->loginUrl = LoginController::getUrl();
        $navbarView->numFavorites = Models\Favorites::favoritesForCurrentUser($cookies)->getPhotoCount();
        $navbarView->ownerEmail = Models\Preferences::valueForModuleWithKey('CameraLife', 'owner_email');
        $navbarView->render();		
	}
	
	protected function renderBottomNavbar()
	{
        $view = new Views\BottomNavView;
        $view->statsUrl = StatisticsController::getUrl();
        $view->analyticsId = Models\Preferences::valueForModuleWithKey('BootstrapTheme', 'analytics');
        $view->ownerEmail = Models\Preferences::valueForModuleWithKey('CameraLife', 'owner_email');
        $view->mainPageOpenGraph = $this;
        $view->render();        
	}
	
	protected function renderOpenContainer()
	{
		        echo '        <div class="container">';
	}

	protected function renderCloseContainer()
	{
		        echo '        </div>';
	}

    /**
     * htmlFooter function.
     *
     * @access protected
     * @return void
     */
    protected function htmlFooter()
    {
        $view = new Views\FooterView;
        $view->statsUrl = StatisticsController::getUrl();
        $view->analyticsId = Models\Preferences::valueForModuleWithKey('BootstrapTheme', 'analytics');
        $view->ownerEmail = Models\Preferences::valueForModuleWithKey('CameraLife', 'owner_email');
        $view->extraJavascript = $this->footerJavascript;
        $view->mainPageOpenGraph = $this;
        $view->render();
    }

    /**
     * Utility function to render the HTML meta tags for this open graph entity
     *
     * @access public
     * @param  string $prefix To print before each line (default: '')
     * @return void
     */
    public function htmlRenderMetaTags($prefix = '')
    {
        $map = [
          'title' => 'og:title',
          'type' => 'og:type',
          'url' => 'og:url',
          'image' => 'og:image',
          'description' => 'og:description',
          'determiner' => 'og:determiner',
          'siteName' => 'og:site_name',
          'imageSecureUrl' => 'og:image:secure_url',
          'imageType' => 'og:image:type',
          'imageWidth' => 'og:image:width',
          'imageHeight' => 'og:image:height'
        ];
        foreach ($map as $var => $property) {
            if (!empty($this->$var)) {
                echo $prefix;
                echo "<meta property=\"{$property}\" content=\"".htmlspecialchars($this->$var)."\">\n";
            }
        }
    }
}
