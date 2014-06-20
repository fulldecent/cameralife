<?php
namespace CameraLife;
/**
 * Photo view
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */

# $cameralife and $photo are set by showPage()
# Display a photo, edit a photo, link to relevant places

$cameralife->getFeature('security');
$cameralife->getFeature('fileStore');
$cameralife->getFeature('imageProcessing');
$openGraph = $photo->getOpenGraph();

if (isset($_GET['referer'])) {
    $_SERVER['HTTP_REFERER'] = $_GET['referer'];
}
if (isset($_SERVER['HTTP_REFERER'])) {
    $_SERVER['HTTP_REFERER'] = str_replace($cameralife->baseURL, '', $_SERVER['HTTP_REFERER']);
    $_SERVER['HTTP_REFERER'] = preg_replace('|^/|', '', $_SERVER['HTTP_REFERER']);
} else {
    $_SERVER['HTTP_REFERER'] = '';
}

# Handy stuff
if (isset($_SERVER['HTTP_REFERER'])) {
    //TODO USE parse-str and http_build_query
    if (strpos($openGraph['og:url'], '?')) {
        $openGraph['og:url'] .= '&referer=' . urlencode($_SERVER['HTTP_REFERER']);
    } else {
        $openGraph['og:url'] .= '?referer=' . urlencode($_SERVER['HTTP_REFERER']);
    }
}
$photoPrev = $photo->getPrevious();
$photoNext = $photo->getNext();

if ($cameralife->security->getName()) {
    $rating = $avg = $cameralife->database->SelectOne(
        'ratings',
        'AVG(rating)',
        'id=' . $photo->get('id') . " AND username='" . $cameralife->security->getName() . "'"
    );
} else {
    $rating = $avg = $cameralife->database->SelectOne(
        'ratings',
        'AVG(rating)',
        'id=' . $photo->get('id') . " AND user_ip='" . $_SERVER['REMOTE_ADDR'] . "'"
    );
}

## Extra header...
$headers = '<link rel="stylesheet" type="text/css" href="' . $cameralife->baseURL . '/modules/theme/' . $cameralife->getPref(
        'theme'
    ) . '/delicious.css" />' . PHP_EOL;

if ($photoPrev) {
    $prevOpenGraph = $photoPrev->GetOpenGraph();
    if (strpos($prevOpenGraph['og:url'], '?')) {
        $prevOpenGraph['og:url'] .= '&referer=' . urlencode($_SERVER['HTTP_REFERER']);
    } else {
        $prevOpenGraph['og:url'] .= '?referer=' . urlencode($_SERVER['HTTP_REFERER']);
    }
    $headers .= '<link rel="prev" href="' . htmlspecialchars($prevOpenGraph['og:url']) . '" />' . PHP_EOL;
}
if ($photoNext) {
    $nextOpenGraph = $photoNext->GetOpenGraph();
    if (strpos($nextOpenGraph['og:url'], '?')) {
        $nextOpenGraph['og:url'] .= '&referer=' . urlencode($_SERVER['HTTP_REFERER']);
    } else {
        $nextOpenGraph['og:url'] .= '?referer=' . urlencode($_SERVER['HTTP_REFERER']);
    }
    $headers .= '<link rel="next" href="' . htmlspecialchars($nextOpenGraph['og:url']) . '" />' . PHP_EOL;
}

$headers .= '<script type="text/javascript" src="' . $cameralife->baseURL . '/modules/theme/' . $cameralife->getPref(
        'theme'
    ) . '/photo.js"></script>';
$headers .= '<script type="text/javascript" src="' . $cameralife->baseURL . '/modules/theme/' . $cameralife->getPref(
        'theme'
    ) . '/ajax.js"></script>';

$cameralife->theme->header($photo, null, $headers);

if ($photo->get('status') > 0) {
    ?>
    <div class="alert">This photo is not public</div>
<?php
}
?>

<div class="row">
    <div class="col-md-8">
