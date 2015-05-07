<?php
    /** @var $eventListObj \Concrete\Package\Schedulizer\Src\EventList */
    $resultsGroupedByDay = $eventListObj->getGroupedByDay();
    if( empty($resultsGroupedByDay) ){
        echo 'No events available'; return;
    }
?>

<style type="text/css">
    .schedulizer-list {}
    .schedulizer-list [group] {float:left;padding:0 1rem;max-width:25%;width:100%;display:block;}
    .schedulizer-list [group-date] {font-size:120%;font-weight:bold;}
    .schedulizer-list [event] {display:block;position:relative;}
    .schedulizer-list [event]:hover {background:rgba(240,240,240,0.75);cursor:pointer;}
    .schedulizer-list [time] {padding:2px 4px;line-height:1;display:inline-block;position:absolute;top:0;right:0;font-size:12px;text-transform:uppercase;color:#fff;background:rgba(0,0,0,0.35);}
</style>

<div id="schedulizer-list-<?php echo $this->controller->bID; ?>" class="schedulizer-list">
    <?php foreach($resultsGroupedByDay AS $dateKey => $items): ?>
        <div group>
            <div group-date>
                <span><?php echo (new \DateTime($dateKey))->format('M d, Y'); ?></span>
            </div>
            <ul class="list-unstyled">
                <?php foreach($items AS $row){ ?>
                    <li event="<?php echo $row['eventID'] ?>">
                        <?php if( $row['fileID'] ){ ?>
                            <img src="<?php echo \Concrete\Core\File\File::getRelativePathFromID($row['fileID']); ?>" />
                        <?php } ?>
                        <span title><?php echo $row['title']; ?></span>
                        <span time><?php echo (new \DateTime($row['startLocal']))->format('g:i a'); ?></span>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>