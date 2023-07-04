// JavaScript Document
$(document).ready(function() {
	console.log();
	Tasks.calendar();
	Tasks.today();

	});

var Tasks = {
	
	calendar: function(){
		
		// Basic initialization
		$('.fullcalendar-basic').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				//right: 'month,basicWeek,basicDay'
				right: 'month,basicWeek'
			},
			aspectRatio: 1,
			editable: false,
			eventLimit: true,
			events: function(start, end, timezone, callback) {
				
				var startDate = moment(start._d, 'MM-DD-YYYY').format('YYYY-MM-DD');
				var endDate = moment(end._d, 'MM-DD-YYYY').format('YYYY-MM-DD');
				var period = startDate+','+endDate;
				
				$.ajax({
					type:'GET',
					url: rootUrl+'/statutory/task/all?periodRange='+period+'&taskView=calendar&taskStatus=Incomplete',
					dataType: 'json',
					success: function(e) {

							var events = e.data;
							callback(events);

					}
				});

			},
			

			eventRender: function(event, element) {
				
				element.popover({
					template: '<div class="popover border-teal-400"><div class="arrow"></div><h3 class="popover-title bg-teal-400"></h3><div class="popover-content"></div></div>'
				});
				
			},
			
			

			eventMouseover: function(calEvent, jsEvent) {
				console.log(calEvent);
				var tooltip = '<div class="popover border-info-600"><div class="arrow"></div><h3 class="popover-title bg-info-600">'+calEvent.title+'</h3><div class="popover-content"><b>Client Name: </b>'+calEvent.client_name+'<br><b>Service Type:</b> '+calEvent.service_type+'</div></div>';
				var $tooltip = $(tooltip).appendTo('body');

				$(this).mouseover(function(e) {
					$(this).css('z-index', 10000);
					$tooltip.fadeIn('500');
					$tooltip.fadeTo('10', 1.9);
				}).mousemove(function(e) {
					$tooltip.css('top', e.pageY + 10);
					$tooltip.css('left', e.pageX + 20);
				});
			},

			eventMouseout: function(calEvent, jsEvent) {
				$(this).css('z-index', 8);
				$('.popover').remove();
			},





		});
		
	},
	
	today: function(){
		
		$.ajax({
			type:'GET',
			url: rootUrl+'/statutory/task/all?periodRange=today&taskView=calendar&taskStatus=Incomplete',
			dataType: 'json',
			success: function(e) {

				if(e.data.length > 0){
					
					e.data.forEach(function(element) {
						
						
						$('.dayTasks').append('<div class="panel panel-flat border-left-xlg border-left-info"><div class="panel-heading"><h6 class="panel-title"><span class="text-warning">'+element.title+'</span><br><span class="text-size-small text-muted">'+element.client_name+'</span></h6></div><div class="panel-footer"><a class="heading-elements-toggle"><i class="icon-more"></i></a><div class="heading-btn pull-right"><a href="javascript:void(0)" class="CompleteTask animation" data-animation="bounceOutLeft" style="margin-right: 4px;" data-popup="tooltip" data-animation="true" data-original-title="Complete Task" data-placement="left" data-id="'+element.id+'" data-client="'+element.id_client+'" data-rel="'+element.service_type+'"><span class="label label-striped border-left-teal-300 label-icon"><i class="icon-checkmark2"></i></span></a></div></div></div>');	

					});
					
				}
				

			}
		});
		
	}
	
};
