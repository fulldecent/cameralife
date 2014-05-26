<?php
namespace CameraLife;
/** View one topic page
 * NOTE: $cameralife and $topic are set by showPage()
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */

$cameralife->getFeature('security');
$cameralife->getFeature('fileStore');
$albums = $topic->getAlbums();
$counts = $topic->getCounts();
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

$cameralife->theme->header($topic);
?>
<form name="form1" method=post action="album_controller.php">
    <?php

    if (isset($_GET['edit'])) {
        $cameralife->security->authorize('admin_albums', 1);
        ?>
        <div class="administrative" align=center>
            <input type="hidden" name="param4" value="<?= htmlentities($_GET['term']) ?>">
            <input type="hidden" name="target" value="<?= $cameralife->baseURL ?>/index.php">

            <p class="info" align=left>
                You are about to create an Album of photos whose descriptions contain "<b><?=
                    htmlentities(
                        $_GET['term']
                    ) ?></b>".
                <a href="http://fdcl.sourceforge.net/wiki/index.php/Albums">Click here for more information about
                    albums</a>
            </p>
            <table>
                <tr>
                    <td>The Album's name:
                    <td><input type="text" name="param3" value="<?= htmlentities(ucwords($_GET['term'])) ?>">
                        <tr>
                            <td>This album and others like it are:
                            <td><select name="param1">
                                    <?php
                                    foreach (Topic::getTopics() as $topic) {
                                        $thetopic = $topic->name;
                                        $selected = ($album['topic'] == $thetopic) ? 'selected' : '';
                                        if ($album['topic'] == $thetopic) {
                                            $preselect = true;
                                        }
                                        echo "<option $selected onclick=\"document.form1.topicother.disabled=true\" value=\"$thetopic\">$thetopic</option>\n";
                                    }
                                    ?>
                                    <option value="othertopic" <?= $preselect ? '' : 'selected' ?>
                                            onclick="document.form1.topicother.disabled=false">(A new topic)
                                    </option>
                                </select>
                                <input <?= $preselect ? 'disabled' : '' ?> type="text" name="param2" name="topicother"
                                                                           id="topicother" value="NEW TOPIC">
                <tr>
                    <td>
                    <td>
                        <input type=submit name="action" value="Create">
                        <a href="search.php&#63;q=<?= urlencode($topic->getName()) ?>">(cancel)</a>
            </table>
        </div>
        <?php
        exit();
    }

    $cameralife->theme->grid($albums);
    $cameralife->theme->pageSelector($start, $counts['photos'], 12, "name=" . urlencode($_GET["name"]));
    ?>
</form>

<form method="POST">
    <p>
        Sort by <select name="sort">
            <?php
            $options = $topic->sortOptions();
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











