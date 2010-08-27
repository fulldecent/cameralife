<?php

/** Creates RSS feed of photos
 *@link http://fdcl.sourceforge.net
 *@version 2.6.3b5
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */

  $features=array('database','theme','security', 'photostore');
  require "main.inc";

  $search = new Search($_GET['q']);
  $searchicon = $search->GetIcon();
  $search->SetSort('newest');
  $photos = $search->GetPhotos();

  header('Content-type: application/rss+xml');

  echo "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\n";
?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:dc="http://purl.org/dc/elements/1.1/"
      xmlns:media="http://search.yahoo.com/mrss/">

  <title><?= htmlentities($cameralife->GetPref('sitename')) ?> - <?= $searchicon['name'] ?></title>
  <link rel="self" href="<?= $cameralife->base_url.'/rss.php?q='.htmlentities($_GET['q']) ?>"/>
  <link rel="alternate" type="text/html" href="<?= $searchicon['href'] ?>"/>
  <id>urn:CLsearch:thiscouldbebetter</id>
<!--
  <icon>http://farm1.static.flickr.com/38/buddyicons/33939198@N00.jpg?1142557353#33939198@N00</icon>
  <subtitle></subtitle>
-->
  <updated><?= date('c') ?></updated>
  <generator uri="http://fdcl.sf.net/">Camera Life</generator>
  <author>
    <name><?= $cameralife->GetPref('sitename') ?> maintainer</name>
    <uri><?= $cameralife->base_url ?></uri>
  </author>

<?php
  foreach($photos as $photo)
  {
    $icon = $photo->GetIcon();
    $date = strtotime($photo->Get('created'));
    $exif = $photo->GetEXIF();
    if ($exif['Date taken'])
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
