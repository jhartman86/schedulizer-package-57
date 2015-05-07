<style type="text/css">
    #btSchedulizerEvent {position:static;z-index:99999;}
    #btSchedulizerEvent [ajax-work] {position:relative;}
    #btSchedulizerEvent [ajax-work]::after {content:'';display:none;width:100%;height:100%;position:absolute;top:0;right:0;bottom:0;left:0;background:rgba(255,255,255,0.85) url('/packages/schedulizer/images/spinner.svg') no-repeat 50% 50%;z-index:19;}
    #btSchedulizerEvent [ajax-work].working::after {display:block;}
</style>

<div id="btSchedulizerEvent">
    <div class="row">
        <div class="col-sm-12">
            <h4>Select Calendar To View Events From</h4>
            <?php echo $form->select('calendarList', $calendarList, $selectedCalendarID); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12" ajax-work>
            <h4>Event</h4>
            <?php echo $form->select('eventID', array(), $selectedEventID); ?>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(function(){
        var $parent         = $('#btSchedulizerEvent'),
            $eventList      = $('[name="eventID"]', $parent),
            $ajaxWork       = $('[ajax-work]', $parent),
            _eventCache     = {},
            // On create, this will be zero since $selectedEventID will be null but cast to an (int)
            selectedEventID = <?php echo (int)$selectedEventID; ?>

        function _fetch( calendarID ){
            if( ! _eventCache[calendarID] ){
                _eventCache[calendarID] = $.get('/_schedulizer/calendar/' + calendarID + '/events');
            }
            return _eventCache[calendarID];
        }

        $('[name="calendarList"]', $parent).on('change', function(){
            $ajaxWork.toggleClass('working', true);
            _fetch(this.value).done(function( resp ){
                $eventList.empty();
                if( $.isArray(resp) ){
                    resp.forEach(function( obj ){
                        var str = '<option value="'+obj.id+'">'+obj.title+'</option>';
                        if( +(obj.id) === selectedEventID ){
                            str = '<option value="'+obj.id+'" selected>'+obj.title+'</option>';
                        }
                        $eventList.append(str);
                    });
                }
                $ajaxWork.toggleClass('working', false);
            });
        }).trigger('change');
    });
</script>