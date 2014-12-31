<?php
namespace CameraLife\Views;

/**
 * Simple view for showing errors
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2014 William Entriken
 * @access public
 */
class ExceptionView extends View
{
//todo, make this phone home
    public $exception;

    public $showDebuggingInformation = true;

    public function render()
    {
        $message = $this->exception->getMessage();

        echo '<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">';
        echo '<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">';
        echo "<div class=\"alert\">\n";
        echo "  <h2><i class=\"fa fa-bomb\"></i> Camera Life has encountered an exception</h2>\n";
        echo "  <blockquote class=\"lead\">";
        echo htmlentities($message);
        echo "<footer>" . get_class($this->exception) . "</footer>";
        echo "</blockquote>\n";

        if ($this->showDebuggingInformation == false) {
            echo "</div>\n";
            return;
        }

        echo "  <hr><h3>Debugging information</h3>\n";
        $calls = $this->exception->getTrace();

        echo "  <dl>\n";
        foreach ($calls as $call) {
            $filePretty = str_replace(constant('BASE_DIR'), '', $call['file']);
            $fileHref = 'https://github.com/fulldecent/cameralife/blob/master' . $filePretty . '#L' . $call['line'];
            $fileHtml = $filePretty;
            if ($filePretty != $call['file']) {
                $fileHtml = "<a target=\"_new\" href=\"$fileHref\">$filePretty:{$call['line']}</a>";
            }
            $callHtml = $call['function'];
            if (isset($call['class'])) {
                $callHref = "http://camera.phor.net/docs/cameralife/{$call['class']}.html#{$call['function']}";
                $callHtml = "<a target=\"_new\" href=\"$callHref\">" . $call['class'] . '::' . $call['function'] . "()</a>";
            }

            echo "    <dt><i class=\"fa fa-file-o\"></i> $fileHtml<br><i class=\"fa fa-gear\"></i> $callHtml</dt>";

            echo '    <dd><ul>';
            if (count($call['args'])) {
                foreach ($call['args'] as $callarg) {
                    echo "<li>" . print_r($callarg, true) . "</li>";
                }
            }
            echo "</ul></dd>\n";
            #       echo '<dd><p>For object details view source...</p><!--<pre>';
            #       var_dump($call['object']);
            #       echo '--></pre></dd>';
        }
        echo "  </dl>\n";
        echo "<p><a href=\"https://github.com/fulldecent/cameralife/issues/new\" class=\"btn btn-primary\">Report to Camera Life Project</a></p>";
        echo "</div>\n";
    }
}