<?php
namespace CameraLife\Views;

use CameraLife\Models as Models;

/**
 * Theme name - Bootstrap
 * @author William Entriken<cameralife@phor.net>
 * @access public
 * @copyright 2014 William Entriken
 * @todo make this HTML valid
 */
class FooterView extends View
{
    public $ownerEmail;
    public $statsUrl;
    public $analyticsId;
    public $extraJavascript;
    public $mainPageOpenGraph;

    public function render()
    {
        ?>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js" integrity="sha256-eVNjHw5UeU0jUqPPpZHAkU1z4U+QFBBY488WvueTm88=" crossorigin="anonymous"></script>
        <?php
        if (!empty($this->extraJavascript)) {
        ?>
        <script type="text/javascript">
<?= $this->extraJavascript ?>
        </script>
        <?php
        }

        if (!empty($this->analyticsId)) {
            ?>
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $this->analyticsId ?>"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', '<?= $this->analyticsId ?>');
            </script>
        <?php
        }
        ?>
        </body>
        </html>
    <?php
    }
}
