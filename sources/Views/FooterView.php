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

    public function render()
    {
        ?>
        <hr>

        <footer>
            <p>
                <a href="mailto:<?= htmlspecialchars($this->ownerEmail) ?>"><i class="fa fa-envelope"></i> Contact site
                    owner</a>
                &nbsp;
                <a href="<?= htmlspecialchars($this->statsUrl) ?>"><i class="fa fa-signal"></i> Site stats</a>
                &nbsp;
                <a href="http://fulldecent.github.io/cameralife"><i class="fa fa-globe"></i> Built with Camera Life</a>
            </p>
        </footer>

        </div>

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

        <?php
        if (!empty($this->analyticsId)) {
            ?>
            <!--TRACKING CODE-->
            <script type="text/javascript">
                var _gaq = _gaq || [];
                _gaq.push(['_setAccount', '<?= $this->analyticsId ?>']);
                _gaq.push(['_trackPageview']);

                (function () {
                    var ga = document.createElement('script');
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(ga, s);
                })();
            </script>
            <!--END TRACKING CODE-->
        <?php
        }
        ?>
        </body>
        </html>
    <?php
    }
}

