/**
 * Author		: Dan Chris
 * Copyright	: © 2017 Atlanta Fulton County Zoo, Inc. All rights Reserved.
 * Date			: 04-JAN-2017
 * File			: tablet.js
 * Description	: This file used to map the tank to a particular tablet. We can manage tablets by adding, updating & deleting the tanks from the tablet.
 **/

var tankRowCount = 1;

function chekAnyUpdates() {
	var countUpdateTabLen = $("#tabletTank tr.even.isUpdatedTablet").length;
	var countUpdateEmptyTabLen = $("#tabletTank tr.even.isUpdatedTablet.isEmptyTablet").length;

	if(countUpdateTabLen < 1 && countUpdateEmptyTabLen < 1) {
		$('#publishTablet').css("pointer-events" , "none");
		$('#publishTablet').css("cursor" , "not-allowed");
	}
	unSelectAllTablet();
}

function unSelectAllTablet() {
	var countUpdateCheckbox = $("#tabletTank tr").find('input[name=tabletSync]:checked').length;
	if(countUpdateCheckbox > 0) {
		$('#tabletDeselectAll').attr('checked', 'checked');
		$('#tabletDeselectAll').attr('disabled', false);
	}
}

/* Add new empty tank row for a particular tablet */
jQuery('.addNewTankRow').live("click", function() {

  var parentId = $(this).parent().parent().attr('value');
  var tabletHasChange = $("#tabletRow" + parentId).attr('data-attr');
  var isTabletUpdated = document.querySelectorAll('[data-attr="true"]');
  var tankLength = $("#tabletTankList" + parentId + " tr").length + 1;
  var tankListDropdown = $("#hiddenTankArray").html();
  var getSpeciesCount = $("#row" + parentId + " .speciesNameList").length;
  $("#row" + parentId).css("display", "table");
  
  if(isTabletUpdated.length != 0 && !tabletHasChange) {
		swal({title: "Warning!", text: 'Please save your changes in "'+ isTabletUpdated[0].innerText +'" tablet, then proceed', type: "warning" });
  }else if (tankLength < 6) {
	  if (getSpeciesCount < 5) {
	    $("#tabletTankList" + parentId).append('<tr class="odd" id="tankRow' + tankRowCount +
	      '" >\
														<td class="left w25"><select class="tabletTankList" id="dynamicTankList' + tankRowCount +
	      '" style="width: 50%;"></select></td>\
														<td class="speciesNameContainer left"></td>\
														<td class="center w15">\
															<select name="location">\
																<option value="">None</option>\
																<option value="1">Right</option>\
																<option value="2">Left</option>\
															</select>\
														</td>\
														<td class="center w15" id="seqNumber' +
	      tankLength + '">' + tankLength +
	      '</td>\
														<td class=" center w15">\
															<input class="removeTankRow" type="button" title="Remove Tank">\
														</td>\
													</tr>\
												'
	    );
	    $("#dynamicTankList" + tankRowCount).append(tankListDropdown);
	    tankRowCount++;
	  } else {
	    swal("Warning!", "Maximum of five species limit reached for a tablet", "warning");
	  }
  }else {
	  swal("Warning!", "Maximum of five tank limit reached for a tablet", "warning");
  }
});

/* Click function to control show/hide feature */
jQuery('#tabletTank .even th select').live("click", function() {
	  var parentId = $(this).parent().parent().attr('value');
	  var rowCss = $("#row" + parentId).css("display");
	  if (rowCss == "none") {
	    $("#row" + parentId).css("display", "table");
	  } else if (rowCss == "table") {
	    $("#row" + parentId).css("display", "none");
	  }
	});

