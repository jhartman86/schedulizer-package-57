<?php /** @var $eventObj \Concrete\Package\Schedulizer\Src\Event */
$attrList = \Concrete\Package\Schedulizer\Src\Attribute\Key\SchedulizerEventKey::getList();
foreach($attrList AS $attrKeyObj){ ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label><?php echo $attrKeyObj->getAttributeKeyName(); ?></label>
                <?php echo $attrKeyObj->render('form', $eventObj->getAttributeValueObject($attrKeyObj), true); ?>
            </div>
        </div>
    </div>
<?php }