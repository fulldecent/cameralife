<?php
/**
 * Creates sitemap for search engines
 * Includes a master index and indexs for each 1000 photos
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

  $features=array('security');
  require 'main.inc';

  $page = $_GET['page'];
  $id = (int) $_GET['id'];

  $baseurl = $cameralife->baseURL;
  $stats = new Stats;
  $counts = $stats->getCounts();

  header('Content-type: text/xml');
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

  $nodes = array();

  if (!isset($page)) {
    $lastphoto = $cameralife->database->SelectOne('photos', 'MAX(id)', 'status=0');
    $lastalbum = $cameralife->database->SelectOne('albums', 'MAX(id)');
?>
<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
         http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"
         xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap>
    <loc><?= $baseurl ?>/sitemap.xml?page=common</loc>
    <lastmod><?= $cameralife->getPref('sitedate') ?></lastmod>
  </sitemap>
  <sitemap>
    <loc><?= $baseurl ?>/sitemap.xml?page=albums</loc>
  </sitemap>
  <sitemap>
    <loc><?= $baseurl ?>/sitemap.xml?page=topics</loc>
  </sitemap>
<?php
    flush();
    for ($id = 0; $id < $lastphoto; $id += 1000) {
      if ($cameralife->database->SelectOne('photos', 'COUNT(*)', 'status = 0 AND id>='.$id.' AND id<'.($id+1000)) == 0)
        continue;
      $photodate = $cameralife->database->SelectOne('photos', 'MAX(created)', 'status = 0 AND id>='.$id.' AND id<'.($id+1000));
      echo "  <sitemap>\n";
      echo "    <loc>".$baseurl."/sitemap.xml?page=photos&amp;id=".$id."</loc>\n";
      echo "    <lastmod>".$photodate."</lastmod>\n";
      echo "  </sitemap>\n";
      flush();
    }

    echo "</sitemapindex>\n";
    exit(0);
  } elseif ($page == 'common') {
    $nodes[] = array($baseurl.'/index.php', '1.0');
    $nodes[] = array($baseurl.'/login.php', '0.1');
    $nodes[] = array($baseurl.'/stats.php', '0.1');
  } elseif ($page == 'albums') {
    $result = $cameralife->database->Select('albums', 'id, hits');

    while($record = $result->FetchAssoc())
      $nodes[] = array($baseurl.'/album.php?id='.$record['id'], round(log($record['hits']+1,$counts['maxalbumhits']+1), 4));
  } elseif ($page == 'topics') {
    $result = $cameralife->database->Select('albums', 'DISTINCT(topic)');

    while($record = $result->FetchAssoc())
      $nodes[] = array($baseurl.'/topic.php?name='.urlencode($record['topic']));
  } elseif ($page == 'photos') {
    $result = $cameralife->database->Select('photos', 'id, hits', 'status = 0 AND id>='.$id.' AND id<'.($id+1000), 'ORDER BY id');

    while($record = $result->FetchAssoc())
      $nodes[] = array($baseurl.'/photo.php?id='.$record['id'], round(log($record['hits']+1,$counts['maxphotohits']+1), 4));
  } else {
    die('Invalid page');
  }

  echo "<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
  echo "  xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n";
  echo "  http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\"\n";
  echo "  xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
  foreach ($nodes as $node) {
    echo "  <url>\n";
    echo "    <loc>".$node[0]."</loc>\n";
    if (isset($node[1]))
      echo "    <priority>".$node[1]."</priority>\n";
    echo "  </url>\n";
  }
  echo '</urlset>';