<h1>
    <form action="<?= $cameralife->baseURL ?>/photo_controller.php" method=POST name="form" class="pull-left">
        <input type="hidden" name="id" value="<?= $photo->get('id') ?>">
        <input type="hidden" name="target" value="<?= htmlspecialchars($openGraph['og:url']) ?>">
        <input type="hidden" name="action" value="rate">
        <?php
            if ($rating == 5) {
                echo "<button name=\"param1\" value=\"0\" type=\"submit\" class=\"btn btn-link\" style=\"color:gold\">";
                echo "<i class=\"fa fa-star fa-2x\"></i>\n";
                echo "</button>\n";
            } else {
                echo "<button name=\"param1\" value=\"5\" type=\"submit\" class=\"btn btn-link\" style=\"color:gold\">";
                echo "<i class=\"fa fa-star-o fa-2x\"></i>\n";
                echo "</button>\n";
            }
        ?>
    </form>

    <?= htmlentities($photo->get('description'), null, "UTF-8") ?>
    <?php
    if ($cameralife->security->authorize('photo_rename')) {
        ?>
        <a href="<?= $cameralife->theme->addParam($_SERVER['REQUEST_URI'], 'action', 'rename') ?>"
           id="showHideRenameForm"
           class="btn btn-sm btn-default"
           style="<?= (isset($_GET['action']) && $_GET['action'] == 'rename') ? 'display:none' : '' ?>"
           onclick="$('#showHideRenameForm').hide();$('#renameform').show();$('#formtitle').focus();return false">rename</a>
    <?php
    }
    ?>
    <a href="<?= $photo->getMediaURL('photo') ?>" id="showHideRenameForm" class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="<?= $photo->get('width') ?> x <?= $photo->get('height') ?>px">
        <i class="fa fa-arrows-alt"></i>
    </a>
    <?php if ($cameralife->security->authorize('photo_delete')) { ?>
        <a href="<?= $cameralife->theme->addParam($_SERVER['REQUEST_URI'], 'action', 'flag') ?>"
          class="btn btn-sm btn-default" 
          data-toggle="tooltip" 
          data-placement="top" 
          title="Flag photo"
          onClick="$('#deleteform').show();return false"
        >
            <i class="fa fa-flag"></i>
        </a>
    <?php } ?>
    <?php if ($cameralife->security->authorize('photo_modify')) { ?>
        <a href="<?= $cameralife->theme->addParam($_SERVER['REQUEST_URI'], 'action', 'modify') ?>"
          class="btn btn-sm btn-default" 
          data-toggle="tooltip" 
          data-placement="top" 
          title="Modify photo (rotate, revert, ...)"
          onClick="$('#modify').show();return false"
        >
            <i class="fa fa-wrench"></i>
        </a>
    <?php } ?>
    <?php if ($photo->get('status') == 0) { ?>
        <a href="<?= $cameralife->theme->addParam($_SERVER['REQUEST_URI'], 'action', 'print') ?>"
          class="btn btn-sm btn-default" 
          data-toggle="tooltip" 
          data-placement="top" 
          title="Order prints"
          onClick="$('#modify').show();return false"
        >
            <i class="fa fa-print"></i>
        </a>
        <a href="http://www.facebook.com/sharer.php?u=<?= rawurlencode(htmlspecialchars($openGraph['og:url'])) ?>"
          class="btn btn-sm btn-default" 
          data-toggle="tooltip" 
          data-placement="top" 
          title="Share on Facebook"
        >
            <i class="fa fa-star"></i>
        </a>
    <?php } ?>    
</h1>
<?php

