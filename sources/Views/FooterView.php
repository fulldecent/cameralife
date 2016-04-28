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
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
<!--
		<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script> 
		<script src="//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
-->		
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="//cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/js/bootstrap.js" crossorigin="anonymous"></script>
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
