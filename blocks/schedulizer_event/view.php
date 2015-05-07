<?php
    /** @var $eventObj Concrete\Package\Schedulizer\Src\Event */
    /** @var $eventListObj Concrete\Package\Schedulizer\Src\EventList */
    // If no event obj, bail out
    if( ! is_object($eventObj) ){
        echo 'No event selected'; return;
    }
    // Otherwise, get the results from the list...
    $eventResults = $eventListObj->get();
    $first10      = array_slice($eventResults, 0, 10);
    $restResults  = array_slice($eventResults, 10);
?>

<style type="text/css">
    .schedulizer-event {}
    .schedulizer-event ul li span {display:block;padding:0.5rem 1rem;background:#f1f1f1;border-bottom:1px solid #e1e1e1;}
    .schedulizer-event [expandable] {display:none;}
    .schedulizer-event [show-expanded] {display:block;padding:0.5rem 1rem;cursor:pointer;}
</style>

<div id="schedulizer-event-<?php echo $this->controller->bID; ?>" class="schedulizer-event container-fluid">
    <div class="row">
        <div class="col-sm-8">
            <h2><?php echo $eventObj; ?></h2>
            <div>
                <?php echo $eventObj->getDescription(); ?>
            </div>
        </div>
        <div class="col-sm-4">
            <h3>Event Time(s)</h3>
            <ul class="list-unstyled">
                <?php foreach($first10 AS $result): ?>
                    <li><span><?php echo (new DateTime($result['computedStartLocal']))->format('g:i a (n/j/y)') ?></span></li>
                <?php endforeach; ?>
                <?php if(!empty($restResults)): ?>
                    <?php foreach($restResults AS $result): ?>
                    <li expandable><span><?php echo (new DateTime($result['computedStartLocal']))->format('g:i a (n/j/y)') ?></span></li>
                    <?php endforeach; ?>
                    <li><a show-expanded>Show <?php echo count($restResults); ?> more times</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>