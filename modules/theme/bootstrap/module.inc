<?php

/**
 * Theme name - Bootstrap
 * @author William Entriken<cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @todo make this HTML valid
 */
class BootstrapTheme extends ThemeModule
{
    private $menu;
    private $cameralife;

    public function __construct($cameralife)
    {
        parent::__construct($cameralife);
        $this->cameralife = $cameralife;
        $this->name = 'Bootstrap';
        $this->about = 'Sends all pages a marked-up Twitter Bootstrap content and style';

        $this->preferences[] = array(
            'name' => 'main_thumbnails',
            'desc' => 'Show random, most popular, latest photos...',
            'type' => array('1' => 'Show N thumbnails', '0' => 'No'),
            'default' => '1'
        );
        $this->preferences[] = array(
            'name' => 'main_thumbnails_n',
            'desc' => '... N=',
            'type' => 'number',
            'default' => '4'
        );
        $this->preferences[] = array(
            'name' => 'main_topics',
            'desc' => 'Photo album topics',
            'type' => array('0' => "Don't show", '1' => 'Show all topics', '2' => "Show all topics, and N albums each"),
            'default' => '2'
        );
        $this->preferences[] = array(
            'name' => 'main_topics_n',
            'desc' => '... N=',
            'type' => 'number',
            'default' => '3'
        );
        $this->preferences[] = array(
            'name' => 'main_folders',
            'desc' => 'Folders on main page',
            'type' => array('0' => "Don't show", '1' => 'Show N random folders, M photos each'),
            'default' => '1'
        );
        $this->preferences[] = array(
            'name' => 'main_folders_n',
            'desc' => '... N=',
            'type' => 'number',
            'default' => '5'
        );
        $this->preferences[] = array(
            'name' => 'main_folders_m',
            'desc' => '... M=',
            'type' => 'number',
            'default' => '5'
        );
        $this->preferences[] = array(
            'name' => 'analytics',
            'desc' => 'Tracking code for Google Analytics (optional)',
            'type' => 'string',
            'default' => ''
        );
    }

    public function showPage($name, $object = null)
    {
        global $_SERVER, $_GET, $_POST;
        $cameralife = $this->cameralife;
        $objectname = strtolower($name);
        $$objectname = $object;
        require dirname(__FILE__) . '/' . strtolower($name) . '.inc';
    }

//TODO: GET RID OF THIS, BECAUSE ALT CAN BE SET BETTER BY THE CALLER?
    public function Image($name, $extra = array())
    {
        global $cameralife;
        if (preg_match('/src="([^"]*)"/i', $name)) # HTML IMG
        {
            $image['src'] = $regs[1];
            $image['alt'] = basename($image['src']);
        } else {
            if (preg_match('#\.|/#', $name)) # Image URL
            {
                $image['src'] = $name;
                $image['alt'] = basename($image['src']);
            } else # Named asset, like 'small-album'
            {
                $image['src'] = $cameralife->iconURL($name);
                $image['alt'] = $name;
            }
        }

        $image = array_merge($image, $extra);
        echo "<img";
        foreach ($image as $attr => $val) {
            if ($val) {
                echo " $attr=\"$val\"";
            }
        }
        echo " />\n";
    }

