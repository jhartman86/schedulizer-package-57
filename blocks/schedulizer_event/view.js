(function(){

    var eventNodeList = document.querySelectorAll('.schedulizer-event'),
        iterableList  = Array.prototype.slice.call(eventNodeList);

    iterableList.forEach(function( node ){
        var showExpanded = node.querySelector('[show-expanded]');
        if( showExpanded ){
            showExpanded.addEventListener('click', function( event ){
                var hidden = node.querySelectorAll('[expandable]');
                Array.prototype.slice.call(hidden).forEach(function( node ){
                    node.style.display = 'list-item';
                });
                showExpanded.style.display = 'none';
            });
        }
    });

})();