/* show selected species name in the UI */
jQuery('.tabletTankList').live("change", function() {
  var currentDomObject = $(this);
  var tabTankSpeciesList = [];
  var getValue = currentDomObject.val();
  var getId = currentDomObject.attr('id');
  var getSpeciesListObj = $("#" + getId + " option[value=" + getValue + "]").data('locations');
  if (getSpeciesListObj) {
    var tankSpeciesListStr = JSON.stringify(getSpeciesListObj);
    var tankSpeciesListString = tankSpeciesListStr.replace(/[/]/g, "'");
    var tankSpeciesListObj = JSON.parse(tankSpeciesListString);

    var tabTankSpeciesLength = tankSpeciesListObj.length;

    if (tankSpeciesListStr.indexOf("/") > 0) {
      var speciesArrayList = tankSpeciesListString;
    } else {
      var speciesArrayList = tankSpeciesListStr;
    }

    for (var tankIndex = 0; tankIndex < tabTankSpeciesLength; tankIndex++) {
      var tabTankSpeciesName = tankSpeciesListObj[tankIndex].name;
      tabTankSpeciesList.push("<button class='speciesNameList'>" + tabTankSpeciesName + "</button>");
    }
    var tabTankSpeciesName = tabTankSpeciesList.join("");
    currentDomObject.parent().next('td').html('' + tabTankSpeciesName + '');
  } else {
	  currentDomObject.parent().next('td').html('');
  }

});

/* API to update tank details for a particular tablet */
jQuery('.saveTank').live("click", function() {
  var currentDomObject = $(this);
  var parentId = currentDomObject.parent().parent().attr('value');
  var tabletImei = currentDomObject.parent().attr('data');
  var tabletTheme = $("#tankTheme" + parentId).val();
	var tabletBackground = $("#tankBackground" + parentId).val();
  var tabletId = currentDomObject.parent().attr('value');
  var rowId = "tabletRow"+parentId;
  var tankLength = $("#tabletTankList" + parentId + " tr").length;
  var tabletHasChange = currentDomObject.parent().parent('.even').attr('data-attr');
  var tabletDelete = currentDomObject.parent().parent('.even').attr('data-locations');
  var isTabletUpdated = document.querySelectorAll('[data-attr="true"]');
  var rowCss = $("#row" + parentId).css("display");

  if (rowCss == "none") {
    $("#row" + parentId).css("display", "table");
  } else if (rowCss == "table") {
    $("#row" + parentId).css("display", "none");
  }

  var totalSpecies = 0;
  var tankListArray = [];
  var speciesLengthTotal = [];
  var tankListObj = {};
  var updateTabletJsonObj = {};
  var speciesCountJsonObj = {};

  updateTabletJsonObj.id = tabletId;
  updateTabletJsonObj.imei = tabletImei;
  updateTabletJsonObj.theme = tabletTheme;
	updateTabletJsonObj.bgimage = tabletBackground;
  updateTabletJsonObj.tank = tankListArray;
  
  if(!tabletHasChange) {
	  if(isTabletUpdated.length != 0) {
			swal({title: "Warning!", text: 'Please save your changes in "'+ isTabletUpdated[0].innerText +'" tablet, then proceed', type: "warning" });
		}else {
			swal("Warning!", "No changes found for this tablet", "warning");
		}
  }else
  if(tabletHasChange &&  tankLength != 0 || tabletDelete) {
	  
	  /* Loop for collect tank and species information for a single tablet */
	  var order = 1;
	  for (var tabTankIndex = 1; tabTankIndex <= tankLength; tabTankIndex++) {
				var tankSpeciesArray = [];
				var tankId = $("#tabletTankList" + parentId + " tr:nth-child(" + tabTankIndex + ") select").val();
				var tankName = $("#tabletTankList" + parentId + " tr:nth-child(" + tabTankIndex + ") select option[value=" + tankId + "]")
					.html();
				var tankNumber = $("#tabletTankList" + parentId + " tr:nth-child(" + tabTankIndex + ") select option[value=" + tankId +
					"]").attr('data');
				var tankDirection = $("#tabletTankList" + parentId + " tr:nth-child(" + tabTankIndex + ") td:nth-child(3) select").val();
				//var order = i.toString();
				var tankSpecies = $("#tabletTankList" + parentId + " tr:nth-child(" + tabTankIndex + ") select option[value=" + tankId +
					"]").data('locations');
	
	    	if (tankId != '' && tankSpecies != '' && tankSpecies != undefined) {
	    	
					var tankSpeciesListStr = JSON.stringify(tankSpecies);
					var tankSpeciesTemp = tankSpeciesListStr.replace(/[/]/g, "'");
					var tankSpeciesListObj = JSON.parse(tankSpeciesTemp);
	    	
					tankListObj = {};
					tankListObj.name = tankName;
					tankListObj.id = tankId;
					tankListObj.number = tankNumber;
					tankListObj.direction = tankDirection;
					tankListObj.order = order.toString();
					
					if (tankSpeciesListStr.indexOf("/") > 0) {
						tankListObj.species = tankSpeciesListObj;
						} else {
						tankListObj.species = tankSpecies;
					}
					
					tankListArray.push(tankListObj);
					order++;
					totalSpecies += tankListObj.species.length << 0; //Validation for species count
	    }
	
	    /* Validation for duplicate tank */
	    var valueArr = tankListArray.map(function(item) {
	      return item.id
	    });
	    var isDuplicate = valueArr.some(function(item, idx) {
	      return valueArr.indexOf(item) != idx
	    });
	  }
	
	  if (!isDuplicate) {
	    var tankUpdateJsonObj = JSON.stringify(updateTabletJsonObj);
	    if (totalSpecies < 6) {
	      $.ajax({
	        url: '/tank/tablettankmanager/map-tank',
	        data: tankUpdateJsonObj,
	        type: "POST",
	        dataType: 'json',
	        contentType: 'application/json',
	        success: function(result) {

			          $.ajax({
   						 url: '/atlantazoo/mp-includes/pjax/update-tablet.php?imei='+updateTabletJsonObj.imei,
   						 dataType: 'json',
    					         async: false,
   						 success: function(tabletTankList) {
     					  var jj=10;
   						 },
   						 error: function(error) {
      					var jj=20;
   						 }
  						});


	        	loadTabletTankPage();
	        	setTimeout(function() {
	        	swal({
		              title: "Success!",
		              text: "Tablet(s) updated successfully.",
		              type: "success",
		              timer: 3000,
		              showConfirmButton: false
		            });
	        	}, 500);
	        },
	        error: function(error) {
	          swal("Warning!", "Unable to update Tablet!", "warning");
	        }
	      });
	    } else {
	    	swal("Warning!", "Maximum of five species limit reached for a tablet", "warning");
	    }
	  } else {
		  swal("Warning!", "Same tanks are not allowed for a single tablet", "warning");
	  }
  }else if(tankLength == 0) {
	  swal("Warning!", "Please map tanks for this tablet", "warning");
  }
  return false;

});