if ($cameralife->security->authorize('photo_rename')) {
    ?>
    <form action="<?= $cameralife->baseURL ?>/photo_controller.php" method="POST" name="form" id="renameform"
          class="well form-horizontal"
          style="<?= (isset($_GET['action']) && $_GET['action'] == 'rename') ? '' : 'display:none' ?>">
        <p class="lead">The <strong>Title</strong> shows on this page and thumbnails of this photo elsewhere. <strong>Tags</strong>
            are never displayed. Searches and Albums use the <strong>Title</strong> and <strong>Tags</strong> to
            find photos.</p>
        <input type="hidden" name="id" value="<?= $photo->get('id') ?>">
        <input type="hidden" name="target" value="<?= htmlspecialchars($openGraph['og:url']) ?>">
        <input type="hidden" name="action" value="rename">

        <div class="form-group">
            <label for="formtitle" class="col-sm-2 control-label">Title</label>

            <div class="col-sm-10">
                <input type="text" class="form-control" id="formtitle" placeholder="Title" name="param1"
                       value="<?= htmlentities($photo->get('description'), null, "UTF-8") ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="tags" class="col-sm-2 control-label">Tags</label>

            <div class="col-sm-10">
                <input type="text" class="form-control" id="tags" placeholder="Tags" name="param2"
                       value="<?= htmlentities($photo->get('tags'), null, "UTF-8") ?>">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">

                <input class="btn btn-primary" type="submit" value="Save Changes"
                       onclick="makeRequest('<?= $cameralife->baseURL ?>/photo_controller.php','renameform',function(){$('h1').html($('form#renameform input[name=param1]').val())};return false">
                <a class="btn btn-default" href="<?= htmlspecialchars($openGraph['og:url']) ?>"
                   onclick="$('#showHideRenameForm').show();$('#renameform').hide();return false">Cancel</a>

            </div>
        </div>
    </form>
<?php
}
if ($cameralife->security->authorize('photo_delete')) {
    if ($photoNext) {
        $target = $nextOpenGraph['og:url'];
    } elseif ($photoPrev) {
        $target = $prevOpenGraph['og:url'];
    } elseif (isset($_SERVER['HTTP_REFERER'])) {
        $target = $cameralife->baseURL . '/' . $_SERVER['HTTP_REFERER'];
    } else {
        $target = $cameralife->baseURL . '/index.php';
    }

    ?>
    <form action="<?= $cameralife->baseURL ?>/photo_controller.php" method="POST" name="form" id="deleteform"
          class="well form-horizontal"
          style="<?= (isset($_GET['action']) && $_GET['action'] == 'flag') ? '' : 'display:none' ?>">
        <input type="hidden" name="id" value="<?= $photo->get('id') ?>"/>
        <input type="hidden" name="target" value="<?=
        $cameralife->theme->addParam(
            htmlspecialchars($openGraph['og:url']),
            'referer',
            urlencode($_SERVER['HTTP_REFERER'])
        ) ?>"/>
        <input type="hidden" name="action" value="flag"/>

        <p class="lead">Thank you for taking the time to report bad content. Please choose the obvious problem with this
            photo.</p>

        <label class="radio">
            <input type="radio" name="param1" value="indecent">
            <strong>Indecent or Sensitive</strong> / Obsenity, nudity
        </label>
        <label class="radio">
            <input type="radio" name="param1" value="photography">
            <strong>Bad photography</strong> / Blurry, poor lighting, terrible cropping
        </label>
        <label class="radio">
            <input type="radio" name="param1" value="subject">
            <strong>Poor subject</strong> / The subject is uninteresting, eg: dirt
        </label>
        <label class="radio">
            <input type="radio" name="param1" value="bracketing">
            <strong>Bracketing</strong> / A very similar photo supersedes this one
        </label>
        <input class="btn btn-primary btn-danger" type="submit" value="Flag Photo">
        <a class="btn btn-default" href="<?= htmlspecialchars($openGraph['og:url']) ?>"
           onclick="$('#delete').hide();return false">Cancel</a>
    </form>
<?php
}
if ($cameralife->security->authorize('photo_modify')) {
    ?>
    <div id="modify" class="well"
         style="<?= (isset($_GET['action']) && $_GET['action'] == 'modify') ? '' : 'display:none' ?>">
        <p>Modify photo. All modifications can be reverted.</p>

        <form class="form-horizontal" action="<?= $cameralife->baseURL ?>/photo_controller.php" method="POST">
            <input type="hidden" name="id" value="<?= $photo->get('id') ?>"/>
            <input type="hidden" name="target" value="<?= htmlspecialchars($openGraph['og:url']) ?>"/>
            <input type="hidden" name="action" value="rotate"/>

            <div class="control-group">
                <label class="control-label" for="tags">rotate</label>

                <div class="controls">
                    <input class="btn" type="submit" name="param1" value="rotate Counter-Clockwise">
                    <input class="btn" type="submit" name="param1" value="rotate Clockwise">
                </div>
            </div>
        </form>

        <form class="form-horizontal" action="<?= $cameralife->baseURL ?>/photo_controller.php" method="POST">
            <input type="hidden" name="id" value="<?= $photo->get('id') ?>"/>
            <input type="hidden" name="target" value="<?= htmlspecialchars($openGraph['og:url']) ?>"/>
            <input type="hidden" name="action" value="revert"/>

            <div class="control-group">
                <label class="control-label" for="tags">Revert</label>

                <div class="controls">
                    <input class="btn" type="submit" name="param1" value="Revert">
                </div>
            </div>
        </form>

        <form class="form-horizontal" action="<?= $cameralife->baseURL ?>/photo_controller.php" method="POST">
            <input type="hidden" name="id" value="<?= $photo->get('id') ?>"/>
            <input type="hidden" name="target" value="<?= htmlspecialchars($openGraph['og:url']) ?>"/>
            <input type="hidden" name="action" value="rethumb"/>

            <div class="control-group">
                <label class="control-label" for="tags">Regenerate Thumbnail</label>

                <div class="controls">
                    <input class="btn" type="submit" name="param1" value="Regenerate Thumbnail">
                </div>
            </div>
        </form>

        <div class="form-horizontal">
            <div class="control-group">
                <div class="controls">
                    <a class="btn" href="<?= htmlspecialchars($openGraph['og:url']) ?>"
                       onclick="$('#modify').hide();return false">Cancel</a>
                </div>
            </div>
        </div>
    </div>

<?php
}
if ($photo->get('status') == 0) {
    $json = '{"request":{"merchant":{"merchant-api-key":"","merchant-button-key":"13658928432161657"},"order":{"product":{"thumbnail":{"src":"' . htmlspecialchars(
            $photo->getMediaURL(
                'thumbnail'
            )
        ) . '","width":"0","height":"0"},"source":{"file":{"dimensions":{"width":"' . $photo->get(
            'width'
        ) . '","height":"' . $photo->get(
            'height'
        ) . '","bleed":"0.3"},"usage":"full-product","filetype":"image","src":"' . htmlspecialchars(
            $photo->getMediaURL(
                'photo'
            )
        ) . '","publicationId":"","pages":"1"}},"target":{"offering":{"id":"0"}},"title":""},"reference":""},"redirect":{"cancellation":{"href":""},"error":{"href":""},"thankyou":{"href":"' . $cameralife->baseURL . '"}},"quantity":"1","currency":"USD","locale":"en_EN","filterCategory":"","filterSize":"","filterColour":"","filterOfferingId":"0"}}';
    ?>
    <div id="print" class="well"
         style="<?= (isset($_GET['action']) && $_GET['action'] == 'print') ? '' : 'display:none' ?>">
        <p class="lead">Order postcards, canvas prints and more styles of this photo.</p>

        <form action="http://secure.print.peecho.com/button/checkout/pay" method="post">
            <input type="hidden" name="orderRequest" value="<?= htmlentities($json) ?>">
            <input type="hidden" name="format" value="json">
            <input type="hidden" name="apiType" value="spb">
            <button class="btn btn-primary">Order prints</button>
        </form>
    </div>

<?php
}

