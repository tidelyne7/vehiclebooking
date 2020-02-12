  jQuery( function() {
    jQuery( "#datepicker" ).datepicker();

  } );
  jQuery(document).ready(function(){
  	jQuery('.vb_vehicle_type_select').on('change',function(){
  		var vehicle_selected = jQuery(this).attr("value");
  		// alert(vehicle_selected);
		   jQuery.ajax({
                url: my_ajax_object.ajax_url, // this is the object instantiated in wp_localize_script function
                type: 'POST',
                data:{ 
                  action: 'vb_vehicle_select_ajax', // this is the function in your functions.php that will be triggered
                  vehicle_selected : vehicle_selected
                },
                success: function( data ){  
                  console.log(data);
                   jQuery(".vb_vehicle_select").empty();
                   jQuery(".vb_vehicle_select").append(data);

                }
              });

  	});
  	  jQuery('.vb_vehicle_select').on('change',function(){
  		var vehicle_name = jQuery(this).attr("value");
  		// alert(vehicle_selected);
		   jQuery.ajax({
                url: my_ajax_object.ajax_url, // this is the object instantiated in wp_localize_script function
                type: 'POST',
                data:{ 
                  action: 'vb_vehicle__name_select_ajax', // this is the function in your functions.php that will be triggered
                  vehicle_name : vehicle_name
                },
                success: function( data ){  
                  console.log(data);
                   jQuery("#starting_price_per_day").empty();
                   jQuery("#starting_price_per_day").val(data);
                   // jQuery(".vb_vehicle_select").append(data);

                }
              });

  	});


  })