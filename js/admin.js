"use strict";
$('#gatekeeperForm') .ready(function () {

	$( "#gkTabs" ).tabs();
	
	var display = function (msg, nature) {
  var id = '#gk_display_'+nature;
   var c = $(id);
   if (!c.find('ol') .length) {
     c.append('<ol></ol>');
   }
   $('<li>' + msg + '</li>') .appendTo( id+' ol');
 };

	/********************************************************
	* MODE function
	*********************************************************/
	$('#selectMode').change( function(e) {

    $('#gk_display_error') .empty();


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
      display('OK:'+tValue, 'info');
      block.removeClass('gk_changed gk_error gk_saved');
      block.addClass('gk_saved');
      block.removeClass('gk_saved', 2000);

    }, 'json') .fail(function (jqXHR, textStatus, errorThrown) {

      block.removeClass('gk_changed gk_error gk_saved');
      block.addClass('gk_error');
      $('#gk_display_error') .text(jqXHR.responseJSON.msg);
    });

  });

	/********************************************************
	* search group function
	*********************************************************/
	var groupUrl = OC.generateUrl('apps/gatekeeper/api/settings/group');


	// $('.searchGroupField').autocomplete({
	// 	minLength: 2,
	// 	delay: 500,
	// 	source: function(request,response) {
	// 		$.get(groupUrl, {term: request.term})
	// 			.done( function(data, textStatus, jqXHR){
	// 				response(data);
	// 			})
	// 	}
	// });


  /**
  * Add A List Item
  */
  var addListItem = function(mode, grpId, grpName, list) {
    var id = 'gk_action_'+mode+'_'+grpId;
    var img = '<img src="/core/core/img/actions/delete.svg" class="svg action">';
    var span = '<span>'+grpName+'</span>';
    var li = $('<li><a id="'+id+'" class="action delete">'+img+'</a>'+span+'</li>');
    li.appendTo(list);
    li.click(onClickRemoveGroup);
    return li;
  }

  /**
  * Handler for EventListener
  */
  var onClickRemoveGroup = function(e){
    var anchor = $(e.target).parent();
    var li = anchor.parent();
    var anchorId = anchor.attr('id');
    var parts = anchorId.split('_');
    var grpId = parts[parts.length - 1 ];
    var mode = parts[parts.length - 2 ];
    var name = li.find('span').text();

    li.removeClass('gk_changed gk_error gk_saved');
    li.addClass('gk_changed');
    postUpdate({group: grpId, action: 'rm', mode: mode }, 
      function() {
        display(name+' removed  from '+mode, 'info');
        showSuccess(li);
        li.remove();
      }
    , function() {
      showError(li);
    });

  }

  var showSuccess = function(li) {
      li.removeClass('gk_changed gk_error gk_saved');
      li.addClass('gk_saved');
      li.removeClass('gk_saved', 2000);
  }

  var showError = function(li) {
      li.removeClass('gk_changed gk_error gk_saved');
      li.addClass('gk_error');
  }

  var postUpdate = function(parms, onSuccess, onFail) {

    $.post(groupUrl, parms)
    .done(function(data){
      if( typeof onSuccess == 'function' ) onSuccess(data.id);
      return data.id;
    })
    .fail(function(jqXHR,  textStatus, errorThrown){
      if( typeof onFail == 'function' ) onFail();
      $('#gk_display_error') .text(jqXHR.responseJSON.msg);
    })

  }

  var registerGroupInModeList = function(mode) {
    var name = $('#gkGroupName_'+mode).val();
    var list = $('#gkList_'+mode);
    var addButton = $('#gkAddButton_'+mode);
    addButton.addClass('loading');
    var id = postUpdate({name: name, action: 'add', mode: mode }, function(){
      addButton.removeClass('loading');
      var li = addListItem(mode, id, name, list);
      showSuccess(li);
      $('#gkGroupName_'+mode).attr('value','');
      display(name+' added in '+mode, 'info');
    });
  }


  /**
  * Load group according to mode
  */
  var loadGroupByMode = function(mode) {

    var loadButton = $('#gkLoadButton_'+mode);

    loadButton.addClass('loading');

    var list = $('#gkList_'+mode);
    list.empty();

    $.get(groupUrl, { mode: mode} )
    .done(function(data){
      loadButton.removeClass('loading');
      for (var i=0; i<data.length; i++) {
        var grp = data[i];
        addListItem(mode, grp.id, grp.name, list);
      }
    })
    .fail(function(jqXHR,  textStatus, errorThrown){
      $('#gk_display_error').text(textStatus);
    });

  }


  /**
  * ---------------------------------------------------------
  * Bind buttons to function
  * ---------------------------------------------------------
  */
  $('#gkLoadButton_whitelist').click(function(e) {
    loadGroupByMode('whitelist');
  });

  $('#gkAddButton_whitelist').click(function(e) {
    registerGroupInModeList('whitelist');
  });

  $('#gkLoadButton_blacklist').click(function(e) {
    loadGroupByMode('blacklist');
  });

  $('#gkAddButton_blacklist').click(function(e) {
    registerGroupInModeList('blacklist');
  });



});