    /**
     * @access public
     * @param view $view VIEW being displayed
     * @param string $menu Block of HTML to show on side of page in a well
     * @param string $extrahead (default: '')
     * @param string $onload (default: '')
     * @return void
     */
    public function header($view, $menu = '', $extrahead = '', $onload = '')
    {
        $cameralife = $this->cameralife;
        $this->menu = $menu;
        $openGraph = $view->GetOpenGraph();

        $myfolder = new Folder ('/upload/' . $cameralife->security->getName());
        $myfolderOpenGraph = $myfolder->getOpenGraph();
        $myfolderURL = $myfolderOpenGraph['og:url'];
        $body = $onload ? "<body onLoad=\"$onload\">" : '<body>';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <title><?= htmlentities($openGraph['og:title'], null, "UTF-8") ?></title>
            <meta property="og:title" content="<?= htmlentities($openGraph['og:title'], null, "UTF-8") ?>">
            <meta property="og:type" content="<?= htmlentities($openGraph['og:type'], null, "UTF-8") ?>">
            <meta property="og:url" content="<?= htmlentities($openGraph['og:url'], null, "UTF-8") ?>">
            <meta property="og:image" content="<?= htmlentities($openGraph['og:image'], null, "UTF-8") ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="generator" content="Camera Life version <?= $cameralife->version ?>">
            <meta name="author" content="<?= $cameralife->getPref('owner_email') ?>">
            <?= $extrahead ?>
            <!-- Le styles -->
            <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
            <link rel="search" href="opensearch.xml.php" type="application/opensearchdescription+xml" title="Content Search" />
            <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
            <link href="<?=
            $cameralife->baseURL . "/modules/theme/" . (basename(
                dirname(__FILE__)
            )) ?>/css-extra/custom.css" rel="stylesheet">
            <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
            <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
            <![endif]-->
        </head>
        <?= $body ?>
        <nav class="navbar navbar-default navbar-static-top" role="navigation">
            <div class="container">
                <a class="navbar-brand" href="<?= $cameralife->baseURL ?>"><?= $cameralife->getPref('sitename') ?></a>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse">
                    <form class="navbar-form navbar-left" action="<?= $cameralife->baseURL ?>/search.php" method="get">
                        <input type="text" class="form-control" placeholder="Search" name="q"/>
                    </form>
                    <ul class="nav navbar-nav navbar-right">
                        <?php
                        if ($cameralife->security->getName()) {
                            $gravitarHTML = htmlentities(
                                "http://www.gravatar.com/avatar/" . md5(
                                    $cameralife->security->getName()
                                ) . "?s=16&d=identicon"
                            );

                            ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img
                                        src="<?= $gravitarHTML ?>" height=16
                                        width=16> <?= $cameralife->security->getName() ?> <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= $myfolderURL ?>">My Uploads</a></li>
                                    <li><a href="<?= $cameralife->baseURL . '/admin/index.php' ?>">Administer</a></li>
                                    <li><a href="<?= $cameralife->baseURL . '/admin/index.php' ?>">Customize this
                                            page</a></li>
                                    <li class="divider"></li>
                                    <li><a href="<?= $cameralife->baseURL . '/logout.php' ?>">Sign Out</a></li>
                                </ul>
                            </li>
                        <?php } else { ?>
                            <a class="btn btn-default navbar-btn narbar-right"
                               href="<?= $cameralife->security->LoginURL() ?>">
                                <i class="icon-user"></i>
                                <img alt="facebook" src="http://facebook.com/favicon.ico" height=16 width=16>
                                <img alt="google" src="http://google.com/favicon.ico" height=16 width=16>
                                <img alt="yahoo" src="http://yahoo.com/favicon.ico" height=16 width=16>
                                Login / Free account
                            </a>
                        <?php } ?>
                    </ul>
                    <?php if (isset($_GET['path'])) { ?>
                    <a class="btn btn-default navbar-btn navbar-right"
                       href="<?= $cameralife->baseURL ?>/upload.php?path=<?= htmlentities($_GET['path']) ?>">
                        <?php } else { ?>
                        <a class="btn btn-default navbar-btn navbar-right"
                           href="<?= $cameralife->baseURL ?>/upload.php">
                            <?php } ?>
                            <i class="icon-plus"></i>
                            Upload
                        </a>
                </div>
                <!-- /.navbar-collapse -->
            </div>
        </nav>

        <div class="container">
        <?php if ($this->menu) { ?>
        <div class="row">
        <div class="col-md-9">
    <?php
    }
    }

    public function footer()
    {
        $cameralife = $this->cameralife;
        if ($this->menu) {
            ?>
            </div><!--/span-->
            <div class="col-md-3">
                <div class="well sidebar-nav">
                    <?= $this->menu ?>
                </div>
            </div>
            </div><!--/row-->
        <?php
        }
        ?>
        <hr>

        <footer>
            <p>
                <a href="mailto:<?= $cameralife->getPref('owner_email') ?>"><i class="icon-envelope"></i> Contact site
                    owner</a>
                &nbsp;
                <a href="<?= $cameralife->baseURL ?>/stats.php"><i class="icon-signal"></i> Site stats</a>
                &nbsp;
                <a href="http://fulldecent.github.com/cameralife"><i class="icon-globe"></i> Built with Camera Life</a>
            </p>
        </footer>

        </div>

        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
        <!--    <script src="//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.0/jquery.mobile.min.js"></script> -->
        <script type="text/javascript"
                src="<?= $cameralife->baseURL . '/modules/theme/' . $cameralife->getPref('theme') ?>/theme.js"></script>
        <?php
        if ($cameralife->theme->getPref('analytics')) {
            ?>
            <!--TRACKING CODE-->
            <script type="text/javascript">
                var _gaq = _gaq || [];
                _gaq.push(['_setAccount', '<?= $cameralife->theme->getPref('analytics') ?>']);
                _gaq.push(['_trackPageview']);

                (function () {
                    var ga = document.createElement('script');
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(ga, s);
                })();
            </script>
            <!--END TRACKING CODE-->
        <?php
        }
        ?>
        </body>
        </html>
    <?php
    }

