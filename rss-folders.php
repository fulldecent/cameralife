<?php
/**
 * Creates RSS feed of folders
 *
 * @see http://validator.w3.org/feed/check.cgi?url=http%3A%2F%2Fcamera.phor.net%2Fcameralife%2Frss-folders.php%3Fq%3D
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2014 William Entriken
 * @access public
 */

require 'main.inc';
$features = array('theme', 'security', 'fileStore');
$cameralife = CameraLife::cameraLifeWithFeatures($features);

$query = isset($_GET['q']) ? $_GET['q'] : '';
$search = new Search($query);
$openGraph = $search->getOpenGraph();
$search->setSort('newest');
$views = $search->getFolders();

header('Content-type: text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<feed xmlns="http://www.w3.org/2005/Atom"
    >
    <title><?= htmlentities($cameralife->getPref('sitename')) ?> - <?=
        htmlspecialchars(
            $openGraph['og:title']
        ) ?></title>
    <link rel="self" href="<?= $cameralife->baseURL . '/rss.php?q=' . htmlentities($query) ?>"/>
    <link rel="alternate" type="text/html" href="<?= htmlspecialchars($openGraph['og:url']) ?>"/>
    <id>urn:CLsearch:thiscouldbebetter</id>

    <updated><?= date('c') ?></updated>
    <generator uri="https://github.com/fulldecent/cameralife">Camera Life</generator>
    <author>
        <name><?= $cameralife->getPref('sitename') ?> maintainer</name>
        <uri><?= $cameralife->baseURL ?></uri>
    </author>

    <?php
    foreach ($views as $view) {
        $viewOpenGraph = $view->GetOpenGraph();
        $date = strtotime($view->date); // WARNING: This only works when VIEW is a FOLDER
        echo "    <entry>\n";
        echo "      <title>" . htmlentities($viewOpenGraph['og:title']) . "</title>\n";
        echo "      <link rel=\"alternate\" type=\"text/html\" href=\"" . htmlspecialchars(
                $viewOpenGraph['og:url']
            ) . "\" />\n";
        echo "      <id>urn:folder:" . rawurlencode($view->path) . "</id>\n";
        echo "      <published>" . date('c', $date) . "</published>\n";
        echo "      <updated>" . date('c', $date) . "</updated>\n";
        echo "        <content type=\"html\">&lt;p&gt;&lt;a href=&quot;" . htmlspecialchars(
                $viewOpenGraph['og:url']
            ) . "&quot;&gt;" . htmlspecialchars($viewOpenGraph['og:title']) . " &lt;img src=&quot;" . htmlspecialchars(
                $viewOpenGraph['og:image']
            ) . "&quot;
    alt=&quot;" . htmlentities($viewOpenGraph['og:title']) . "&quot; /&gt;&lt;/a&gt;&lt;/p&gt;</content>\n";
        echo "      <link rel=\"enclosure\" type=\"image/jpeg\" href=\"" . htmlspecialchars(
                $viewOpenGraph['og:image']
            ) . "\" />\n";
        echo "    </entry>\n\n";
    }
    ?>
</feed>
