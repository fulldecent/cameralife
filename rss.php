<?php
// see: http://validator.w3.org/feed/check.cgi?url=http%3A%2F%2Fcamera.phor.net%2Fcameralife%2Frss.php%3Fq%3D

/** Creates RSS feed of photos
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */

  $features=array('theme','security', 'filestore');
  require 'main.inc';

  $query = isset($_GET['q']) ? $_GET['q'] : '';
  $search = new Search($query);
  $searchicon = $search->GetIcon();
  $search->SetSort('newest');
  $photos = $search->GetPhotos();

  header('Content-type: text/xml');

  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
  
  /*
  <!--
  <icon>http://farm1.static.flickr.com/38/buddyicons/33939198@N00.jpg?1142557353#33939198@N00</icon>
  <subtitle></subtitle>
-->
*/
?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:dc="http://purl.org/dc/elements/1.1/"
      xmlns:media="http://search.yahoo.com/mrss/">
  <title><?= htmlentities($cameralife->GetPref('sitename')) ?> - <?= $searchicon['name'] ?></title>
  <link rel="self" href="<?= $cameralife->base_url.'/rss.php?q='.htmlentities($query) ?>"/>
  <link rel="alternate" type="text/html" href="<?= $searchicon['href'] ?>"/>
  <id>urn:CLsearch:thiscouldbebetter</id>

  <updated><?= date('c') ?></updated>
  <generator uri="http://fdcl.sf.net/">Camera Life</generator>
  <author>
    <name><?= $cameralife->GetPref('sitename') ?> maintainer</name>
    <uri><?= $cameralife->base_url ?></uri>
  </author>

<?php
  foreach ($photos as $photo) {
    $icon = $photo->GetIcon();
    $date = strtotime($photo->Get('created'));
    $exif = $photo->GetEXIF();
    if (isset($exif['Date taken']))
      $datetaken = date('c', strtotime($exif['Date taken']));
    else
      $datetaken = null;

    echo "    <entry>\n";
    echo "      <title>".htmlentities($photo->Get('description'))."</title>\n";
    echo "      <link rel=\"alternate\" type=\"text/html\" href=\"".$icon['href']."\" />\n";
    echo "      <id>urn:photo:".$photo->record['id']."</id>\n";
    echo "      <published>".date('c',$date)."</published>\n";
    echo "      <updated>".date('c',$date)."</updated>\n";
    if ($datetaken)
      echo "      <dc:date.Taken>$datetaken</dc:date.Taken>\n";
    echo "        <content type=\"html\">&lt;p&gt;&lt;a href=&quot;".htmlentities($icon['href'])."&quot;&gt;".htmlentities($photo->Get('description'))." &lt;img src=&quot;".$icon['image']."&quot; width=&quot;".$icon['width']."&quot; height=&quot;".$icon['height']."&quot; alt=&quot;".htmlentities($photo->Get('description'))."&quot; /&gt;&lt;/a&gt;&lt;/p&gt;</content>\n";
    echo "      <link rel=\"enclosure\" type=\"image/jpeg\" href=\"".$icon['image']."\" />\n";
    echo "    </entry>\n\n";
  }
?>
</feed>
