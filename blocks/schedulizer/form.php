<style type="text/css">
    #btSchedulizer {position:static;z-index:99999;}
    #btSchedulizer .select2-container-multi {border:1px solid #ccc;}
    #btSchedulizer .select2-container-multi.select2-dropdown-open {border-color:#66afe9;}
</style>

<div id="btSchedulizer">
    <div class="row">
        <div class="col-sm-12">
            <h4>Calendars <small>(Select one or more calendars to display events from)</small></h4>
            <select name="calendarIDs[]" class="form-control" multiple="multiple">
                <?php foreach($calendarList AS $calendarObj):
                    $attrs = join(' ', array(
                        (in_array($calendarObj->getID(), $selectedCalendars) ? 'selected' : null),
                        sprintf('value="%s"', $calendarObj->getID())
                    ));
                    echo sprintf('<option %s>%s</option>', $attrs, $calendarObj);
                endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <h4>Tags <small>(Filter results by tags)</small></h4>
            <select name="eventTags[]" class="form-control" multiple="multiple">
                <?php foreach($tagList AS $tagObj):
                    $attrs = join(' ', array(
                        (in_array($tagObj->getID(), $selectedTags) ? 'selected' : null),
                        sprintf('value="%s"', $tagObj->getID())
                    ));
                    echo sprintf('<option %s>%s</option>', $attrs, $tagObj);
                endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <h4>Starting</h4>
            <input type="text" name="startDate" class="form-control" datepicker value="<?php echo $startDate; ?>" placeholder="Start Date" />
        </div>
        <div class="col-sm-6">
            <h4>Ending</h4>
            <input type="text" name="endDate" class="form-control" datepicker value="<?php echo $endDate; ?>" placeholder="End Date" />
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <h4>Events Per Day</h4>
            <input type="text" name="limitPerDay" class="form-control" value="<?php echo $limitPerDay; ?>" placeholder="Max 25" />
        </div>
    </div>
</div>


<script type="text/javascript">
    $(function(){
        $('select[multiple]', '#btSchedulizer').select2();
        $('[datepicker]', '#btSchedulizer').datepicker();
    });
</script>