if (isset($cameralife->receipt)) {
    echo '<div id="receipt" class="receipt">';
    echo '<form action="' . $cameralife->baseURL . '/undo_controller.php" method="post">';
    echo '<input id="receiptid" type="hidden" name="id" value="' . $cameralife->receipt->Get('id') . '" />';
    echo '<input type="hidden" name="action" value="undo" />';
    echo '<input type="hidden" name="target" value="' . htmlspecialchars($openGraph['og:url']) . '" />';
    echo $cameralife->receipt->GetDescription() . ' <button type="submit">Undo</button></form></div>';
} else {
    echo '<div id="receipt" class="receipt" style="visibility: hidden"></div>';
}

echo '<a href="'.htmlspecialchars($prevOpenGraph['og:url']).'"><i class="fa fa-caret-square-o-left fa-4x"></i></a>';

$alt = htmlentities($photo->get('description'));
echo "<img id=\"curphoto\" class=\"img-thumbnail\" style=\"margin: 0 auto\" src=\"" . htmlspecialchars(
        $photo->getMediaURL(
            'scaled'
        )
    ) . "\" alt=\"$alt\">\n\n";

echo '<a href="'.htmlspecialchars($nextOpenGraph['og:url']).'"><i class="fa fa-caret-square-o-right fa-4x"></i></a>';

