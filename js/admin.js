"use strict";
$('#gatekeeperForm') .ready(function () {




	$( "#gkTabs" ).tabs();

  var fmt = function(translationName, args) {
    var formatted = $("#gk_translation span[name="+translationName+"]").text()
    for( var arg in args ) {
        formatted = formatted.replace("{" + arg + "}", args[arg]);
    }
    return formatted;
  };
    
	
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
	$('input[name=mode]').change( function(e) {

    $('#gk_display_error').empty();


    var url = OC.generateUrl('apps/gatekeeper/api/settings/mode/');
    var s = $(e.target);
    var block = s.parent();

    block.removeClass('gk_changed gk_error gk_saved');
    block.addClass('gk_changed');

    var value = $('input[name=mode]:checked').val();
    $.post(url, {
      value: value
    }, function (result) {
      display(fmt('mode_is_selected', [ value]), 'info');
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
  var trashUrl = OC.imagePath('core', 'actions/delete.svg');


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


  var mode2group = { "whitelist": 1, "blacklist": 2, "exclusion": 3};
  /**
  * Add A List Item
  */
  var addListItem = function(kind, grpId, grpName, list) {
    var id = 'gk_action_'+kind+'_'+grpId;
    var img = '<img src="'+trashUrl+' " class="svg action">';
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
    var kind = parts[parts.length - 2 ];
    var name = li.find('span').text();

    li.removeClass('gk_changed gk_error gk_saved');
    li.addClass('gk_changed');
    postUpdate({group: grpId, action: 'rm', gt: mode2group[kind] }, 
      function() {
        display(fmt('group_removed_from', [name, kind]), 'info');
        showSuccess(li);
        li.remove();
      }
    , function() {
      showError(li);
    });

  }

/*  var onRefreshDelay = function(e) {
    var delayUrl = OC.generateUrl('apps/gatekeeper/api/settings/delay');
    var block = $(e.target).parent();
    var delay = $(e.target).val();

    block.removeClass('gk_changed gk_error gk_saved');
    block.addClass('gk_changed');
     $.post(delayUrl, { delay: delay })
    .done(function(data){
      block.removeClass('gk_changed gk_error gk_saved');
      block.addClass('gk_saved');
      block.removeClass('gk_saved', 2000);
    })
    .fail(function(jqXHR,  textStatus, errorThrown){
      block.removeClass('gk_changed gk_error gk_saved');
      block.addClass('gk_error');
      $('#gk_display_error') .text(jqXHR.responseJSON.msg);
    });
  }*/




  var onKeyChange = function(e, key, url, valueCallBack ) {
    var block = $(e.target).parent();
    var value;
    if ( valueCallBack == undefined) {
      value = $(e.target).val();
    } else {
      value = valueCallBack($(e.target));
    }

    block.removeClass('gk_changed gk_error gk_saved');
    block.addClass('gk_changed');
    var data = {};
    data[key] = value;
    $.post(url, data)
    .done(function(data){
      block.removeClass('gk_changed gk_error gk_saved');
      block.addClass('gk_saved');
      block.removeClass('gk_saved', 2000);
    })
    .fail(function(jqXHR,  textStatus, errorThrown){
      block.removeClass('gk_changed gk_error gk_saved');
      block.addClass('gk_error');
      $('#gk_display_error') .text(jqXHR.responseJSON.msg);
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

  var postUpdate = function(parms, onSuccess, onFail, onAlways) {

    $.post(groupUrl, parms)
    .done(function(data){
      if( typeof onSuccess == 'function' ) onSuccess(data.id);
      return data.id;
    })
    .fail(function(jqXHR,  textStatus, errorThrown){
      if( typeof onFail == 'function' ) onFail();
      $('#gk_display_error') .text(jqXHR.responseJSON.msg);
    })
    .always( function() {
       if( typeof onAlways == 'function' ) onAlways();
    })

  }

  var registerGroup = function(kind) {
    var name = $('#gkGroupName_'+kind).val();
    var list = $('#gkList_'+kind);
    var addButton = $('#gkAddButton_'+kind);
    addButton.addClass('loading');
    var id = postUpdate({name: name, action: 'add', gt: mode2group[kind] }, 
      function(){
        var li = addListItem(kind, id, name, list);
        showSuccess(li);
        $('#gkGroupName_'+kind).attr('value','');
        display( fmt( 'group_added_in', [name, kind]), 'info');
      },
      null,
      function() {
        addButton.removeClass('loading');
      }
    );
  }


  /**
  * Load groups according to kind
  */
  var loadGroups = function(kind) {

    var loadButton = $('#gkLoadButton_'+kind);

    loadButton.addClass('loading');

    var list = $('#gkList_'+kind);
    list.empty();

    $.get(groupUrl, { kind: kind} )
    .done(function(data){
      if ( data.length == 0 ) {
        list.append('<p>0 group</p>');
      } 
      for (var i=0; i<data.length; i++) {
        var grp = data[i];
        addListItem(kind, grp.id, grp.name, list);
      }
    })
    .fail(function(jqXHR,  textStatus, errorThrown){
      $('#gk_display_error').text(textStatus);
    }).always(function() {
      loadButton.removeClass('loading');
    });

  }


  /**
  * ---------------------------------------------------------
  * Bind buttons to function
  * ---------------------------------------------------------
  */
  $('#gkLoadButton_whitelist').click(function(e) {
    loadGroups('whitelist');
  });

  $('#gkAddButton_whitelist').click(function(e) {
    registerGroup('whitelist');
  });

  $('#gkLoadButton_blacklist').click(function(e) {
    loadGroups('blacklist');
  });

  $('#gkAddButton_blacklist').click(function(e) {
    registerGroup('blacklist');
  });

  $('#gkLoadButton_exclusion').click(function(e) {
    loadGroups('exclusion');
  });

  $('#gkAddButton_exclusion').click(function(e) {
    registerGroup('exclusion');
  });  


  $('#gk_refresh_delay').change( function(e) {
      onKeyChange(e, 'delay', OC.generateUrl('apps/gatekeeper/api/settings/delay'));
  });

  $('#gk_deny_logger').change( function(e) {
    onKeyChange(e, 'logger', OC.generateUrl('apps/gatekeeper/api/settings/logger'));
  });

});