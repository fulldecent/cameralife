<?php
namespace CameraLife;
# $cameralife and $album are set by showPage()
/**
 *Editing an album
 *
 *<b>Note</b>  $cameralife and $album are set by showPage()
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2001-2009 William Entriken
 * @access public
 */
/**
 */

$cameralife->getFeature('security');
$cameralife->getFeature('fileStore');
$results = $album->getPhotos();
$counts = $album->getCounts();
$openGraph = $album->getOpenGraph();
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'rand';
$extrahead = '  <link rel="alternate" type="application/rss+xml" title="RSS feed of ' . $album->get(
        'name'
    ) . '" href="' . $cameralife->baseURL . '/rss.php&#63;q=' . $album->get('term') . '"/>';
$cameralife->theme->header($album, null, $extrahead);

if (isset($_GET['edit']) && $cameralife->security->authorize('admin_albums')) {
    ?>
    <div class="administrative" align=center>
        <form name="form1" method=post action="<?= $cameralife->baseURL ?>/album_controller.php">
            <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
            <input type="hidden" name="target"
                   value="<?= $cameralife->baseURL . '/album.php&#63;id=' . $album->get('id') ?>">

            <p class="info" align=left>
                You are editing the Album named <b><?= $album->get('name') ?></b>, which is a collection of photos
                whose descriptions contain "<b><?= $album->get('term') ?></b>".
                <a href="http://fdcl.sourceforge.net/wiki/index.php/Albums">Click here for more information about
                    albums</a>
            </p>
            <table>
                <tr>
                    <td>
                        The Album's name:
                    <td><input type="text" name="param1" value="<?= $album->get('name') ?>"><br>
                        <tr>
                            <td>
                                This album and others like it are:
                            <td>
                                <select name="param2">
                                    <?php
                                    foreach (Topic::getTopics() as $topic) {
                                        $thetopic = $topic->name;
                                        $selected = ($album->get('topic') == $thetopic) ? 'selected' : '';
                                        if ($album->get('topic') == $topic) {
                                            $preselect = true;
                                        }
                                        echo "<option $selected onclick=\"document.form1.topicother.disabled=true\" value=\"$thetopic\">$thetopic</option>\n";
                                    }
                                    ?>
                                    <option value="othertopic" <?= $preselect ? '' : 'selected' ?>
                                            onclick="document.form1.topicother.disabled=false">(A new topic)
                                    </option>
                                </select>
                                <input <?= $preselect ? 'disabled' : '' ?> type="text" name="param3" name="topicother"
                                                                           id="topicother" value="NEW TOPIC">
                <tr>
                    <td class="info">(Deleting this album will not delete the photos)
                    <td>
                        <input type=submit name="action" value="Update">
                        <input type=submit name="action" value="Delete">
                        <a href="<?= htmlentities($openGraph['og:url']) ?>">(cancel)</a>
            </table>
        </form>
    </div>
<?php
}
if (isset($_GET['poster_id'])) {
    $cameralife->security->authorize('admin_albums', 1);
    $album->setPoster($_GET['poster_id']);
    ?>
    <div class="administrative" align=center>
        The album <b><?= $album->get('name') ?></b> will be represented by this photo
        <img src="<?= htmlspecialchars($album->getPoster()->getMediaURL('thumbnail')) ?>" alt="Poster photo">
    </div>
<?php
}
?>
<div id="help" class="administrative"
     style="display:<?php echo isset($_GET['help']) ? 'block' : 'none' ?>; text-align:center">
    To add photos to this album, edit any photo on the site and add "<b><?= $album->get('term') ?></b>" to its
    description.
</div>
<?php
$cameralife->theme->grid($results);
$cameralife->theme->pageSelector(($sort == 'rand()') ? -1 : $start, $counts['photos'], 12, "id=" . $album->get('id'));
?>

<form method="POST">
    <p>
        Sort by <select name="sort">
            <?php
            $options = $album->sortOptions();
            foreach ($options as $option) {
                if (isset($option[2])) {
                    echo "    <option " . $option[2] . " value=\"" . $option[0] . "\">" . $option[1] . "</option>\n";
                } else {
                    echo "    <option value=\"" . $option[0] . "\">" . $option[1] . "</option>\n";
                }
            }
            ?>
        </select>
        <input type=submit value="Sort">
    </p>
</form>
<?php
$cameralife->theme->footer();
?>

