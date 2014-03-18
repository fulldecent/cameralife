<?php

/**Creates an RSS feed of folders
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
*/

/**
*/
  $features=array('theme','security', 'photostore');
  require 'main.inc';

  $query = isset($_GET['q']) ? $_GET['q'] : '';
  $search = new Search($query);
  $search->SetSort('newest');
  $results = $search->GetFolders();

  header('Content-type: text/xml');
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<rss version="2.0">
  <channel>
    <title><![CDATA[<?= $cameralife->GetPref('sitename') ?> - <?= $query ?>]]></title>
    <link><?= $cameralife->base_url ?></link>
    <description>Search results for '<?php $query ?>'</description>
    <language>en-us</language>
<?php
  foreach ($results as $result) {
    $icon = $result->GetIcon();
    #$date = strtotime($photo->Get('created'));
    #var_dump($icon, $result);

    echo "    <item>\n";
    echo "      <title><![CDATA[".$icon['name']."]]></title>\n";
    echo "      <link>".$icon['href']."</link>\n";
    echo "      <guid isPermaLink=\"true\">".$icon['href']."</guid>\n";
    echo "      <description><![CDATA[<a href=\"".$icon['href']."\"><img border=\"0\" src=\"".$icon['image']."\"></a>]]></description>\n";
    echo "      <category>photo</category>\n";
    echo "      <pubDate>".date('r',$icon['date'])."</pubDate>\n";
#    echo "      <enclosure url=\"".$icon['image']."\" type=\"image/jpeg\" length=\"0\"></enclosure>\n";
    echo "    </item>\n";
  }
?>
  </channel>
</rss>