/* API for delete mapped species details */
jQuery('.removeTankRow').live("click", function() {

  var speciesCount = 0;
  var getRowId = $(this).parent().parent().attr('id');
  var rootParentId = $("#" + getRowId).parent().attr('value');
  var isTabletUpdated = document.querySelectorAll('[data-attr="true"]');
  $("#row" + rootParentId).css("display", "table");

  var trCount = $("#tabletTankList" + rootParentId + " tr").length;

  /* Taking speices count from the mapped tank */
  for (var rowIndex = 0; rowIndex < trCount; rowIndex++) {
    var row = rowIndex + 1;
    var speciesList = $("#tabletTankList" + rootParentId + " tr:nth-child(" + row + ") td.speciesNameContainer button").length;
    speciesCount += speciesList << 0;
  }

  var checkSelected = $('#' + getRowId + ' .tabletTankList').val();
  if (checkSelected == '') {
    $("#" + getRowId).remove();
  }else if(isTabletUpdated.length != 0) {
		swal({title: "Warning!", text: 'Please save your changes in "'+ isTabletUpdated[0].innerText +'" tablet, then proceed', type: "warning" });
  } else {
    swal({
        title: "Are you sure?",
        text: "This will remove the tank from the tablet",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Yes, I am sure!',
        cancelButtonText: "No, cancel it!"
      },
      function(isConfirm) {
        if (isConfirm) {
          $("#" + getRowId).remove();
          $('#tabletRow'+rootParentId).attr("data-attr", true); 
          $('#tabletRow'+rootParentId).attr("data-locations", true);
          $('#tabletRow' + rootParentId + ' th.tabletActionItems input.saveTank').trigger("click");
          setTimeout(function() {
            swal({
              title: "Success!",
              text: "Tank has been removed successfully!",
              type: "success",
              timer: 2000,
              showConfirmButton: false
            });
          }, 500);
        }
      });
  }
});

