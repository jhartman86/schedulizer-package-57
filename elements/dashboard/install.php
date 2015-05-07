<?php $support = new Concrete\Package\Schedulizer\Src\Install\Support(); ?>
<style type="text/css">
    .support-table tbody tr td:nth-child(1){text-align:center;}
    .support-table tbody tr td:nth-child(1),
    .support-table tbody tr td:nth-child(2) {white-space:nowrap;}
    .support-table tbody tr td:last-child {width:99%;}
    .support-table tbody tr.success i {color:#3c763d;}
    .support-table tbody tr.danger i {color:#a94442;}
    .support-table p {margin-bottom:0;}
    .support-table pre {white-space:normal;margin-top:10px;}
    .support-table h4 {text-align:center;}
    .install-screen h5 {margin-top:0;padding-bottom:6px;border-bottom:1px dotted #ccc;}
</style>

<div class="install-screen container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <p>In order to provide robust support for timezones, Schedulizer makes use of some
                features sometimes not provided in shared hosting environments. It would take 3x the
                effort to build Schedulizer without using such features, and there is no plan to
                provide backwards compatibility with older systems. Depending on your hosting
                provider, you should be able to file a support ticket and request that a certain
                feature be provided, if it is missing. Tests on your system have been run below.</p>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <table class="support-table table table-striped table-condensed">
                <thead>
                <tr>
                    <th>Result</th>
                    <th>Requirement</th>
                    <th>Details</th>
                </tr>
                </thead>
                <tbody>
                <!-- php version test -->
                <tr class="<?php echo ($support->phpVersion()) ? 'success' : 'danger'; ?>">
                    <td><i class="fa <?php echo ($support->phpVersion()) ? 'fa-check' : 'fa-close'; ?>"></i></td>
                    <td>PHP Version 5.4&plus;</td>
                    <td>
                        <?php if( $support->phpVersion() ): ?>
                            <p>Your PHP version is up to snuff.</p>
                        <?php else: ?>
                            <p>Unfortunately your PHP version is not up to snuff. PHP Version 5.4 was released in 2013, you are running a significantly outdated version.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- mysql timezone support -->
                <tr class="<?php echo ($support->mysqlHasTimezoneTables()) ? 'success' : 'danger'; ?>">
                    <td><i class="fa <?php echo ($support->mysqlHasTimezoneTables()) ? 'fa-check' : 'fa-close'; ?>"></i></td>
                    <td>MySQL Timezone Support</td>
                    <td>
                        <?php if( $support->mysqlHasTimezoneTables() ): ?>
                            <p>MySQL Timezone Tables Installed</p>
                        <?php else: ?>
                            <p>Schedulizer requires that MySQL has <a href="http://dev.mysql.com/doc/refman/5.0/en/mysql-tzinfo-to-sql.html" target="_blank">timezone tables</a> installed in order to support conversions properly. If you are running
                                in a shared hosting environment (GoDaddy, BlueHost, Arvixe, etc.), your hosting provider should support this upon request.
                                Alternatively, if you administer your own server and have <i>root</i> access to the system, you can try running the following command:</p>
                            <pre>$: mysql_tzinfo_to_sql 2>/dev/null /usr/share/zoneinfo | mysql -u root --password={{root_password}} mysql 2>/dev/null</pre>
                            <p>whereas <code>{{root_password}}</code> should be replaced with your root password.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- php datetimezone stuff -->
                <tr class="<?php echo ($support->phpDateTimeZoneConversionsCorrect()) ? 'success' : 'danger'; ?>">
                    <td><i class="fa <?php echo ($support->phpDateTimeZoneConversionsCorrect()) ? 'fa-check' : 'fa-close'; ?>"></i></td>
                    <td>System Timezones</td>
                    <td>
                        <?php if( $support->phpDateTimeZoneConversionsCorrect() ): ?>
                            <p>PHP DateTimeZone Conversions Correct</p>
                        <?php else: ?>
                            <p>For some reason the PHP installation your site is running on is failing to convert between timezones accurately. This is most likely
                                related to poor configuration defaults by your hosting provider.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- php ordinal support -->
                <tr class="<?php echo ($support->phpDateTimeSupportsOrdinals()) ? 'success' : 'danger'; ?>">
                    <td><i class="fa <?php echo ($support->phpDateTimeSupportsOrdinals()) ? 'fa-check' : 'fa-close'; ?>"></i></td>
                    <td>PHP Date Ordinals</td>
                    <td>
                        <?php if( $support->phpDateTimeSupportsOrdinals() ): ?>
                            <p>Your PHP installation supports date time ordinal conversions correctly.</p>
                        <?php else: ?>
                            <p>For some reason the PHP installation your site is running on is failing to convert between timezones accurately. This is most likely
                                related to poor configuration defaults by your hosting provider.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
                <tfoot>
                <tr class="<?php echo $support->allPassed() ? 'success' : 'danger'; ?>">
                    <td colspan="3">
                        <?php if($support->allPassed()): ?>
                            <h4 class="text-success">All Installation Tests Passed, Good To Go</h4>
                        <?php else: ?>
                            <h4 class="text-danger">One Or More Installation Tests Failed</h4>
                        <?php endif; ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <h4>Schedulizer Settings</h4>
        </div>
    </div>
    <!-- configurable settings -->
    <?php Loader::packageElement('dashboard/config_settings', 'schedulizer'); ?>
</div>

<?php if( ! $support->allPassed() ){ ?>
    <script type="text/javascript">
        $(function(){
            var $form = $('form').attr('disabled', 'disabled').find('input[type="submit"]').remove();
        });
    </script>
<?php } ?>
