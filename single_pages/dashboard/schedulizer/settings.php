<form method="post" class="container-fluid" action="<?php echo $this->action('save'); ?>">
    <?php Loader::packageElement('dashboard/config_settings', 'schedulizer'); ?>
    <div class="row">
        <div class="col-sm-12">
            <button type="submit" class="btn btn-success btn-block">Save</button>
        </div>
    </div>
</form>