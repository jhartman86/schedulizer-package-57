<?php /** @var $eventObj \Concrete\Package\Schedulizer\Src\Event */
$attrList = \Concrete\Package\Schedulizer\Src\Attribute\Key\SchedulizerEventKey::getList();
$chunked  = array_chunk($attrList, 2);



if( !empty($attrList) ): foreach($chunked AS $pair): ?>
    <div class="row">
        <?php foreach($pair AS $attrKeyObj){ /** @var $attrKeyObj \Concrete\Core\Attribute\Key\Key */ ?>
            <div class="col-sm-6">
                <div class="form-group" data-type="<?php echo $attrKeyObj->getAttributeTypeHandle(); ?>">
                    <label><?php echo $attrKeyObj->getAttributeKeyName(); ?></label>
                    <?php echo $attrKeyObj->render('form', $eventObj->getAttributeValueObject($attrKeyObj), true); ?>
                </div>
            </div>
        <?php } ?>
    </div>
<?php endforeach; else: ?>
    <div class="row">
        <div class="col-sm-12">
            <p class="lead text-center">No Custom Attributes Setup Yet</p>
        </div>
    </div>
<?php endif; ?>