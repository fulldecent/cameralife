<?php
/**
 * @author Will Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @access public
 */

$cameralife->getFeature('security');
$cameralife->getFeature('fileStore');

if (isset($_REQUEST['path']) && $_REQUEST['path'] != 'upload/' . $cameralife->security->GetName() . '/') {
    $cameralife->security->Authorize('admin_file', 1);
    $path = $_REQUEST['path'];
} else {
    $cameralife->security->Authorize('photo_upload', 1);
    $path = 'upload/' . $cameralife->security->GetName() . '/';
}
$folder = new Folder($path);
$folderURL = $folder->getOpenGraph()['og:url'];

$headers = '<script src="' . $cameralife->baseURL . '/modules/theme/' . $cameralife->getPref(
        'theme'
    ) . '/dropzone.js"></script>';
$cameralife->theme->Header($cameralife, null, $headers); //TODO THIS IS INACCURATE
?>
<BGSOUND src="<?= $cameralife->baseURL ?>/filedone.wav">
    <h1>Uploading Photos</h1>
    <ul>
        <li>You can upload a photo in JPEG format or a ZIP of JPEG photos
        <li>The maximum file size is 20MB
            <?php
            if ($cameralife->security->Authorize('admin_file') && !isset($_REQUEST['path'])) {
                echo '    <li>To upload somewhere else, go to that folder and click "upload here"';
            }
            ?>
    </ul>

    <form id="dropzone" enctype="multipart/form-data" method=POST
          action="<?= $cameralife->baseURL ?>/upload_controller.php" class="form-horizontal dropzone">
        <input type="hidden" name="target" value="<?= $folderURL ?>">

        <div class="control-group">
            <label class="control-label" for="path">Path:</label>

            <div class="controls">
                <span class="input-xlarge uneditable-input"><i class="icon-folder-open"></i> <?= $path ?></span>
                <input name="path" type=hidden value="<?= $path ?>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="userfile">Photo file:</label>

            <div class="controls">
                <input name="userfile" type="file">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="description">Photo description:</label>

            <div class="controls">
                <input name="description" placeholder="Unnamed">
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn">Upload Photo</button>
            </div>
        </div>

    </form>

    <script>
        Dropzone.options.dropzone = { paramName: "userfile" };
    </script>
    <?php
    $cameralife->theme->Footer();
    ?>
