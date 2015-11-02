jQuery( function ( $ ) {

	if(typeof queryParameters().order_ids != 'undefined'){
		var orderIds = decodeURIComponent(queryParameters().order_ids).split(",");
		var ordersTodo = orderIds.length;
		var ordersPassed = 0;
		var successIds = [];
		var problemIds = [];
		var alreadyIds = [];
		
		processId(orderIds, successIds, problemIds, alreadyIds, ordersTodo);
	}
	
	function processId(orderIds, successIds, problemIds, alreadyIds, ordersTodo){
		var id = orderIds.shift();
		
		showProcess(id, successIds, problemIds, alreadyIds, ordersTodo);

		$.ajax({
			url: ajaxurl,
			data: { action: 'mass_order', security: ajax_nonce, order_id: id },
			dataType: "json",
			error: function(){
				problemIds.push(id);
				ordersTodo -= 1;
				if( orderIds.length > 0 ) {
					processId(orderIds, successIds, problemIds, alreadyIds, ordersTodo);
				}
				else{
					window.location = redirect_url + '&success_ids=' + encodeURI(successIds.join(', ')) + '&problem_ids=' + encodeURI(problemIds.join(', ')) + '&already_ids=' + encodeURI(alreadyIds.join(', '));
				}
			},
			success: function(data){
				if(typeof data.status != 'undefined'){
					switch(data.status){
						case 'success':
							successIds.push(id);
							ordersTodo -= 1;
							if( orderIds.length > 0 ) {
								processId(orderIds, successIds, problemIds, alreadyIds, ordersTodo);
							}
							else{
								window.location = redirect_url + '&success_ids=' + encodeURI(successIds.join(', ')) + '&problem_ids=' + encodeURI(problemIds.join(', ')) + '&already_ids=' + encodeURI(alreadyIds.join(', '));
							}
							break;
						
						case 'error':
							problemIds.push(id);
							ordersTodo -= 1;
							if( orderIds.length > 0 ) {
								processId(orderIds, successIds, problemIds, alreadyIds, ordersTodo);
							}
							else{
								window.location = redirect_url + '&success_ids=' + encodeURI(successIds.join(', ')) + '&problem_ids=' + encodeURI(problemIds.join(', ')) + '&already_ids=' + encodeURI(alreadyIds.join(', '));
							}
							break;
						
						case 'already':
							alreadyIds.push(id);
							ordersTodo -= 1;
							if( orderIds.length > 0 ) {
								processId(orderIds, successIds, problemIds, alreadyIds, ordersTodo);
							}
							else{
								window.location = redirect_url + '&success_ids=' + encodeURI(successIds.join(', ')) + '&problem_ids=' + encodeURI(problemIds.join(', ')) + '&already_ids=' + encodeURI(alreadyIds.join(', '));
							}
							break;
					}
				}
			}
		});
	}
	
	function queryParameters(){
		var result = {};

		var params = window.location.search.split(/\?|\&/);

		params.forEach( function(it) {
			if (it) {
				var param = it.split("=");
				result[param[0]] = param[1];
			}
		});

		return result;
	}

	function showProcess(id, successIds, problemIds, alreadyIds, ordersTodo){
		$('#mass_order .ongoing .processed_id').html(id);
		$('#mass_order .ongoing .success_ids').html(successIds.join(', '));
		$('#mass_order .ongoing .problem_ids').html(problemIds.join(', '));
		$('#mass_order .ongoing .already_ids').html(alreadyIds.join(', '));
		$('#mass_order .ongoing .remaining').html(ordersTodo);
	}
});