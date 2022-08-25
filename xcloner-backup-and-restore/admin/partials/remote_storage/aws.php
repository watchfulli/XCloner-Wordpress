<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="collapsible-header">
    <i class="material-icons">computer</i><?php echo __("Amazon S3 Storage", 'xcloner-backup-and-restore') ?>
    <div class="switch right">
        <label>
            Off
            <input type="checkbox" name="xcloner_aws_enable" class="status"
                   value="1" <?php if (get_option("xcloner_aws_enable")) {
                echo "checked";
            } ?> >
            <span class="lever"></span>
            On
        </label>
    </div>
</div>
<div class="collapsible-body">

    <div class="row">
        <div class="col s12 m3 label">
            &nbsp;
        </div>
        <div class=" col s12 m6">
            <p>
                <?php echo sprintf(__('Visit %s and get your "Key" and "Secret <br />Visit %s to install your own S3 like service.'), "<a href='https://aws.amazon.com/s3/' target='_blank'>https://aws.amazon.com/s3/</a>", "<a href='https://minio.io/' target='_blank'>https://minio.io/</a>") ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="aws_key"><?php echo __("S3 Key", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("S3 Key", 'xcloner-backup-and-restore') ?>" id="aws_key" type="text"
                   name="xcloner_aws_key" class="validate" value="<?php echo esc_attr(get_option("xcloner_aws_key")) ?>"
                   autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="aws_secret"><?php echo __("S3 Secret", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("S3 Secret", 'xcloner-backup-and-restore') ?>" id="aws_secret" type="text"
                   name="xcloner_aws_secret" class="validate"
                   value="<?php echo esc_attr(str_repeat('*', strlen(get_option("xcloner_aws_secret")))) ?>"
                   autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="aws_region"><?php echo __("S3 Region", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <select placeholder="<?php echo __("example: us-east-1", 'xcloner-backup-and-restore') ?>" id="aws_region"
                    type="text" name="xcloner_aws_region" class="validate"
                    value="<?php echo esc_attr(get_option("xcloner_aws_region")) ?>" autocomplete="off">
                <option readonly value="">
                    <?php echo __("Please Select AWS S3 Region or Leave Unselected for Custom Endpoint") ?>
                </option>
                <?php
                $aws_regions = $remote_storage->get_aws_regions();
                foreach ($aws_regions as $key => $region) { ?>
                    <option value="<?php echo esc_attr($key) ?>" <?php echo($key == get_option('xcloner_aws_region') ? "selected" : "") ?>>
                        <?php echo esc_html($region) ?>
                        = <?php echo esc_html($key) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div id="custom_aws_endpoint">
        <div class="row">
            <div class="col s12 m3 label">
                <label for="aws_endpoint"><?php echo __("S3 EndPoint", 'xcloner-backup-and-restore') ?></label>
            </div>
            <div class=" col s12 m6">
                <input
                        placeholder="<?php echo __("S3 EndPoint, leave blank if you want to use the default Amazon AWS Service", 'xcloner-backup-and-restore') ?>"
                        id="aws_endpoint" type="text" name="xcloner_aws_endpoint" class="validate"
                        value="<?php echo esc_attr(get_option("xcloner_aws_endpoint")) ?>" autocomplete="off">
            </div>
        </div>
        <div class="row">
            <div class="col s12 m3 label">
                <label for="aws_region"><?php echo __("S3 Custom Region", 'xcloner-backup-and-restore') ?></label>
            </div>
            <div class=" col s12 m6">
                <input placeholder="<?php echo __("S3 Custom Region, ex: af-south-1", 'xcloner-backup-and-restore') ?>"
                       id="aws_region" type="text" name="xcloner_aws_region" class="validate"
                       value="<?php echo esc_attr(get_option("xcloner_aws_region")) ?>" autocomplete="off">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="aws_bucket_name"><?php echo __("S3 Bucket Name", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input placeholder="<?php echo __("S3 Bucket Name", 'xcloner-backup-and-restore') ?>" id="aws_bucket_name"
                   type="text" name="xcloner_aws_bucket_name" class="validate"
                   value="<?php echo esc_attr(get_option("xcloner_aws_bucket_name")) ?>" autocomplete="off">
        </div>
    </div>

    <div class="row">
        <div class="col s12 m3 label">
            <label for="aws_prefix"><?php echo __("S3 Prefix", 'xcloner-backup-and-restore') ?></label>
        </div>
        <div class=" col s12 m6">
            <input
                    placeholder="<?php echo __("S3 Prefix, use / ending to define a folder", 'xcloner-backup-and-restore') ?>"
                    id="aws_prefix" type="text" name="xcloner_aws_prefix" class="validate"
                    value="<?php echo esc_attr(get_option("xcloner_aws_prefix")) ?>" autocomplete="off">
        </div>
    </div>

    <?php echo common_cleanup_html('aws') ?>

    <div class="row">
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light" type="submit" name="action" id="action"
                    value="aws"><?php echo __("Save Settings", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">save</i>
            </button>
        </div>
        <div class="col s6 m4">
            <button class="btn waves-effect waves-light orange" type="submit" name="action" id="action" value="aws"
                    onclick="jQuery('#connection_check').val('1')"><?php echo __("Verify", 'xcloner-backup-and-restore') ?>
                <i class="material-icons right">import_export</i>
            </button>
        </div>
    </div>

</div>
