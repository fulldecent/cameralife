<?php
  $features=array('database','security','imageprocessing', 'photostore');
  require '../main.inc';
  $cameralife->base_url = dirname($cameralife->base_url);

  echo '<html><head><title>Pics</title></head><body>';
  echo '<h1 style="font-size:medium; border-left: 13px solid #003377; padding-left: 5px ">Photos from '.$cameralife->GetPref('sitename').'</h1>';

  $photos = new Search();
  $photos->SetPage(0, 3);
  $photos->SetSort('rand');
  foreach ($photos->GetPhotos() as $photo) {
    $icon = $photo->GetIcon();
    echo "          <a href=\"".$icon['href']."\" target=\"_new\">\n";
    echo "            <img style=\"border:0\" src=\"".$icon['image']."\" width=\"".$icon['width']."\" height=\"".$icon['height
']."\" ".
                      " alt=\"".htmlentities($icon['name'])."\" />\n";
    echo "          </a>\n";
  }
  echo '</body></html>';