    // Query is an array of Views
    public function grid($views)
    {
        echo '<div>' . PHP_EOL;
        foreach ($views as $view) {
            $openGraph = $view->GetOpenGraph();

            echo '<div class="l1" style="margin-bottom:1em">' . PHP_EOL;
            echo '<a class="l2" href="' . htmlspecialchars($openGraph['og:url']) . '">';
            if (isset($openGraph['og:image:width']) && isset($openGraph['og:image:height'])) {
                $imageattrs = array(
                    'alt' => $openGraph['og:title'],
                    'width' => $openGraph['og:image:width'],
                    'height' => $openGraph['og:image:height'],
                    'class' => 'l3'
                );
            } else {
                $imageattrs = array('alt' => $openGraph['og:title'], 'class' => 'l3');
            }
            $this->Image($openGraph['og:image'], $imageattrs);
            if (isset($openGraph['og:image:width']) && isset($openGraph['og:image:height'])) {
                echo '<div class="l4" style="width:' . $openGraph['og:image:width'] . 'px">';
            } else {
                echo '<div class="l4">';
            }
            if ($openGraph['og:title'] != 'unnamed') {
                echo htmlentities($openGraph['og:title']);
            }
            echo '</div>';
            echo '</a>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
        }
        echo '</div>' . PHP_EOL;
    }

    /// Taken an Icon to make base url
    public function multiSection($sections, $baseURL, $conjunction = 'and', $query = null)
    {
        global $_GET;

        echo '<ul class="nav nav-tabs">' . PHP_EOL;
        foreach ($sections as $section) {
            if ($_GET['page'] == $section['page_name']) {
                echo "<li class=\"active\">";
            } else {
                echo "<li>";
            }
            parse_str(parse_url($baseURL, PHP_URL_QUERY), $query);
            $query['page'] = $section['page_name'];
            $url = preg_replace('/\?.*/', '', $baseURL) . '?' . http_build_query($query);
            echo "<a href=\"$url\">";
            echo $section['name'];
            echo "</a>";
            echo "</li>";
        }

        echo "</ul>";
    }

    public function pageSelector($start, $total, $perPage, $url)
    {
        if ($total > $perPage) // Refuse to only show one page
        {
            echo '<ul class="pagination">';

            if ($start == -1) {
                echo "<span class='current'>Randomly showing <b>$perPage</b> of <b>$total</b></span> ";
                echo "<a class='nextprev' href=\"$url\">More &#187;</a>";
            } else {
                $first = max($start - 2 * $perPage, 0);
                $last = min($first + 4 * $perPage, intval(($total - 1) / $perPage) * $perPage);
                $first = max($last - 4 * $perPage, 0);
                if ($last == 0) {
                    $last = 1;
                }

                if ($first != $start) {
                    parse_str(parse_url($url, PHP_URL_QUERY), $query);
                    $query['start'] = $start - $perPage;
                    $newURL = preg_replace('/\?.*/', '', $url) . '?' . http_build_query($query);
                    echo "<li><a href=\"" . htmlspecialchars($newURL) . "\">&laquo;</a></li>";
                } else {
                    echo "<li class=\"disabled\"><span>&laquo;</span></li>";
                }

                for ($i = $first; $i <= $last; $i += $perPage) {
                    if ($i == $start) {
                        parse_str(parse_url($url, PHP_URL_QUERY), $query);
                        $query['start'] = $i;
                        $newURL = preg_replace('/\?.*/', '', $url) . '?' . http_build_query($query);
                        echo "<li class=\"active\"><a href=\"" . htmlspecialchars($newURL) . "\">" . ($i / $perPage + 1) . "</a></li>";
                    } else {
                        parse_str(parse_url($url, PHP_URL_QUERY), $query);
                        $query['start'] = $i;
                        $newURL = preg_replace('/\?.*/', '', $url) . '?' . http_build_query($query);
                        echo "<li><a href=\"" . htmlspecialchars($newURL) . "\">" . ($i / $perPage + 1) . "</a></li>";
                    }
                }

                if ($last != $start) {
                    parse_str(parse_url($url, PHP_URL_QUERY), $query);
                    $query['start'] = $i;
                    $newURL = preg_replace('/\?.*/', '', $url) . '?' . http_build_query($query);

                    echo "<li><a href=\"" . htmlspecialchars($newURL) . "\">&raquo;</a></li>";
                } else {
                    echo "<li class=\"disabled\"><span>&raquo;</span></li>";
                }
            }
            echo "</ul>\n";
        }
    }

    public function addParam($url, $var, $val)
    {
      parse_str(parse_url($url, PHP_URL_QUERY), $query);
      $query[$val] = $val;
      return preg_replace('/\?.*/', '', $url) . '?' . http_build_query($query);
    }
}

?>
