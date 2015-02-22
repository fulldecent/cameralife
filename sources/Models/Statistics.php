<?php
namespace CameraLife\Models;

/**
 * Stats class.
 * get stat information about the whole photo collection
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */
class Statistics
{
    private $counts;

    /**
     * Returns information about the aggregate of photos on the site
     *
     * @access public
     * @return array
     */
    public function getCounts()
    {
        $this->counts['albums'] = Database::selectOne('albums', 'COUNT(*)');
        $this->counts['topics'] = Database::selectOne('albums', 'COUNT(DISTINCT topic)');
        $this->counts['photos'] = Database::selectOne('photos', 'COUNT(*)');
        $this->counts['pixels'] = Database::selectOne('photos', 'SUM(width*height)');
        $this->counts['albumhits'] = Database::selectOne('albums', 'SUM(hits)');
        $this->counts['photohits'] = Database::selectOne('photos', 'SUM(hits)');
        $this->counts['maxphotohits'] = Database::selectOne('photos', 'MAX(hits)');
        $this->counts['maxalbumhits'] = Database::selectOne('albums', 'MAX(hits)');
        $this->counts['daysonline'] = floor((time() - strtotime(Preferences::valueForModuleWithKey('CameraLife', 'sitedate'))) / 86400);
        return $this->counts;
    }

    /**
     * Get an array of the popular photos
     *
     * @access public
     * @return array
     */
    public function getPopularPhotos()
    {
        $popularPhotos = array();
        $query = Database::select('photos', 'id', null, 'ORDER BY hits DESC limit 5');
        while ($photo = $query->fetchAssoc()) {
            $popularPhotos[] = Photo::getPhotoWithID($photo['id']);
        }
        return $popularPhotos;
    }

    /**
     * Get an array of the popular tags
     *
     * @access public
     * @return array
     */
    public function getPopularTags()
    {
        $popularTags = array();
        $query = Database::select('albums', 'id', null, 'ORDER BY hits DESC limit 5');
        while ($row = $query->fetchAssoc()) {
            $popularTags[] = new Tag($row['id']);
        }
        return $popularTags;
    }

    /**
     * Get an array of fun facts (English text)
     *
     * @access public
     * @return array
     */
    public function getFunFacts()
    {
        if (empty($this->counts)) {
            $this->getCounts();
        }

        $funfacts[] = 'If these photos were taken with a film camera, they would have used <strong>' .
            (round($this->counts['photos'] / 24, 0)) . '</strong> rolls of film.';
        $funfacts[] = 'If the photos were laid on a football field, they would go up to the ' .
            '<strong>' . (round($this->counts['pixels'] / 358318080, 2)) . '</strong> yard line.';
        // 358318080 = 160ft * 1 yd * 3ft/yd * 144 in^2/ft^2 * 5184 px^2/in^2
        $funfacts[] = 'If the photo pixels were laid 1-wide, they would circle ' .
            '<strong>' . (round($this->counts['pixels'] / 1135990288, 2)) . '%</strong> of the world.';
        // 1135963699 = 24901mi * 63360in/mi * 72px/in / 100%
        $funfacts[] = 'If I had a nickel every time someone looked at a picture here, I would have ' .
            '<strong>$' . (floor($this->counts['photohits'] / 20)) . '</strong>.';
        $funfacts[] = 'There have been an average of ' .
            '<strong>' . (round(
                $this->counts['photos'] / ($this->counts['daysonline'] + 1),
                3
            )) . '</strong> photos posted every day.';
        $funfacts[] = 'If you printed these photos and stacked them, they would be ' .
            '<strong>' . (round($this->counts['photos'] / 60, 2)) . '</strong> inches high.';
        $funfacts[] = 'It would take ' .
            '<strong>' . (round($this->counts['photos'] / 350, 0)) . ' shoeboxes</strong> to store all these photos.';
        $funfacts[] = 'Printing these photos on an inkjet printer would use ' .
            '<strong>' . (round($this->counts['photos'] / 11, 0)) . '</strong> cartridges costing ' .
            '<strong>$' . (round($this->counts['photos'] / 11 * 13, 0)) . '</strong> retail.';
        // http://www.epinions.com/content_141398871684
        $funfacts[] = 'Printing these photos with the leading online print service would cost ' .
            '<strong>$' . (round($this->counts['photos'] * 0.15, 0)) . '</strong>.';
        // http://www.shutterfly.com/help/pop/pricing.jsp#volume
        $funfacts[] = 'Putting all these photos on your refrigerator will require ' .
            '<strong>' . (round($this->counts['photos'] / 64, 0)) . ' refrigerators</strong>.';
        // Model General Electric GTS18FBSWW
        $funfacts[] = 'Postage for mailing a photo here to each of your friends (like you have that many) will cost ' .
            '<strong>$' . (round($this->counts['photos'] * 0.49, 2)) . '</strong>.';
        // http://www.usps.com/prices/welcome.htm

        return $funfacts;
    }
}