?>

    </div>
    <div class="col-md-4">
        <h2>Context</h2>
        <?php
        $photo->getContext(); /// TODO TEMPORARY HACK
        $context = $photo->context;
        $og = $context->getOpenGraph();
        $hHref = htmlspecialchars($og['og:url']);
        $hDescription = htmlspecialchars($og['og:title']);
        echo "<p><a href=\"$hHref\">$hDescription</a></p>";
//        var_dump($og, $context);
        ?>
        <?php
        foreach (array_slice($photo->getContext(), 0, 48) as $contextPhoto) {
            $og = $contextPhoto->getOpenGraph();
            $class = $og['og:image:width'] > $og['og:image:height'] ? 'croph' : 'cropv';
            $hHref = htmlspecialchars($og['og:url']);
            $hSrc = htmlspecialchars($contextPhoto->getMediaURL('thumbnail'));
            $hDescription = htmlspecialchars($contextPhoto->get('description'));
            $hStyle = $contextPhoto->get('id') == $photo->get('id') ? 'border:7px solid gray' : '';
            echo "<a href=\"$hHref\" style=\"$hStyle\" class=\"$class pull-left \"><img src=\"$hSrc\" alt=\"$hDescription\"></a>";
        }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <h2>Comments</h2>
        <?php

        $result = $cameralife->database->Select('comments', '*', 'photo_id=' . $photo->get('id'));
        while ($comment = $result->fetchAssoc()) {
            echo "<strong>" . $comment['username'] . "</strong> <em>" . date(
                    'Y-m-d',
                    strtotime($comment['date'])
                ) . "</em><br>" . htmlentities($comment['comment']) . "<hr>";
        }
        ?>
        <form action="<?= $cameralife->baseURL ?>/photo_controller.php" method=POST name="form">
            <input type="hidden" name="id" value="<?= $photo->get('id') ?>">
            <input type="hidden" name="target" value="<?= htmlspecialchars($openGraph['og:url']) ?>">
            <input type="hidden" name="action" value="comment">
            <input name="param1" class="form-control">
            <input type="submit" value="Post comment" class="btn">
        </form>
    </div>
    <div class="col-md-4">
        <h2>Information</h2>
        <dl class="dl-horizontal">
            <?php
            if ($photo->get('username')) {
                echo '         <dt>Author</dt><dd>' . $photo->get('username') . '</dd>';
            }

            if ($exif = $photo->getEXIF()) {
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
# Cache the next image the user is likely to look at
if ($photoNext) {
    echo '<img style="display:none" src="' . htmlspecialchars(
            $photoNext->getMediaURL('scaled')
        ) . '" alt="hidden photo">';
}

$cameralife->theme->footer();
?>
