<?php
/**
 * Creates RSS feed of photos
 *
 * @see http://validator.w3.org/feed/check.cgi?url=http%3A%2F%2Fcamera.phor.net%2Fcameralife%2Frss.php%3Fq%3D
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2014 Will Entriken
 * @access public
 */

$features=array('theme','security', 'filestore');
require 'main.inc';

$query = isset($_GET['q']) ? $_GET['q'] : '';
$search = new Search($query);
$openGraph = $search->GetOpenGraph();
$search->SetSort('newest');
$photos = $search->GetPhotos();

header('Content-type: text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:dc="http://purl.org/dc/elements/1.1/"
      xmlns:media="http://search.yahoo.com/mrss/">
  <title><?= htmlentities($cameralife->GetPref('sitename')) ?> - <?= htmlspecialchars($openGraph['og:title']) ?></title>
  <link rel="self" href="<?= $cameralife->baseURL.'/rss.php?q='.htmlentities($query) ?>"/>
  <link rel="alternate" type="text/html" href="<?= htmlspecialchars($openGraph['og:url']) ?>"/>
  <id>urn:CLsearch:thiscouldbebetter</id>

  <updated><?= date('c') ?></updated>
  <generator uri="https://github.com/fulldecent/cameralife">Camera Life</generator>
  <author>
    <name><?= $cameralife->GetPref('sitename') ?> maintainer</name>
    <uri><?= $cameralife->baseURL ?></uri>
  </author>

<?php
foreach ($photos as $photo) {
  $photoOpenGraph = $photo->GetOpenGraph();
  $date = strtotime($photo->Get('created'));
  $exif = $photo->GetEXIF();
  if (isset($exif['Date taken']))
    $datetaken = date('c', strtotime($exif['Date taken']));
  else
    $datetaken = null;

  echo "    <entry>\n";
  echo "      <title>".htmlentities($photo->Get('description'))."</title>\n";
  echo "      <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars($photoOpenGraph['og:url'])."\" />\n";
  echo "      <id>urn:photo:".$photo->record['id']."</id>\n";
  echo "      <published>".date('c',$date)."</published>\n";
  echo "      <updated>".date('c',$date)."</updated>\n";
  if ($datetaken)
    echo "      <dc:date.Taken>$datetaken</dc:date.Taken>\n";
  echo "        <content type=\"html\">&lt;p&gt;&lt;a href=&quot;".htmlspecialchars($photoOpenGraph['og:url'])."&quot;&gt;".htmlspecialchars($photoOpenGraph['og:title'])." &lt;img src=&quot;".htmlspecialchars($photoOpenGraph['og:image'])."&quot; width=&quot;".htmlspecialchars($photoOpenGraph['og:image:width'])."&quot; height=&quot;".htmlspecialchars($photoOpenGraph['og:image:height'])."&quot; alt=&quot;".htmlentities($photo->Get('description'))."&quot; /&gt;&lt;/a&gt;&lt;/p&gt;</content>\n";
  echo "      <link rel=\"enclosure\" type=\"image/jpeg\" href=\"".htmlspecialchars($photoOpenGraph['og:image'])."\" />\n";
  echo "    </entry>\n\n";
}
?>
</feed>
