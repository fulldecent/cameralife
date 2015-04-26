<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * Shows the photos page
 *
 * @author    William Entriken <cameralife@phor.net>
 * @copyright 2001-2014 William Entriken
 * @access    public
 */
class PhotoView extends View
{
    /**
     * photo
     *
     * @var    Models\Photo
     * @access public
     */
    public $photo;

    /**
     * url of referring page, used to find other photos in context
     *
     * @var    string
     * @access public
     */
    public $referrer = null;

    /**
     * currentUser
     *
     * (default value: null)
     *
     * @var    Models\User
     * @access public
     */
    public $currentUser = null;

    /**
     * Render the view to standard output
     *
     * @access public
     * @return void
     */
    public function render()
    {
        $this->referrer = str_replace(constant('BASE_URL'), '', $this->referrer);
        $this->referrer = preg_replace('|^/|', '', $this->referrer);

        //todo, photo model needs to know referrer
        $photoPrev = $this->photo->getPrevious();
        $photoNext = $this->photo->getNext();

        // Get stuff related to the current user
        if ($this->currentUser->isLoggedIn) {
            $rating = $avg = Models\Database::selectOne(
                'ratings',
                'AVG(rating)',
                'id=' . $this->photo->get('id') . " AND username='" . $this->currentUser->name . "'"
            );
        } else {
            $rating = $avg = Models\Database::selectOne(
                'ratings',
                'AVG(rating)',
                'id=' . $this->photo->get('id') . " AND user_ip='" . $this->currentUser->remoteAddr . "'"
            );
        }
        ?>

<div class="row">
    <div class="col-md-8">
<h1>
    <form action="" method=POST name="form" class="pull-left">
        <input type="hidden" name="action" value="<?= $rating ? 'unfavorite' : 'favorite' ?>">
        <?php
            $count = $this->photo->getLikeCount();
            ?>
        <button type="submit" class="btn btn-link">
            <div class="stacked-icons">
                <span class="fa-stack fa-lg">
                    <i class="fa fa-star<?= $rating ? '' : '-o' ?> fa-stack-2x" style="color:gold"></i>
                    <strong class="fa-stack-1x" style="font-size:0.7em;color:black"><?= $count ? $count : '' ?></strong>
                </span>
            </div>
        </button>
    </form>

    <?= htmlentities($this->photo->get('description'), null, "UTF-8") ?>
    <a href="<?= $this->photo->getMediaURL('photo') ?>"
        id="showHideRenameForm"
        class="btn btn-sm btn-default"
        data-toggle="tooltip"
        data-placement="top"
        title="<?= $this->photo->get('width') ?> x <?= $this->photo->get('height') ?>px">
        <i class="fa fa-arrows-alt"></i>
    </a>
</h1>
<?php

$alt = htmlentities($this->photo->get('description'));
echo "<img id=\"curphoto\" class=\"img-thumbnail\" style=\"margin: 0 auto\" src=\"" . htmlspecialchars(
    $this->photo->getMediaURL(
        'scaled'
    )
) . "\" alt=\"$alt\">\n\n";

if (isset($nextOpenGraph['og:url'])) {
    echo '<a href="'.htmlspecialchars($nextOpenGraph['og:url']).'"><i class="fa fa-caret-square-o-right fa-4x"></i></a>';
}

?>

    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <h2>Comments</h2>
        <?php

        $result = Models\Database::select('comments', '*', 'photo_id=' . $this->photo->get('id'));
        while ($comment = $result->fetchAssoc()) {
            echo "<strong>" . $comment['username'] . "</strong> <em>" . date(
                'Y-m-d',
                strtotime($comment['date'])
            ) . "</em><br>" . htmlentities($comment['comment']) . "<hr>";
        }
        ?>
        <form action="" method=POST name="form">
            <input type="hidden" name="id" value="<?= $this->photo->get('id') ?>">
            <input type="hidden" name="action" value="comment">
            <input name="param1" class="form-control">
            <input type="submit" value="Post comment" class="btn">
        </form>
    </div>
    <div class="col-md-4">
        <h2>Information</h2>
        <dl class="dl-horizontal">
            <?php
            if ($this->photo->get('username')) {
                echo '         <dt>Author</dt><dd>' . $this->photo->get('username') . '</dd>';
            }

            if ($exif = $this->photo->getEXIF()) {
                foreach ($exif as $key => $val) {
                    if ($key == "Location") {
                        echo "         <dt>$key</dt><dd><a href=\"http://maps.google.com/maps?q=$val\">$val</a></dd>\n";
                    } else {
                        if ($key == "Camera Model") {
                            echo "         <dt>$key</dt><dd><a href=\"http://pbase.com/cameras/$val\">$val</a></dd>\n";
                        } else {
                            echo "         <dt>$key</dt><dd>$val</dd>\n";
                        }
                    }
                }
            }
            ?>
        </dl>
    </div>
</div>

<?php
// Cache the next image the user is likely to look at
if ($photoNext) {
    echo '<img style="display:none" src="' . htmlspecialchars(
        $photoNext->getMediaURL('scaled')
    ) . '" alt="hidden photo">';
}

    }
}
