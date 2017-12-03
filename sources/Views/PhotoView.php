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
     * openGraphObject
     *
     * @var    Models\OpenGraphObject
     * @access public
     */
    public $openGraphObject;

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
     * url of referring page, used to find other photos in context
     *
     * @var    string
     * @access public
     */
    public $contextUrl = null;

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
        if ($this->photo->get('status') != 0) {
            echo '<p class="alert alert-danger lead"><strong>Notice:</strong> This photo is not publicly viewable</p>';
        }

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

		<nav class="navbar navbar-light bg-faded fixed-bottom" style="background:rgba(255,255,255,0.4)">
			<div class="container">
				<form class="form-inline pull-xs-left" method=POST name="form" style="margin-right:10px">
					<input type="hidden" name="action" value="<?= $rating ? 'unfavorite' : 'favorite' ?>">
					<?php $count = $this->photo->getLikeCount(); ?>
					<button class="btn btn-link" type="submit" style="padding:2px">
				        <span class="fa-stack">
				            <i class="fa fa-star<?= $rating ? '' : '-o' ?> fa-stack-2x" style="color:gold"></i>
				            <strong class="fa-stack-1x" style="font-size:0.7em;color:black"><?= $count ? $count : '' ?></strong>
				        </span>
					</button>
				</form>
			    <a href="<?= $this->photo->getMediaURL('photo') ?>"
			        class="btn btn-link pull-xs-left"
			        title="<?= $this->photo->get('width') ?> x <?= $this->photo->get('height') ?>px"
					style="margin-right:10px"
				>
			        <i class="fa fa-arrows-alt"></i>
			    </a>
			    <a href="<?= $this->contextUrl ?>"
			        class="btn btn-link pull-xs-left"
			        title="Close"
					style="margin-right:10px"
				>
			        <i class="fa fa-times"></i>
			    </a>
		        <span class="navbar-brand"><?= htmlspecialchars($this->openGraphObject->title) ?></span>
			</div>
		</nav>

<div
	id="mainPic"
	style="position:absolute;top:0;left:0;width:100%;height:100%;background:url(<?= $this->photo->getMediaURL('scaled') ?>);background-size:contain;background-repeat:no-repeat;background-position:center"
>

	<img
		src="<?= $this->photo->getMediaURL('scaled') ?>"
		alt="<?= htmlentities($this->photo->get('description')) ?>"
		style="display:none"
	>
</div>

<div class="container" style="position:absolute;top:100%;height:100%;">
        <h3>Information</h3>
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

<?php
// Cache the next image the user is likely to look at
if ($photoNext) {
    echo '<img style="display:none" src="' . htmlspecialchars(
        $photoNext->getMediaURL('scaled')
    ) . '" alt="hidden photo">';
}

    }
}
