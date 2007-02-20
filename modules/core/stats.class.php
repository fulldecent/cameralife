<?php
  # the class for getting and using photos
  
class Stats
{
  var $counts;

  function Stats()
  {
    $this->counts = array();
  }

  function GetCounts()
  {
    global $cameralife;

    $this->counts['albums'] = $cameralife->Database->SelectOne('albums','COUNT(*)');
    $this->counts['topics'] = $cameralife->Database->SelectOne('albums','COUNT(DISTINCT topic)');
    $this->counts['photos'] = $cameralife->Database->SelectOne('photos','COUNT(*)');
    $this->counts['pixels'] = $cameralife->Database->SelectOne('photos','SUM(width*height)');
    $this->counts['albumhits'] = $cameralife->Database->SelectOne('albums','SUM(hits)');
    $this->counts['photohits'] = $cameralife->Database->SelectOne('photos','SUM(hits)');
    $this->counts['maxalbumhits'] = $cameralife->Database->SelectOne('albums','MAX(hits)');
    $this->counts['daysonline'] = floor((time()-strtotime($cameralife->preferences['core']['sitedate'])) / 86400 );
    return $this->counts;
  }

  function GetPopularPhotos()
  {
    global $cameralife;

    $popular_photos = array();
    $query = $cameralife->Database->Select('photos','id',NULL,'ORDER BY hits DESC limit 5');
    while ($photo = $query->FetchAssoc())
      $popular_photos[] = new Photo($photo['id']);
    return $popular_photos;
  }

  function GetPopularAlbums()
  {
    global $cameralife;

    $popular_albums = array();
    $query = $cameralife->Database->Select('albums','id',NULL,'ORDER BY hits DESC limit 5');
    while ($album = $query->FetchAssoc())
      $popular_albums[] = new Album($album['id']);
    return $popular_albums;
  }

  function GetFunFacts()
  {
    if (empty($this->counts))
      $this->GetCounts();

    $funfacts[] = 'If these photos were taken with a film camera, they would have used '.(round($this->counts['photos'] / 24), 0).' rolls of film.';
    $funfacts[] = 'If the photos were layed on a football field, they would go up to the '.
                  '<b>'.(round($this->counts['pixels'] / 358318080,2)).'</b> yard line.';
                  # 358318080 = 160ft * 1 yd * 3ft/yd * 144 in^2/ft^2 * 5184 px^2/in^2
    $funfacts[] = 'If the photo pixels were layed 1-wide, they would circle '.
                  '<b>'.(round($this->counts['pixels'] / 1135990288,2)).'%</b> of the world.';
                  # 1135990288 = 3963.21mi * 2pi * 1760 yd/mi * 36 in/yd * 72 px/in / 100%
    $funfacts[] = 'If I had a nickel for every time someone looked at a picture here, I would have '.
                  '<b>$'.(floor($this->counts['photohits'] / 20)).'</b>.';
    $funfacts[] = 'There have been an average of '.
                  '<b>'.(round ($this->counts['photos'] / ($this->counts['daysonline'] + 1),3)).'</b> photos posted every day.';
    $funfacts[] = 'If you printed these photos and stacked them, they would be '.
                  '<b>'.(round ($this->counts['photos'] / 60,2)).'</b> inches high.';
    $funfacts[] = 'It would take '.
                  '<b>'.(round ($this->counts['photos'] / 350,0)).' shoeboxes</b> to store all these photos.';
    $funfacts[] = 'Printing these photos on an inkjet printer would use '.
                  '<b>'.(round ($this->counts['photos'] / 11,0)).'</b> cartridges costing '.
                  '<b>$'.(round ($this->counts['photos'] / 11 * 13,0)).'</b> retail.';
                  # http://www.epinions.com/content_141398871684
    $funfacts[] = 'Printing these photos with the leading online print service would cost '.
                  '<b>$'.(round ($this->counts['photos'] * 0.15, 0)).'</b>.';
                  # http://www.shutterfly.com/help/pop/pricing.jsp#volume
    $funfacts[] = 'Putting all these photos on your refrigerator will require '.
                  '<b>'.(round ($this->counts['photos'] / 64, 0)).' refrigerators</b>.';
                  # Model General Electric GTS18FBSWW
    $funfacts[] = 'Postage for mailing a photo here to each of your friends (like you have that many) will cost '.
                  '<b>$'.(round ($this->counts['photos'] * 0.39, 2)).'</b>.';
                  # Model General Electric GTS18FBSWW
    return $funfacts;
  }
}
?>