/* API for showing tank list */
function fetchTankList() {
  $.ajax({
    url: '/tank/tankmanager/list-tank',
    dataType: 'json',
    async: false,
    success: function(result) {
      var tankListObject = result;
      for (var tankIndex = 0; tankIndex < tankListObject.obj.length; tankIndex++) {
    	  if(tankListObject.obj[tankIndex].species.length > 0) {

	        var speciesList = JSON.stringify(tankListObject.obj[tankIndex].species);
	        var speciesListArray = "data-locations= ''";
	        if (speciesList != null) {
	          var speciesWithoutQuote = speciesList.replace(/[']/g, "/");
	          speciesListArray = "data-locations= '" + speciesWithoutQuote + "'";
	        }
	        $('#hiddenTankArray').append('<option value="' + tankListObject.obj[tankIndex]._id + '" ' + speciesListArray +
	          ' data="' + tankListObject.obj[tankIndex].number + '">' + tankListObject.obj[tankIndex].name + '</option>');
    	  }
      }
			listTabletTank();
    }
  });
}

/* API to list tablet tank mapping */
function listTabletTank() {
	var tabTankObj = {};
  $.ajax({
    url: '/tank/tablettankmanager/list-tablet-tank',
    dataType: 'json',
    async: false,
    success: function(tabletTankList) {
      tabTankObj.tabTankList = tabletTankList;
			listTablet(tabTankObj);
    },
    error: function(error) {
      swal("Alert!", "Unable to get the tablet details!");
    }
  });
}

/* API for showing tablet list */
function listTablet(tabTankObj) {
  $.ajax({
    url: '/tank/tablettankmanager/list-tablet',
    dataType: 'json',
    async: false,
    success: function(result) {
      if (result.msgCode == "Success") {

        for (var tabletIndex = 0; tabletIndex < result.obj.length; tabletIndex++) {

          var tabletObjId = result.obj[tabletIndex]._id;
          var tabTankMapLength = tabTankObj.tabTankList.obj.length;
          var tankListDropdown = $('#hiddenTankArray').html();

					if(result.obj[tabletIndex].tablet_id == "") {
							result.obj[tabletIndex].tablet_id = "tablet";
					}

          $("#tabletTank").append('<tr class="even" id="tabletRow' + tabletIndex + '" value="' + tabletIndex +
            '" onclick="showTablet(' + tabletIndex + ');" >\
													<th align="left" class="w25">Preview: <a target="_blank" href="/desktop/www/template.html?tablet_id='+result.obj[tabletIndex].imei+'"><b>' + result.obj[
              tabletIndex].tablet_id + ' </a></b></th>\
									                <th class="w20">\
														<select id="tankTheme' + tabletIndex + '" style="margin:10px 0px;">\
															<option value="">N/A</option>\
															<option value="1">Extreme</option>\
															<option value="2">Habitat</option>\
															<option value="3">Survival</option>\
															<option value="4">Conservation</option>\
														</select>\
													</th>\
													<th class="w20">\
														<select id="tankBackground' + tabletIndex + '" style="margin:10px 0px;">\
															<option value="">N/A</option>\
															<option value="ExtremeBackground.jpg">Extreme</option>\
															<option value="HabitatBackground.jpg">Habitat</option>\
															<option value="SurvivalBackground.jpg">Survival</option>\
															<option value="ConservationBackground.jpg">Conservation</option>\
														</select>\
													</th>\
													<th colspan="2" class="tabletActionItems w25" value="' + result.obj[tabletIndex]._id +
            '" data="' + result.obj[tabletIndex].imei +
            '">\
														<input class="addNewTankRow" type="button" title="Add Tank">\
														<input class="saveTank" type="button" title="Save Tablet">\
													</th>\
            										<th class="w10"><input class="tabletSyncCheckbox" name="tabletSync" id="tabletSync'+ tabletIndex +'" type="checkbox" disabled></th>\
											</tr>\
											<tr id="row' +
            tabletIndex +
            '" style="display: none">\
												<td colspan="5">\
													<table class="innerTableTabletTank">\
														<thead>\
															<tr>\
																<th class="center w25 ui-state-default">Tank</th>\
																<th class="center ui-state-default">Species</th>\
																<th class="center w15 ui-state-default">Direction</th>\
																<th class="center w15 ui-state-default">Order ID</th>\
																<th class="center w15 ui-state-default">Actions</th>\
															</tr>\
														</thead>\
														<tbody class="tabletTankInnerTable" id="tabletTankList' +
            tabletIndex + '" value="' + tabletIndex +
            '"></tbody>\
													</table>\
												</td>\
											</tr>\
											');
          
          for (var tabletTankIndex = 0; tabletTankIndex < tabTankMapLength; tabletTankIndex++) { //Mapping tank details in tablet

            var tabTankObjId = tabTankObj.tabTankList.obj[tabletTankIndex].id;
            var tabTankLength = tabTankObj.tabTankList.obj[tabletTankIndex].tank.length;
            var tabTankTheme = tabTankObj.tabTankList.obj[tabletTankIndex].theme;
						var tabTankBackground = tabTankObj.tabTankList.obj[tabletTankIndex].bgimage;

            if (tabletObjId == tabTankObjId) {
            	
            	$("#tankTheme" + tabletIndex).find('option[value="' + tabTankTheme + '"]').attr("selected", true);
							$("#tankBackground" + tabletIndex).find('option[value="' + tabTankBackground + '"]').attr("selected", true);

              var isUpdated = tabTankObj.tabTankList.obj[tabletTankIndex].update;
              if (isUpdated == 1) {
                  $("#tabletRow" + tabletIndex).addClass('isUpdatedTablet');
                  $("#tabletSync" + tabletIndex).attr("disabled", false);
                  $("#tabletSync" + tabletIndex).attr("checked", true);
                  if (tabTankLength == 0) {
	                    $("#tabletRow" + tabletIndex).addClass('isEmptyTablet');
	                    $("#tabletRow" + tabletIndex).attr("title", "No speices mapped for this tablet");
	                  }
                }

              for (var tankSpeIndex = 0; tankSpeIndex < tabTankLength; tankSpeIndex++) { //Mapping species details for a tank in tablet

                var tabTankSpeciesList = [];
                var tabTankOrder = tabTankObj.tabTankList.obj[tabletTankIndex].tank[tankSpeIndex].order;
                var tabTankName = tabTankObj.tabTankList.obj[tabletTankIndex].tank[tankSpeIndex].id;
                var tabletTankName = tabTankObj.tabTankList.obj[tabletTankIndex].tank[tankSpeIndex].name;
                var tabTankDirection = tabTankObj.tabTankList.obj[tabletTankIndex].tank[tankSpeIndex].direction;
								if(tabTankObj.tabTankList.obj[tabletTankIndex].tank[tankSpeIndex].species) {
									var tabTankSpeciesLength = tabTankObj.tabTankList.obj[tabletTankIndex].tank[tankSpeIndex].species.length;
								}							
                
                for (var speNameIndex = 0; speNameIndex < tabTankSpeciesLength; speNameIndex++) {
                  var tabTankSpeciesName = tabTankObj.tabTankList.obj[tabletTankIndex].tank[tankSpeIndex].species[speNameIndex].name;
                  tabTankSpeciesList.push("<button class='speciesNameList'>" + tabTankSpeciesName + "</button>");
                }
                var tabletTankSpeciesName = tabTankSpeciesList.join("");

                $("#tabletTankList" + tabletIndex).append('<tr class="odd" id="tankRow' + tabletIndex + tankSpeIndex +
                  '" >\
																	<td class="left w25"><select class="tabletTankList" id="dynamicTankList' +
                  tabletIndex + tankSpeIndex + '" style="width: 50%;"></select></td>\
																	<td class="speciesNameContainer left">' +
                  tabletTankSpeciesName +
                  '</td>\
																	<td class="w15 center">\
																		<select id="tabTankDirection' +
                  tabletIndex + tankSpeIndex +
                  '" name="location">\
																			<option value="">None</option>\
																			<option value="1">Right</option>\
																			<option value="2">Left</option>\
																		</select>\
																	</td>\
																	<td class="center w15" id="seqNumber' +
                  tabletIndex + tankSpeIndex + '">' + tabTankOrder +
                  '</td>\
																	<td class=" center w15">\
																		<input class="removeTankRow" type="button" title="Remove Tank">\
																	</td>\
																</tr>\
															'
                );
                $("#dynamicTankList" + tabletIndex + tankSpeIndex).append(tankListDropdown);
                if(tabTankSpeciesLength == 0) {
                	$("#dynamicTankList" + tabletIndex + tankSpeIndex).append('<option value=' + tabTankName + '>'+ tabletTankName +'</option>');
                	$("#dynamicTankList" + tabletIndex + tankSpeIndex).find('option[value = ' + tabTankName + ']').attr("selected", true);
                }else {
                	$("#dynamicTankList" + tabletIndex + tankSpeIndex).find('option[value = ' + tabTankName + ']').attr("selected", true);
                }
                $("#tabTankDirection" + tabletIndex + tankSpeIndex).find('option[value = ' + tabTankDirection + ']').attr("selected",
                  true);
              }
							var getSpeciesCount = $("#row" + tabletIndex + " .speciesNameList").length;
							if (getSpeciesCount == 0) {
									$("#tabletRow" + tabletIndex).addClass('isEmptyTablet');
									$("#tabletRow" + tabletIndex).attr("title", "No speices mapped for this tablet");
							}else {
									$("#tabletRow" + tabletIndex).removeClass('isEmptyTablet');
									$("#tabletRow" + tabletIndex).attr("title", "");
							}
            }
          }
        }
      }
			$("#cover").css("display", "none");
    },
    error: function() {
      swal("Alert!", "Service temporarily unavailable!");
			$("#cover").css("display", "none");
    }
  });
  chekAnyUpdates();
}

/* Click function to validate the publish tablets details */
jQuery('#publishTablet').live("click", function() {
	
	var tabletSpeciesCountArray = [];
	
	$("#tabletTank").find(".even.isUpdatedTablet").each(function() {
		
		  var parentId = $(this).attr('value');
		  var tabletName = $(this).find('th b').html();
		  var unCheckedTablet = $(this).find('input[name=tabletSync]:checked').length;
		  var tabletSpeciesCount = $('#row' + parentId + ' .tabletTankInnerTable tr .speciesNameList').length;
		  
		  if(unCheckedTablet != 0 && tabletSpeciesCount > 5) {
			  tabletSpeciesCountArray.push(" " + tabletName);
		  }
	});
	if(tabletSpeciesCountArray.length != 0) {
		speciesCountAlert(tabletSpeciesCountArray);
	}else {
		checkUnselectedTablet();
	}
});

/* Function to check the unselected tablet details */
function checkUnselectedTablet() {
	
  var countEmptyTab = $("#tabletTank tr.even.isUpdatedTablet.isEmptyTablet").length;
  var countEmptyCheckbox = $("#tabletTank tr.even.isUpdatedTablet.isEmptyTablet").find('input[name=tabletSync]:checked').length;

  if(countEmptyTab != 0 && countEmptyTab == countEmptyCheckbox) {
  swal({
        title: "Are you sure?",
        text: "No species mapped for highlighted red color tablets",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: 'Yes, I am sure!',
        cancelButtonText: "No, cancel it!"
      },
      function(isConfirm) {
        if (isConfirm) {
          publishTablet();
       }});
  }else {
    swal({
      title: "Are you sure?",
      text: "This will publish the selected tablet details",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: 'Yes, I am sure!',
      cancelButtonText: "No, cancel it!"
    },
    function(isConfirm) {
      if (isConfirm) {
        publishTablet()
      }});
  }
}

/* Function to alert the user if more than than 5 species mapped */
function speciesCountAlert(tabletName) {
	swal({
	      title: "Warning!",
	      text: 'Maximum of five species already found for the tablet(s): ('+ tabletName + ' )' ,
	      type: "warning"
	    });
}

/* API to Publish the updated tablet details in the core JSON collection */
function publishTablet() {
	
  var countUpdatedTab = $("#tabletTank tr.even.isUpdatedTablet").length;
  var countUpdateCheckbox = $("#tabletTank tr").find('input[name=tabletSync]:checked').length;
  if(countUpdatedTab != countUpdateCheckbox) {

    var unSelectedTabletObj = {};
    var unSelectedObj = {};
    var unSelectedTabletArray = [];
    $("#tabletTank").find("tr.even.isUpdatedTablet").each(function() {
      var currentDomObject = $(this);
      var getItem = currentDomObject.find('th .tabletSyncCheckbox').attr('checked');
      if(getItem == "checked") {
      }else {
        var getTabletImei = currentDomObject.find('th.tabletActionItems').attr('data');
        var getTabletId = currentDomObject.find('th.tabletActionItems').attr('value');
        
        unSelectedObj = {};
        unSelectedObj.imei = getTabletImei;
        unSelectedObj.id = getTabletId;
        
        unSelectedTabletArray.push(unSelectedObj);
      }
    });

    unSelectedTabletObj.unSelectedTablets = unSelectedTabletArray;
  }
  var unSelectedTabletList = JSON.stringify(unSelectedTabletObj);
    $.ajax({
      url: '/tank/tablettankmanager/publish-tablet',
      data: unSelectedTabletList,
      type: 'POST',
      dataType: 'json',
      contentType: 'application/json',
      async: false,
      success: function(result) {
        setTimeout(function() {
          swal({title: "Success!", text: result.message, timer: 3000, showConfirmButton: false, type: "success" });
        }, 500);
          loadTabletTankPage();
      }, 
      error: function(error) {
    	var error = JSON.parse(error.responseText);
        setTimeout(function() {
        swal({title: "Warning!", text: error.message, type: "warning" });
        }, 500);
      }
  });
}

/* Function to refresh the page */
function loadTabletTankPage() {
	$.ajax({
		url: '/atlantazoo/admin/tablettank/',
		type: 'GET',
		dataType: 'html',
		success: function(data) {
			$('#current-content').html($(data).find('#mpAdminTabletTank').html());
			fetchTankList();
		}
	});
}

jQuery('.tabletSyncCheckbox').live("change", function() {
	var currentDomObject = $(this);
	var parentId = currentDomObject.parent().parent().attr('value');
	var checkboxStatus = currentDomObject.prop('checked')
	var rowCss = $("#row" + parentId).css("display");

  if (rowCss == "none") {
    $("#row" + parentId).css("display", "table");
  } else if (rowCss == "table") {
    $("#row" + parentId).css("display", "none");
  }
  if(!checkboxStatus){
	  swal({
	        title: "Are you sure?",
	        text: "This will remove the tablet from sync process",
	        type: "warning",
	        showCancelButton: true,
	        confirmButtonText: 'Yes, I am sure!',
	        cancelButtonText: "No, cancel it!"
	      },
	      function(isConfirm) {
	        if (isConfirm) {
	        	$('#tabletSync'+parentId).attr('checked', false); 
	        }else {
	        	$('#tabletSync'+parentId).attr('checked', true); 
	        }
	    });
  }
	return false;  
});

/* show / hide function for tablet */
function showTablet(id) {
  jQuery("#row" + id).toggle();
}

/* Validation for unsaved changes for a particular tablet */
$('#tabletTank tr').live('change', function() {
	var currentDomObject = $(this);
	var parentId = '';
	var tabletHasChange = '';
	var isTabletUpdated = document.querySelectorAll('[data-attr="true"]');
	var getClassName = currentDomObject.closest('tr').attr('class');
	
	if(getClassName == "odd") {
		parentId = currentDomObject.closest('tr').parent().attr('value');
		tabletHasChange = $("#tabletRow" + parentId).attr('data-attr');
	}else if(getClassName == "even" || getClassName == "even isUpdatedTablet" || getClassName == "even isUpdatedTablet isEmptyTablet") {
		parentId = currentDomObject.closest('tr').attr('value');
		tabletHasChange = $("#tabletRow" + parentId).attr('data-attr');
	}
	
	if(tabletHasChange){
		if(isTabletUpdated.length == 1) {
			$('#tabletRow'+parentId).attr("data-attr", true); 
		}else if(isTabletUpdated.length > 1) {
			swal({title: "Warning!", text: 'Please save your changes in "'+ isTabletUpdated[0].innerText +'" tablet, then proceed', type: "warning" });
		}
		return false;
	}else {
		if(isTabletUpdated.length == 1) {
			swal({title: "Warning!", text: 'Please save your changes in "'+ isTabletUpdated[0].innerText +'" tablet, then proceed', type: "warning" });
		}else {
			$('#tabletRow'+parentId).attr("data-attr", true);
		}
		return false;
	}
});

jQuery('#tabletDeselectAll').live("change", function() {
	
	if(this.checked) {
		loadTabletTankPage();
	}else{
		swal({
	        title: "Are you sure?",
	        text: "This will remove all the tablets from publish",
	        type: "warning",
	        showCancelButton: true,
	        confirmButtonText: 'Yes, I am sure!',
	        cancelButtonText: "No, cancel it!"
	      },
	      function(isConfirm) {
	        if (isConfirm) {
	        	$('.tabletSyncCheckbox').attr('checked', false);
	        	$('#tabletDeselectAll').attr('disabled', true);

	        }else {
	        	$('.tabletSyncCheckbox').attr('checked', true);
	        }
	    });
	}
	
});
