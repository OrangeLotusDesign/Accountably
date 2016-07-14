
$(window).load(function(){

$.validator.addMethod("wordCount",
   function(value, element, params) {
      var typedWords = jQuery.trim(value).split(' ').length;
 
      if(typedWords <= params[0]) {
         return true;
      }
   },
   jQuery.format("Only {0} words allowed.")
);

$.validator.addMethod("phoneValidate", function(number, element) {
    number = number.replace(/\s+/g, ""); 
    return this.optional(element) || number.length > 9 &&
        number.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
}, "Please specify a valid phone number");

$("#signup_form").validate({
        rules: {
	        first_name: {
			  required: true,
			  minlength: 2
			},
	        last_name: {
			  required: true,
			  minlength: 2
			},
	        email: {// compound rule
	          required: true,
	          email: true
	        },
	        confirm_email: {// compound rule
	          required: false,
	          email: true,
	          equalTo: "#email"
	        },
	        phone: {
		      required: false,
		      phoneValidate: true
		    },
	        age: {
			  required: true,
			  digits: true
			},
	        location: "required",
	          industry: "required",
	          title: "required",
			goal: {
			  required: true,
			  wordCount: ['100']
			},
		 },
        messages: {
		  goal: "Your goal must be 100 words or less."
        }
      });

	$('#phone').on('input', function() {
	    var number = $(this).val().replace(/[^\d]/g, '')
	    if (number.length == 7) {
	      number = number.replace(/(\d{3})(\d{4})/, "$1-$2");
	    } else if (number.length == 10) {
	      number = number.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
	    }
	    $(this).val(number)
	});

	$(function() {
		var url = '/wp-content/plugins/accountably/';
	    $( "#industry" ).autocomplete({
	        source: url+'search.php'
	    });
	});

// setInterval(function(){
//   var form = $('#volunteer_form');
//   var method = form.attr('method').toLowerCase();      // "get" or "post"

// $("#autosave").css("display","none");  
//   $[method]("<?php bloginfo('wpurl'); ?>/wp-content/plugins/volunteer-form/admin.php?action=save", form.serialize(), function(data){
//   $('#autosave').slideDown();
//   $('#autosave').delay(800).slideUp();
//   });
// },60000);                                      // do it every minute
  
// $("#submit").click(function(){
//   var form = $('#signup_form');
//   var method = form.attr('method').toLowerCase();      // "get" or "post"
//   $[method]("http://accountably.dev/wp-content/plugins/accountably/admin.php", form.serialize(), function(data){
//   alert("Form saved.");
//   });
// });
//<?php bloginfo('wpurl'); ?>
	  // $("select#primary_language_proficiency").css("display","none");
	  // $("select#secondary_language_proficiency").css("display","none");
	   
	  // $("span.ui-slider-label").css("margin-left","-10");
	   
		// Toggle textarea to list other experience
	   $("#other_list").css("display","none");

	   $("#other").click(function(){

		// If checked
		if ($("#other").is(":checked"))
		{
			//show the hidden question
			$("#other_list").show("fast");
		}
		else
		{
			//otherwise, hide it
			$("#other_list").hide("fast");
		}
	  });

// Decision tree toggling for the short answer section
	   $("#know_ambassador_champion_wrap").css("display","none");
	  
	  $(":radio[name='ambassador_champion']").click(function(){
		  var newVal = $(":radio[name='ambassador_champion']:checked").val();
		  if (newVal == "ambassador_champion_no") {
			$("#know_ambassador_champion_wrap").show();
		  } else {
			$("#know_ambassador_champion_wrap").hide();
			$("#ambassador_champion_names_wrap").hide();
		  }
		});

	   $("#ambassador_champion_names_wrap").css("display","none");
	  
	  $(":radio[name='know_ambassador_champion']").click(function(){
		  var newVal = $(":radio[name='know_ambassador_champion']:checked").val();
		  if (newVal == "know_ambassador_champion_yes") {
			$("#ambassador_champion_names_wrap").show();
		  } else {
			$("#ambassador_champion_names_wrap").hide();
		  }
		});

	   $("#which_wrap").css("display","none");
	  
	  $(":radio[name='participated']").change(function(){
		  var newVal = $(":radio[name='participated']:checked").val();
		  if (newVal == "participated_yes") {
			$("#which_wrap").show();
		  } else {
			$("#which_wrap").hide();
		  }
		});
	  
	  // Toggle sections on the legend and on completion
		$(".hide").css("display","none");
		
		$('#expand').click(function(event) {
			event.preventDefault();
			$(".hide").show(400);
			$("#personal_information").show(400);
			$("legend").addClass('expanded').removeClass('collapsed');
		});
		
		$('#collapse').click(function(event) {
			event.preventDefault();
			$(".hide").hide(400);
			$("#personal_information").hide(400);
			$("legend").addClass('collapsed').removeClass('expanded');
		});
		
		$('#personal_information_toggle').click(function() {
			$('#personal_information').toggle(400);
			if($(this).hasClass('expanded'))
			{
				$(this).addClass('collapsed').removeClass('expanded');
			}
			else
			{
				$(this).addClass('expanded').removeClass('collapsed');
			}
		});
		
		$('.personal_information').click(function(event) {
			event.preventDefault();
			$("#personal_information").hide(400);
			$("#professional_information").show(400);
			$("#personal_information_toggle").addClass('collapsed').removeClass('expanded');
			$("#professional_information_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('#professional_information_toggle').click(function() {
			$('#professional_information').toggle(400);
			if($(this).hasClass('expanded'))
			{
				$(this).addClass('collapsed').removeClass('expanded');
			}
			else
			{
				$(this).addClass('expanded').removeClass('collapsed');
			}
		});
		
		$('#email2').change(function() {
			$("#professional_information").show(400);
			$("#personal_information").hide(400);
			$("#personal_information_toggle").addClass('collapsed').removeClass('expanded');
			$("#professional_information_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('.professional_information').click(function(event) {
			event.preventDefault();
			$("#professional_information").hide(400);
			$("#short_answer").show(400);
			$("#professional_information_toggle").addClass('collapsed').removeClass('expanded');
			$("#short_answer_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('#short_answer_toggle').click(function() {
			$('#short_answer').toggle(400);
			if($(this).hasClass('expanded'))
			{
				$(this).addClass('collapsed').removeClass('expanded');
			}
			else
			{
				$(this).addClass('expanded').removeClass('collapsed');
			}
		});
		
		$('#work_country').change(function() {
			$("#short_answer").show(400);
			$("#professional_information").hide(400);
			$("#professional_information_toggle").addClass('collapsed').removeClass('expanded');
			$("#short_answer_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('.short_answer').click(function(event) {
			event.preventDefault();
			$("#short_answer").hide(400);
			$("#skills_certifications").show(400);
			$("#short_answer_toggle").addClass('collapsed').removeClass('expanded');
			$("#skills_certifications_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('#skills_certifications_toggle').click(function() {
			$('#skills_certifications').toggle(400);
			if($(this).hasClass('expanded'))
			{
				$(this).addClass('collapsed').removeClass('expanded');
			}
			else
			{
				$(this).addClass('expanded').removeClass('collapsed');
			}
		});
		
		$('#hear').change(function() {
			$("#skills_certifications").show(400);
			$("#short_answer").hide(400);
			$("#short_answer_toggle").addClass('collapsed').removeClass('expanded');
			$("#skills_certifications_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('.skills_certifications').click(function(event) {
			event.preventDefault();
			$("#skills_certifications").hide(400);
			$("#professional_experience").show(400);
			$("#skills_certifications_toggle").addClass('collapsed').removeClass('expanded');
			$("#professional_experience_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('#professional_experience_toggle').click(function() {
			$('#professional_experience').toggle(400);
			if($(this).hasClass('expanded'))
			{
				$(this).addClass('collapsed').removeClass('expanded');
			}
			else
			{
				$(this).addClass('expanded').removeClass('collapsed');
			}
		});
		
		$('#certification').change(function() {
			$("#professional_experience").show(400);
			$("#skills_certifications").hide(400);
			$("#skills_certifications_toggle").addClass('collapsed').removeClass('expanded');
			$("#professional_experience_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('.professional_experience').click(function(event) {
			event.preventDefault();
			$("#professional_experience").hide(400);
			$("#individual_capabilities").show(400);
			$("#professional_experience_toggle").addClass('collapsed').removeClass('expanded');
			$("#individual_capabilities_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('#individual_capabilities_toggle').click(function() {
			$('#individual_capabilities').toggle(400);
			if($(this).hasClass('expanded'))
			{
				$(this).addClass('collapsed').removeClass('expanded');
			}
			else
			{
				$(this).addClass('expanded').removeClass('collapsed');
			}
		});
		
		$(":radio[name='travel']").change(function() {
			$("#essay_questions").show(400);
			$("#individual_capabilities").hide(400);
			$("#individual_capabilities_toggle").addClass('collapsed').removeClass('expanded');
			$("#essay_questions_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('.individual_capabilities').click(function(event) {
			event.preventDefault();
			$("#individual_capabilities").hide(400);
			$("#essay_questions").show(400);
			$("#individual_capabilities_toggle").addClass('collapsed').removeClass('expanded');
			$("#essay_questions_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('#essay_questions_toggle').click(function() {
			$('#essay_questions').toggle(400);
			if($(this).hasClass('expanded'))
			{
				$(this).addClass('collapsed').removeClass('expanded');
			}
			else
			{
				$(this).addClass('expanded').removeClass('collapsed');
			}
		});
		
		$('#learn').change(function() {
			$("#liability").show(400);
			$("#essay_questions").hide(400);
			$("#essay_questions_toggle").addClass('collapsed').removeClass('expanded');
			$("#liability_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('.essay_questions').click(function(event) {
			event.preventDefault();
			$("#essay_questions").hide(400);
			$("#liability").show(400);
			$("#essay_questions_toggle").addClass('collapsed').removeClass('expanded');
			$("#liability_toggle").addClass('expanded').removeClass('collapsed');
		});
		
		$('#liability_toggle').click(function() {
			$('#liability').toggle(400);
			if($(this).hasClass('expanded'))
			{
				$(this).addClass('collapsed').removeClass('expanded');
			}
			else
			{
				$(this).addClass('expanded').removeClass('collapsed');
			}
		});
		
		

	});
	
(function($){
	$.fn.textareaCounter = function(options) {
		// setting the defaults
		// $("textarea").textareaCounter({ limit: 100 });
		var defaults = {
			limit: 100
		};	
		var options = $.extend(defaults, options);
 
		// and the plugin begins
		return this.each(function() {
			var obj, text, wordcount, limited;
 
			obj = $(this);
 
			obj.after('<span style="font-size: 11px; clear: both; margin-top: -8px; margin-bottom: 20px; display: block;" class="counter-text">Max. '+options.limit+' words</span>');
 
			// function to check word count in field			
			var countcheck = function() {
		    text = obj.val();
		    if(text === "") {
		    	wordcount = 0;
		    } else {
			    wordcount = $.trim(text).split(" ").length;
				}
		    if(wordcount >= options.limit) {
		      obj.parent().find(".counter-text").html('<span style="color: #DD0000;">'+(options.limit - wordcount)+' words left</span>');
					limited = $.trim(text).split(" ", options.limit);
					limited = limited.join(" ");
					$(this).val(limited);
		    } else {
		      obj.parent().find(".counter-text").html((options.limit - wordcount)+' words left');
		    } 
			}
 
			// if field is not empty, count words
			if(obj.val() != '') {
				countcheck(); }
 
 			// if field changes, count words
			obj.keyup(function() {
				countcheck(); });
		});
	};
})(jQuery);

var Timer;
var TotalSeconds;


function CreateTimer(TimerID, Time) {
    Timer = document.getElementById(TimerID);
    TotalSeconds = Time;
    
    UpdateTimer()
    window.setTimeout("Tick()", 1000);
}

function Tick() {
    TotalSeconds -= 1;
    UpdateTimer()
    window.setTimeout("Tick()", 1000);
}

function UpdateTimer() {
    Timer.innerHTML = TotalSeconds;
}