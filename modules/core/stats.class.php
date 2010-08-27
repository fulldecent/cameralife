<?php
  # the class for getting and using photos
  /**
  *Class Stats enables you to  get and use photos
  *@link http://fdcl.sourceforge.net
  *@version 2.6.3b5
  *@author Will Entriken <cameralife@phor.net>
  *@copyright Copyright (c) 2001-2009 Will Entriken
  *@access public
  */
  /**
  *For getting and using the photos
  */


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
    $this->counts['maxphotohits'] = $cameralife->Database->SelectOne('photos','MAX(hits)');
    $this->counts['maxalbumhits'] = $cameralife->Database->SelectOne('albums','MAX(hits)');
    $this->counts['daysonline'] = floor((time()-strtotime($cameralife->GetPref('sitedate'))) / 86400 );
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

    $funfacts[] = 'If these photos were taken with a film camera, they would have used <strong>'.
                  (round($this->counts['photos'] / 24, 0)).'</strong> rolls of film.';
    $funfacts[] = 'If the photos were layed on a football field, they would go up to the '.
                  '<strong>'.(round($this->counts['pixels'] / 358318080,2)).'</strong> yard line.';
                  # 358318080 = 160ft * 1 yd * 3ft/yd * 144 in^2/ft^2 * 5184 px^2/in^2
    $funfacts[] = 'If the photo pixels were layed 1-wide, they would circle '.
                  '<strong>'.(round($this->counts['pixels'] / 1135990288,2)).'%</strong> of the world.';
                  # 1135990288 = 3963.21mi * 2pi * 1760 yd/mi * 36 in/yd * 72 px/in / 100%
    $funfacts[] = 'If I had a nickel for every time someone looked at a picture here, I would have '.
                  '<strong>$'.(floor($this->counts['photohits'] / 20)).'</strong>.';
    $funfacts[] = 'There have been an average of '.
                  '<strong>'.(round ($this->counts['photos'] / ($this->counts['daysonline'] + 1),3)).'</strong> photos posted every day.';
    $funfacts[] = 'If you printed these photos and stacked them, they would be '.
                  '<strong>'.(round ($this->counts['photos'] / 60,2)).'</strong> inches high.';
    $funfacts[] = 'It would take '.
                  '<strong>'.(round ($this->counts['photos'] / 350,0)).' shoeboxes</strong> to store all these photos.';
    $funfacts[] = 'Printing these photos on an inkjet printer would use '.
                  '<strong>'.(round ($this->counts['photos'] / 11,0)).'</strong> cartridges costing '.
                  '<strong>$'.(round ($this->counts['photos'] / 11 * 13,0)).'</strong> retail.';
                  # http://www.epinions.com/content_141398871684
    $funfacts[] = 'Printing these photos with the leading online print service would cost '.
                  '<strong>$'.(round ($this->counts['photos'] * 0.15, 0)).'</strong>.';
                  # http://www.shutterfly.com/help/pop/pricing.jsp#volume
    $funfacts[] = 'Putting all these photos on your refrigerator will require '.
                  '<strong>'.(round ($this->counts['photos'] / 64, 0)).' refrigerators</strong>.';
                  # Model General Electric GTS18FBSWW
    $funfacts[] = 'Postage for mailing a photo here to each of your friends (like you have that many) will cost '.
                  '<strong>$'.(round ($this->counts['photos'] * 0.41, 2)).'</strong>.';
                  # http://www.usps.com/prices/welcome.htm
    return $funfacts;
  }
}
?>
