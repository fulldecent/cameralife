<?php
  # Create RSS feeds for all kinds of stuff

  $features=array('database','theme','security');
  require "main.inc";

  $search = new Search($_GET['q']);
  $search->SetSort('newest');
  $photos = $search->GetPhotos();

  header('Content-type: text/xml');
  echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
  <channel>
    <title><![CDATA[<?= $cameralife->GetPref('sitename'] ?> - <?= $_GET['q'] ?>])></title>
    <link><?= $cameralife->base_url ?></link>
    <description>Search results for '<? $_GET['q'] ?>'</description>
    <language>en-us</language>
<?php
  foreach($photos as $photo)
  {
    $icon = $photo->GetIcon();
    $date = strtotime($photo->Get('created'));

    echo "    <item>\n";
    echo "      <title><![CDATA[".$photo->Get('description')."]]></title>\n";
    echo "      <link>".$cameralife->base_url.'/'.$icon['href']."</link>\n";
    echo "      <guid isPermaLink=\"true\">".$cameralife->base_url.'/'.$icon['href']."</guid>\n";
    echo "      <description><![CDATA[<a href=\"".$cameralife->base_url.'/'.$icon['href']."\"><img border=\"0\" src=\"".$cameralife->base_url.'/'.$icon['image']."\"></a>]]></description>\n";
    echo "      <category>photo</category>\n";
    echo "      <pubDate>".date('r',$date)."</pubDate>\n";
#    echo "      <enclosure url=\"".$cameralife->base_url.'/'.$icon['image']."\" type=\"image/jpeg\" length=\"0\"></enclosure>\n";
    echo "    </item>\n";
  }
?>
  </channel>
</rss>
