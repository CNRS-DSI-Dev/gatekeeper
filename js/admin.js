"use strict";
$('#gatekeeperForm') .ready(function () {

	$( "#gkTabs" ).tabs();
	
	var echo = function (msg) {
	        var c = $('#gk_settingsEcho');
	        if (!c.find('ol') .length) {
	            c.append('<ol></ol>');
	        }
	        $('<li>' + msg + '</li>') .appendTo('#gk_settingsEcho ol');
	};

	/********************************************************
	* MODE function
	*********************************************************/
	$('#selectMode').change( function(e) {

	   $('#gk_settingsError') .empty();


		var url = OC.generateUrl('apps/gatekeeper/api/settings/mode/');
		var s = $(e.target);
		var block = s.parent();

        block.removeClass('gk_changed gk_error gk_saved');
        block.addClass('gk_changed');

		var value = $('#selectMode option:selected').val();
		var tValue = $('#selectMode option:selected').text();
		$.post(url, {
                value: value
            }, function (result) {
                echo('OK:'+tValue);
                block.removeClass('gk_changed gk_error gk_saved');
                block.addClass('gk_saved');
                block.removeClass('gk_saved', 2000);

            }, 'json') .fail(function (jqXHR, textStatus, errorThrown) {

                block.removeClass('gk_changed gk_error gk_saved');
                block.addClass('gk_error');
                $('#gk_settingsError') .text(jqXHR.responseJSON.msg);
            });

        block.removeClass('gk_changed',3000);         
    });

	/********************************************************
	* search group function
	*********************************************************/
	var groupUrl = OC.generateUrl('apps/gatekeeper/api/settings/group');


	$('#searchGroupField').autocomplete({
		minLength: 2,
		delay: 500,
		source: function(request,response) {
			$.get(groupUrl, {term: request.term})
				.done( function(data, textStatus, jqXHR){
					response(data);
				})
		}
	});


  var loadGroupByMode = function(mode) {
    
    var list = $('#gkList_'+mode);
    $.get(groupUrl, { mode: mode} ).done(function(data){
      for (var i=0; i<data.length; i++) {
        var grp = data[i];
        var liName = 'gkList_'+mode+'_'+grp;
        list.append('<li id="'+liName+'"><a href="#">'+grp+'</a></li>');
      }
    })
    .fail(function(jqXHR,  textStatus, errorThrown){
      $('#gk_settingsError').text(textStatus);
    });

  }


  $('#gkList_whitelist :a').click(function(e){
    var group = $(e.target).text();
    $.post(groupUrl, {group: group, action: 'rm'})
    .done(function(data){
      echo('ok');
    })
    .fail(function(jqXHR,  textStatus, errorThrown){
      $('#gk_settingsError').text(textStatus);
    })
  });


  $('#gkLoadWhitelist').click(function(e) {
    loadGroupByMode('whitelist');
  });



});