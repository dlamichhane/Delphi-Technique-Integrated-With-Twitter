$(function () {
	$('#show_graph').click(function () {
		question_val = $('select[name=question] option:selected').val();
		round_val = $('select[name=round] option:selected').val();
		result_type = $('select[name=result_type] option:selected').val();

		if (question_val == 0) {
			alert('Select question');
			return false;
		}

		if (round_val == 0) {
			alert('Select appropriate Delphi round');
			return false;
		}

		if (result_type == 0) {
			alert('Select result type');
			return false;
		}

		data = {};
		data['question'] = question_val;
		data['round'] = round_val;
		data['result_type'] = result_type;

		if (result_type == "comments") {
			if (question_val !=0 && round_val != 'R3') {
				alert('Comments are only available for round three');
				return false;
			}
			
			$.ajax({
				url: '../admin/proxy.php',
				type: 'POST',
				data: data,
				success: function(responseData) {
					$("#container").html(responseData);
					$('.highcharts-container').remove();
				}
			});
			return true;
		}

		if (question_val != '0' && round_val != '0' && result_type == "graph") {
			$.ajax({
				url: '../admin/proxy.php',
				type: 'POST',
				data: data,
				success: function (responseData) {
					try {
						var obj = jQuery.parseJSON(responseData);
						// console.log(jQuery.parseJSON(obj[0]));	
					} catch(e) {
						alert('Invalid JSON');
					}
					
					categories = [];
					data = [];

					$.each(jQuery.parseJSON(obj[0]), function(key, val) {
						categories.push(key);
						data.push(parseFloat(val));
					});

					$('#container').highcharts({
				        chart: {
				            type: 'column'
				        },
				        title: {
				            text: question_val + " "+ round_val + ' Average value'
				        },
				        xAxis: {
				            categories: categories
				        },
				        yAxis: {
				            min: 0,
				            title: {
				                text: 'Average mean value'
				            }
				        },
				        plotOptions: {
				            column: {
				                pointPadding: 0.2,
				                borderWidth: 0
				            }
				        },
				        series: [{
				            name: 'Mean response',
				            data: data,
				            dataLabels: {
			                    enabled: true,
			                    rotation: -90,
			                    color: '#FFFFFF',
			                    align: 'right',
			                    x: 4,
			                    y: 10,
			                    style: {
			                        fontSize: '13px',
			                        fontFamily: 'Verdana, sans-serif',
			                        textShadow: '0 0 3px black'
			                    }
			                }
				        }]
				    });

					categories = [];
					data = [];

					$.each(jQuery.parseJSON(obj[1]), function(key, val) {
						categories.push(key);
						data.push(parseFloat(val));
					});

					$('#coefficient').highcharts({
				        chart: {
				            type: 'column'
				        },
				        title: {
				            text: question_val + " "+ round_val + ' Coefficient of variation'
				        },
				        xAxis: {
				            categories: categories
				        },
				        yAxis: {
				            min: 0,
				            title: {
				                text: 'Coefficient of variation value'
				            }
				        },
				        plotOptions: {
				            column: {
				                pointPadding: 0.2,
				                borderWidth: 0
				            }
				        },
				        series: [{
				            name: 'Coefficient of variation of response ',
				            data: data,
				            dataLabels: {
			                    enabled: true,
			                    rotation: -90,
			                    color: '#FFFFFF',
			                    align: 'right',
			                    x: 4,
			                    y: 10,
			                    style: {
			                        fontSize: '13px',
			                        fontFamily: 'Verdana, sans-serif',
			                        textShadow: '0 0 3px black'
			                    }
			                }
				        }]
				    });
				}
			});
		} else {
			alert('Select the proper value');
		}
		
	});
});