"use strict";
$('#gatekeeperFormID') .ready(function () {

	
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
	$('#selectModeID').change( function(e) {

		 $('#gk_settingsError') .empty();


		var url = OC.generateUrl('apps/gatekeeper/api/settings/mode/');
		var s = $(e.target);
		var block = s.parent();

        block.removeClass('gk_changed gk_error gk_saved');
        block.addClass('gk_changed');

		var value = $('#selectModeID option:selected').val();
		var tValue = $('#selectModeID option:selected').text();
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

	$('#searchGroupFieldID').autocomplete({
		source: groupUrl
	});

});