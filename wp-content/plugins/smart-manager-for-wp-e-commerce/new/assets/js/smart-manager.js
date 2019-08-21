/**
 * Smart Manager JS class
 * Initialize and load data in grid
 * Public interface
 **/

function Smart_Manager() { 
	var currentDashboardModel='', dashboard_key= '', dashboard_display_title= '', dashboard_select_options= '',sm_nonce= '', column_names= new Array(), simpleSearchText = '', advancedSearchQuery= new Array(), post_data_params = '', 
		month_names_short = '', search_count, state_apply, dashboard_states = {}, skip_default_action, current_selected_dashboard = '';
}

Smart_Manager.prototype.init = function() {

	this.currentDashboardModel='';
	this.dashboard_key= '';
	this.dashboard_display_title= '';
	this.dashboard_select_options= '';
	this.sm_nonce= '';
	this.column_names= new Array();
	this.advancedSearchQuery= new Array();
	this.simpleSearchText = '';
	this.post_data_params = '';
	this.month_names_short = '';
	this.dashboardStates = {};
	this.current_selected_dashboard = '';
	this.currentDashboardData = [];
	this.currentVisibleColumns = new Array('');
	this.editedData = {};
	this.editedCellIds = [];
	this.selectedIds = [];
	this.duplicateStore = false;
	this.selectAll = false;
	this.headerIschecked = false;
	this.batch_update_action_options_default = '';
	this.batch_update_actions = [];
	this.batch_update_copy_from_record_ids = [];
	this.sm_beta_smart_date_filter = '';
	this.addRecords_count = 0;
	this.defaultColumnsAddRow = new Array('posts_post_status', 'posts_post_title', 'posts_post_content');
	this.columnsVisibilityUsed = false; // flag for handling column visibility
	this.totalRecords = 0;
	this.hotPlugin = {}; //object containing all Handsontable plugins
	this.gettingData = 0;
	this.searchType = sm_beta_params.search_type;
	this.advancedSearchContent = '';
	this.simpleSearchContent = '';
	this.searchTimeoutId = 0;
	this.columnSort = false;
	this.defaultEditor = true;
	this.currentGetDataParams = {};
	this.modifiedRows = [];
	this.highlightedRowIds = {};
	this.dirtyRowColIds = {};
	this.wpToolsPanelWidth = 0;
	this.kpiData = {};

	this.state_apply = false;
	this.skip_default_action = false;
	this.search_count = 0;
	this.page = 1;
	this.hideDialog = '';
	this.multiselect_chkbox_list = '';
	this.limit = sm_beta_params.record_per_page;
	this.sm_dashboards_combo = '', // variable to store the dashboard name;
	this.column_names_batch_update = new Array(), // array for storing the batch update field;
	this.sm_store_table_model = new Array(), // array for storing store table mode;
	this.lastrow = '1';
	this.lastcell = '1';
	this.grid_width = '750';
	this.grid_height = '600';
	this.sm_ajax_url = (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=sm_beta_include_file' : ajaxurl + '?action=sm_beta_include_file';

	this.sm_qtags_btn_init = 1;
	this.sm_grid_nm = 'sm_editor_grid'; //name of div containing jqgrid
	this.sm_wp_editor_html = ''; //variable for storing the html of the wp editor
	this.sm_last_edited_row_id = '';
	this.sm_last_edited_col = '';
	this.col_model_search = '';
	this.currentColModel = '';

	//defining default actions for batch update
	this.batch_update_action_string = {set_to:'set to', prepend:'prepend', append:'append', search_and_replace:'search & replace'};
	this.batch_update_action_number = {set_to:'set to', increase_by_per:'increase by %', decrease_by_per:'decrease by %', increase_by_num:'increase by number', decrease_by_num:'decrease by number'};
	this.batch_update_action_datetime = {set_datetime_to:'set datetime to', set_date_to:'set date to', set_time_to:'set time to'};

	this.batch_background_process = sm_beta_params.batch_background_process;
	this.sm_success_msg = sm_beta_params.success_msg;
	this.background_process_name = sm_beta_params.background_process_name;
	this.sm_updated_sucessfull = parseInt(sm_beta_params.updated_sucessfull);
	this.sm_updated_msg = sm_beta_params.updated_msg;
	this.sm_dashboards = sm_beta_params.sm_dashboards;
	this.sm_dashboards_public = sm_beta_params.sm_dashboards_public;
	this.sm_lite_dashboards = sm_beta_params.lite_dashboards;
	this.sm_admin_email = sm_beta_params.sm_admin_email;
	this.sm_deleted_sucessfull = parseInt(sm_beta_params.deleted_sucessfull);

	this.sm_is_woo30 = sm_beta_params.SM_IS_WOO30;
	this.sm_id_woo22 = sm_beta_params.SM_IS_WOO22;
	this.sm_is_woo21 = sm_beta_params.SM_IS_WOO21;
	this.sm_beta_pro = sm_beta_params.SM_BETA_PRO;

	this.wpDbPrefix = sm_beta_params.wpdb_prefix;

	this.window_width = jQuery(window).width();
	this.window_height = jQuery(window).height();

	this.pricingPageURL = location.href + '-pricing';
	
	this.month_names_short = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

	//Code for setting the default dashboard
	if( typeof this.sm_dashboards != 'undefined' && this.sm_dashboards != '' ) {
		this.sm_dashboards_combo = this.sm_dashboards = jQuery.parseJSON(this.sm_dashboards);
		this.sm_lite_dashboards = jQuery.parseJSON(this.sm_lite_dashboards);
		this.current_selected_dashboard = this.sm_dashboards['default'];
		this.dashboard_key = this.sm_dashboards['default'];
		this.dashboard_display_title = this.sm_dashboards['default_dashboard_title'];

		this.sm_nonce = this.sm_dashboards['sm_nonce'];
		delete this.sm_dashboards['sm_nonce'];
	}

	this.container = document.getElementById('sm_editor_grid');

	this.body_font_size = jQuery("body").css('font-size');
	this.body_font_family = jQuery("body").css('font-family');


	//Function to set all the states on unload
	window.onbeforeunload = function (evt) { 
		if ( typeof (window.smart_manager.updateState) !== "undefined" && typeof (window.smart_manager.updateState) === "function" ) {
			window.smart_manager.updateState({'async': false}); //refreshing the dashboard states
		}
	}

	if ( !jQuery(document.body).hasClass('folded') && window.smart_manager.sm_beta_pro == 1 ) {
		jQuery(document.body).addClass('folded');
	}

	if ( !jQuery(document.body).hasClass('folded') ) {
		window.smart_manager.grid_width = document.documentElement.offsetWidth - 200;
	} else {
		window.smart_manager.grid_width = document.documentElement.offsetWidth - 80;
	}
	window.smart_manager.grid_height = document.documentElement.offsetHeight - 360;
	
	window.smart_manager.load_dashboard();
	window.smart_manager.event_handler();

	jQuery('#sm_editor_grid').trigger( 'smart_manager_init' ); //custom trigger
}


Smart_Manager.prototype.convert_to_slug = function(text) {
	return text
		.toLowerCase()
		.replace(/ /g,'-')
		.replace(/[^\w-]+/g,'');
}

Smart_Manager.prototype.convert_to_pretty_text = function(text) {
	return text
		.replace(/_/g,' ')
		.split(' ')
	    .map((s) => s.charAt(0).toUpperCase() + s.substring(1))
	    .join(' ');
}

Smart_Manager.prototype.load_dashboard = function() {

	jQuery('#sm_editor_grid').trigger( 'smart_manager_pre_load_dashboard' ); //custom trigger

	window.smart_manager.page = 1;

	if( typeof(window.smart_manager.currentDashboardModel) == 'undefined' || window.smart_manager.currentDashboardModel == '' ) {
		window.smart_manager.column_names = new Array('');
		window.smart_manager.column_names_batch_update = new Array();

		window.smart_manager.getDashboardModel();
	} else {
		window.smart_manager.getData();
	}

}

// Function to load top right bar on the page
Smart_Manager.prototype.loadTopBar = function() {
	let sm_top_bar_exists = jQuery('#sm_top_bar');

	if( sm_top_bar_exists.length == 0 ) {

		let selected = '',
			switchSearchType = ( window.smart_manager.searchType == 'simple' ) ? 'Advanced' : 'Simple';

		window.smart_manager.dashboard_select_options = '';

		window.smart_manager.simpleSearchContent = "<input type='text' id='sm_simple_search_box' placeholder='Type to search...'>";
		window.smart_manager.advancedSearchContent = "<div style='width: 100%;'> <div id='sm_advanced_search_box' style='float:left;width:74.85%' > <div id='sm_advanced_search_box_0' style='width:100%;margin-bottom:0.5em;'> </div>"+
													"<input type='text' id='sm_advanced_search_box_value_0' name='sm_advanced_search_box_value_0' hidden> </div>"+ 
													"<input type='text' id='sm_advanced_search_query' hidden>"+
													"<div id='sm_advanced_search_or' style='float: left;margin-top: 0.2em;margin-left: 0.25em;/* opacity: 0.85; */cursor: pointer;color: #3892D3;font-size: 2em;' class='dashicons dashicons-plus' title='Add Another Condition'> </div>"+
													"<div style='float: left;margin-left: 1em;cursor: pointer;line-height:0em;'><button id='sm_advanced_search_submit' title='Search' style='height: 2.5em;border: 1px solid #3892D3;background-color: white;border-radius: 3px;cursor: pointer;line-height: 1.5em;color: #515151;'><span class='dashicons dashicons-search' style='color:#3892D3;font-size: 18px;margin-left: -5px;'></span> Search </button> </div>"+
												"</div> ";

		for (let key in window.smart_manager.sm_dashboards) {

			if( key == 'default' || key == 'default_dashboard_title' ) {
				continue;
			    }

			selected = '';

			if (key == window.smart_manager.dashboard_key) {
				selected = "selected";
			}
			window.smart_manager.dashboard_select_options += '<option value="'+key+'" '+selected+'>'+window.smart_manager.sm_dashboards[key]+'</option>'
		}

		if( window.smart_manager.sm_beta_pro == 0 ) { 
			window.smart_manager.sm_beta_smart_date_filter = '<div class="sm_date_range_container">'+
																'<div>'+
																	'<input placeholder="Created Start Date" class="sm_date_selector start-date" id="sm_date_selector_start_date" title="Click to edit created start date" readonly>'+
																'</div>'+
																'<div class="date-separator">-</div>'+
																'<div>'+
																	'<input placeholder="Created End Date" class="sm_date_selector end-date" id="sm_date_selector_end_date" title="Click to edit created end date" readonly>'+
																'</div>'+
																'<div class="dropdown">'+
																	'<span class="dashicons dashicons-arrow-down-alt2 smart-date-icon dropdown-toggle" id="smartDatesDropdown" title="Click to select a pre-defined created date range"></span>'+
																'</div>'+
															'</div>';

			jQuery(document).off('click', '#sm_top_bar_simple_search .sm_date_range_container').on('click', '#sm_top_bar_simple_search .sm_date_range_container', function() {
				window.smart_manager.showNotification();
			})
		}

		let sm_top_bar = "<div id='sm_top_bar' style='font-weight:400 !important;width:"+window.smart_manager.grid_width+"px;'>"+
							"<div id='sm_top_bar_actions' class='sm_beta_left' style='width:100%;'>"+
								"<div id='sm_top_bar_action_btns' class='sm_beta_left' style='width:32%;'>"+
									"<div id='sm_top_bar_action_btns_basic' class='sm_beta_left sm_top_bar_action_btns'>"+
										"<div id='add_sm_editor_grid' title='Add Row' ><span id='add_sm_editor_grid' title='Add Row' class='dashicons dashicons-plus sm_top_bar_action_btns_dashicons'></span> Add Row </div>"+
										"<div id='save_sm_editor_grid_btn' title='Save' ><img class='ui-icon sm-ui-state-disabled sm_beta_left save_sm_editor_grid sm_top_bar_action_btns_dashicons' /> Save </div>"+
										"<div id='del_sm_editor_grid' title='Delete Selected Row' ><span class='sm-ui-state-disabled dashicons dashicons-trash sm_error_icon sm_top_bar_action_btns_dashicons'></span> Delete </div>"+
									"</div>"+
									"<div id='sm_top_bar_action_btns_update' class='sm_beta_left sm_top_bar_action_btns'>"+
										"<div id='batch_update_sm_editor_grid' title='Batch Update' ><span class='dashicons dashicons-images-alt2 sm_top_bar_action_btns_dashicons'></span> Batch Update </div>"+
										"<div id='export_csv_sm_editor_grid' title='Export CSV' ><span class='dashicons dashicons-download sm_top_bar_action_btns_dashicons'></span> Export CSV </div>"+
										"<div class='sm_beta_dropdown' style='float:left;cursor:pointer;'> <span class='dashicons dashicons-admin-page sm_beta_dup_btn sm_top_bar_action_btns_dashicons' title='Duplicate'></span>Duplicate <div class='sm_beta_dropdown_content'><a id='sm_beta_dup_selected' href='#'>Selected Records</a><a id='sm_beta_dup_entire_store' href='#'>Entire Store</a></div></div>"+
									"</div>"+
									"<div id='sm_top_bar_action_btns_misc' class='sm_beta_left sm_top_bar_action_btns'>"+
										"<div id='refresh_sm_editor_grid' title='Refresh' ><span class='dashicons dashicons-update sm_top_bar_action_btns_dashicons'></span> Refresh </div>"+
										"<div id='show_hide_cols_sm_editor_grid' title='Show / Hide Columns' ><span class='dashicons dashicons-admin-generic sm_top_bar_action_btns_dashicons'></span> Columns </div>"+
										"<div id='print_invoice_sm_editor_grid_btn' title='Print Invoice' ><img class='ui-icon sm-ui-state-disabled sm_beta_left print_invoice_sm_editor_grid sm_top_bar_action_btns_dashicons' /> Print Invoice </div>"+
									"</div>"+
								"</div>"+	
								"<div id='sm_top_bar_search' class='sm_beta_left' style='width:62%;'>"+
									"<div id='sm_top_bar_simple_search' class='sm_beta_left'>"+
										window.smart_manager.sm_beta_smart_date_filter+
										"<div style='width:100%;height:4em;overflow-y:auto;'>"+
											"<div id='search_content' style='width:80%;'>"+
												( ( window.smart_manager.searchType == 'simple' ) ? window.smart_manager.simpleSearchContent : window.smart_manager.advancedSearchContent )+
											"</div>"+
										"</div>"+
									"</div>"+
									"<div id='sm_top_bar_advanced_search' class='sm_beta_left'>"+
										"<div> <input type='checkbox' id='search_switch' switchSearchType='"+ switchSearchType +"' /><label title='Switch to "+ switchSearchType +"' for='search_switch'> "+ switchSearchType +" Search </label></div>"+
										"<div id='search_switch_lbl'> "+ String(switchSearchType).capitalize() +" Search </div>"+
									"</div>"+

								"</div>"+	
							"</div>"+
							"<div id='sm_top_bar_left' class='sm_beta_left' style='width:98.5%;background-color: white;padding: 0.5em 1em 1em 1em;'>"+
								"<div class='sm_beta_left'> <select id='sm_dashboard_select'> </select> </div>"+
								"<div id='sm_beta_display_records' class='sm_beta_left sm_beta_select_blue'></div>"+
								"<div class='sm_beta_right' style='cursor: pointer;line-height:0em;margin-top: 0;padding-top:0.5em;' title='Enter Distraction Free Mode'><button id='sm_editor_grid_distraction_free_mode' style='height:2em;border: 1px solid #3892D3;background-color: white;border-radius: 3px;cursor: pointer;line-height: 17px;color:#3892D3;'> <span class='dashicons dashicons-editor-expand' style='font-weight:bold;'></span> </button> </div>"+
							"</div>"+
						"</div>";

		let sm_bottom_bar = "<div id='sm_bottom_bar' style='font-weight:500 !important;color:#3892D3;width:"+window.smart_manager.grid_width+"px;'>"+
							"<div id='sm_bottom_bar_left' class='sm_beta_left'>"+
								// "<div class='sm_beta_left' style='cursor: pointer;line-height:0em;margin-top: 0;' title='Enter Distraction Free Mode'><button id='sm_editor_grid_distraction_free_mode' style='height:2em;border: 1px solid #3892D3;background-color: white;border-radius: 3px;cursor: pointer;line-height: 17px;color:#3892D3;'> <span class='dashicons dashicons-editor-expand' style='font-weight:bold;'></span> </button> </div>"+
								"<div class='sm_beta_left' style='cursor: pointer;line-height:0em;margin-top: 0;margin-left: 1em;display:none;' title='Load more records'><button id='sm_editor_grid_load_items' style='height:2em;border: 1px solid #3892D3;background-color: white;border-radius: 3px;cursor: pointer;line-height: 17px;color:#3892D3;'> Load More </button> </div>"+
							"</div>"+
							"<div id='sm_bottom_bar_right' class='sm_beta_right'>"+
								"<div class='sm_beta_right' style='font-style:italic;'>Scroll to view more records</div>"+
							"</div>"+
						"</div>";

		jQuery(sm_top_bar).insertBefore("#sm_editor_grid");
		jQuery(sm_bottom_bar).insertAfter("#sm_editor_grid");
		jQuery('#sm_dashboard_select').empty().append(window.smart_manager.dashboard_select_options);
		jQuery('#sm_dashboard_select').select2({ width: '15em', dropdownCssClass: 'sm_beta_batch_update_field', dropdownParent: jQuery('#sm_top_bar_left') });
	}

	//Code for Dashboard KPI
	jQuery('#sm_dashboard_kpi').remove();

	if( Object.entries(window.smart_manager.kpiData).length > 0 ) {
		let kpi_html = new Array();

		Object.entries(window.smart_manager.kpiData).forEach(([kpiTitle, kpiObj]) => {
			kpi_html.push('<span class="sm_beta_select_'+ ( ( kpiObj.hasOwnProperty('color') !== false && kpiObj['color'] != '' ) ? kpiObj['color'] : 'grey' ) +'"> '+ kpiTitle +'('+ ( ( kpiObj.hasOwnProperty('count') !== false ) ? kpiObj['count'] : 0 ) +') </span>');
		});

		if( kpi_html.length > 0 ) {
			jQuery('<div id="sm_dashboard_kpi" class="sm_beta_left">'+ kpi_html.join("<span class='sm_separator'> | </span>") +'</div>' ).insertAfter('#sm_beta_display_records');
		}
	}

	if( window.smart_manager.searchType != 'simple' ) {
		window.smart_manager.initialize_advanced_search(); //initialize advanced search control
	}

	if ( window.smart_manager.dashboard_key == 'shop_order' ) {
		jQuery('#print_invoice_sm_editor_grid_btn').show();
	} else {
		jQuery('#print_invoice_sm_editor_grid_btn').hide();
	}

	jQuery('#sm_top_bar').trigger('sm_top_bar_loaded');
}

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

Smart_Manager.prototype.initialize_advanced_search = function() {

	if( typeof(window.smart_manager.currentColModel) == 'undefined' ) {
		return;
	}

	let colModel = JSON.parse( JSON.stringify( window.smart_manager.currentColModel ) );

	window.smart_manager.col_model_search = Object.entries(colModel).map(([key, obj]) => {

		if( obj.hasOwnProperty('searchable') && obj.searchable == 1 ) { 

			if( obj.type == 'checkbox' ) {
				obj.type = 'dropdown';
				if( !(obj.hasOwnProperty('checkedTemplate') && obj.hasOwnProperty('uncheckedTemplate')) ) {
					obj.checkedTemplate = 'true';
					obj.uncheckedTemplate = 'false';
				}
				
				obj.search_values = new Array({'key': obj.checkedTemplate, 'value':  String(obj.checkedTemplate).capitalize()},
													{'key': obj.uncheckedTemplate, 'value':  String(obj.uncheckedTemplate).capitalize()});
			}
		
			return obj; 
		}
	});

	window.smart_manager.col_model_search = window.smart_manager.col_model_search.filter(function( element ) {
	   return element !== undefined;
	});

	var visualsearch_params = {};
	
	window.smart_manager.search_count = 1;

	visualsearch_params  = {
							el      : jQuery("#sm_advanced_search_box_0"),
							placeholder: "Enter your search conditions here!",
							strict: false,
							search: function(json){
								window.smart_manager.advancedSearchQuery[0] = json;
								jQuery("#sm_advanced_search_box_value_0").val(json);
							},
							parameters: window.smart_manager.col_model_search
						};

	if( window.smart_manager.advancedSearchQuery[0] != '' && typeof(window.smart_manager.advancedSearchQuery[0]) != 'undefined'  ) {
		visualsearch_params.defaultquery = JSON.parse(window.smart_manager.advancedSearchQuery[0]);
		jQuery("#sm_advanced_search_box_value_0").val(window.smart_manager.advancedSearchQuery[0]);
	}                            

	window.visualSearch = new VisualSearch(visualsearch_params);

	if( window.smart_manager.sm_beta_pro == 1 ) { //handling multiple search conditions for pro
		if( window.smart_manager.advancedSearchQuery[0] != '' && typeof(window.smart_manager.advancedSearchQuery[0]) != 'undefined' && window.smart_manager.advancedSearchQuery.length > 1 ) { //for search

			for(let i=0; i<window.smart_manager.advancedSearchQuery.length-1; i++) {
				window.smart_manager.search_count = i;
				if ( typeof window.smart_manager.addAdvancedSearchCondition !== "undefined" && typeof window.smart_manager.addAdvancedSearchCondition === "function" ) {
					window.smart_manager.addAdvancedSearchCondition();
				}
			}    
		}    
	}

}

Smart_Manager.prototype.showLoader = function( is_show = true ) {
	if ( is_show ) {
		jQuery('.sm-loader-container').hide().show();
	} else {
		jQuery('.sm-loader-container').hide();
	}
}

Smart_Manager.prototype.send_request = function(params, callback, callbackParams) {

	if( typeof params.showLoader == 'undefined' || (typeof params.showLoader != 'undefined' && params.showLoader !== false ) ) {
		window.smart_manager.showLoader();
	}
	
	jQuery.ajax({
		type : ( ( typeof(params.call_type) != 'undefined' ) ? params.call_type : 'POST' ),
		url : ( (typeof(params.call_url) != 'undefined' ) ? params.call_url : window.smart_manager.sm_ajax_url ),
		dataType: ( ( typeof(params.data_type) != 'undefined' ) ? params.data_type : 'text' ),
		async: ( ( typeof(params.async) != 'undefined' ) ? params.async : 'async' ),
		data: params.data,
		success: function(resp) {
			if( typeof params.showLoader == 'undefined' || (typeof params.showLoader != 'undefined' && params.showLoader !== false ) ) {
				window.smart_manager.showLoader(false);
			}
			return ( ( typeof(callbackParams) != 'undefined' ) ? callback(callbackParams, resp) : callback(resp) );
		}
	});

}

//function to format the column model
Smart_Manager.prototype.format_dashboard_column_model = function( column_model ) {

	if( window.smart_manager.currentColModel == '' || typeof(window.smart_manager.currentColModel) == 'undefined' ) {
		return;
	}

	let chkbox_col = {
						data: 'active',
						type: 'checkbox',
						className: 'htCenter htMiddle smCheckboxColumnModel',
						key: 'row_select_checkbox'
					},
		index = 1;

	if ( typeof (window.smart_manager.sortColumns) !== "undefined" && typeof (window.smart_manager.sortColumns) === "function" ) {
		window.smart_manager.sortColumns();
	}

	window.smart_manager.currentColModel.unshift(chkbox_col);
	window.smart_manager.column_names = new Array('');
	window.smart_manager.currentVisibleColumns = new Array('');

	for (i = 1; i < window.smart_manager.currentColModel.length; i++) {

			if( typeof(window.smart_manager.currentColModel[i]) == 'undefined' ) {
				continue;
			}

			hidden = ( typeof(window.smart_manager.currentColModel[i].hidden) != 'undefined' ) ? window.smart_manager.currentColModel[i].hidden : true;

			column_values = (typeof(window.smart_manager.currentColModel[i].values) != 'undefined') ? window.smart_manager.currentColModel[i].values : '';

			type = (typeof(window.smart_manager.currentColModel[i].type) != 'undefined') ? window.smart_manager.currentColModel[i].type : '';
			selectOptions = (typeof(window.smart_manager.currentColModel[i].selectOptions) != 'undefined') ? window.smart_manager.currentColModel[i].selectOptions : '';

			let name = '';

			if( typeof( window.smart_manager.currentColModel[i].name ) != 'undefined' ) {
				name = ( window.smart_manager.currentColModel[i].name ) ? window.smart_manager.currentColModel[i].name.trim() : '';
			}

			if(window.smart_manager.currentColModel[i].hasOwnProperty('name_display') === false) {// added for state management
				window.smart_manager.currentColModel[i].name_display = name;
			}

			if( hidden === false ) {
				window.smart_manager.column_names[index] = window.smart_manager.currentColModel[i].name_display; //Array for column headers
				window.smart_manager.currentVisibleColumns[index] = window.smart_manager.currentColModel[i];
				index++;
			}

			var batch_enabled_flag = false;

			if (window.smart_manager.currentColModel[i].hasOwnProperty('batch_editable')) {
				batch_enabled_flag = window.smart_manager.currentColModel[i].batch_editable;
			}

			if (batch_enabled_flag === true) {
				window.smart_manager.column_names_batch_update[window.smart_manager.currentColModel[i].src] = {name: window.smart_manager.currentColModel[i].name_display, type:window.smart_manager.currentColModel[i].type, values:column_values, src:window.smart_manager.currentColModel[i].data};

				if( window.smart_manager.currentColModel[i].type == 'checkbox' ) {
					window.smart_manager.column_names_batch_update[window.smart_manager.currentColModel[i].src]['checkedTemplate'] = 'true';
					window.smart_manager.column_names_batch_update[window.smart_manager.currentColModel[i].src]['uncheckedTemplate'] = 'false';

					if( window.smart_manager.currentColModel[i].hasOwnProperty('checkedTemplate') ) {
						window.smart_manager.column_names_batch_update[window.smart_manager.currentColModel[i].src]['checkedTemplate'] = window.smart_manager.currentColModel[i]['checkedTemplate'];
					}

					if( window.smart_manager.currentColModel[i].hasOwnProperty('uncheckedTemplate') ) {
						window.smart_manager.column_names_batch_update[window.smart_manager.currentColModel[i].src]['uncheckedTemplate'] = window.smart_manager.currentColModel[i]['uncheckedTemplate'];
					}
				}

			}

			if ( typeof(window.smart_manager.currentColModel[i].allow_showhide) != 'undefined' && window.smart_manager.currentColModel[i].allow_showhide === false ) {
				window.smart_manager.currentColModel[i].hidedlg = true;
			}
			
			window.smart_manager.currentColModel[i].name = window.smart_manager.currentColModel[i].index;

			// setting the default width
			if (typeof(window.smart_manager.currentColModel[i].width) == 'undefined') {
				// window.smart_manager.currentColModel[i].width = 80;
			}

			//Code for formatting the values
			var formatted_values = '';
			window.smart_manager.currentColModel[i].wordWrap = true;

	}
}

Smart_Manager.prototype.setDashboardModel = function (response) {

	if( typeof response != 'undefined' && response != '' ) {
		window.smart_manager.sm_store_table_model = response.tables;
		window.smart_manager.currentColModel = response.columns;

		//call to function for formatting the column model
		if( typeof( window.smart_manager.format_dashboard_column_model ) !== "undefined" && typeof( window.smart_manager.format_dashboard_column_model ) === "function" ) {
			window.smart_manager.format_dashboard_column_model();
		}

		response.columns = window.smart_manager.currentColModel;
		window.smart_manager.currentDashboardModel = response;
		window.smart_manager.getData();
	}
}

Smart_Manager.prototype.getDashboardModel = function () {

	window.smart_manager.currentDashboardModel = '';

	// Ajax request to get the dashboard model
	let params = {};
		params.data_type = 'json';
		params.data = {
						cmd: 'get_dashboard_model',
						security: window.smart_manager.sm_nonce,
						active_module: window.smart_manager.dashboard_key,
						is_public: ( window.smart_manager.sm_dashboards_public.indexOf(window.smart_manager.dashboard_key) != -1 ) ? 1 : 0,
						active_module_title: window.smart_manager.dashboard_display_title
					};

	window.smart_manager.send_request(params, window.smart_manager.setDashboardModel);
}

Smart_Manager.prototype.set_data = function(response) {
	if( typeof response != 'undefined' && response != '' ) {
		if( response != 'null' ) {
			var res = JSON.parse(response);

			if( res.hasOwnProperty('items') ) { //code for inserting a blank value for the checkbox col model
				Object.entries(res.items).forEach(([key, value]) => {
					res.items[key]['custom_row_select_checkbox'] = '';
				});
			}

			window.smart_manager.totalRecords = res.total_count;

			if( window.smart_manager.page > 1 ) {
			
				let lastRowIndex = window.smart_manager.currentDashboardData.length;
				window.smart_manager.currentDashboardData = window.smart_manager.currentDashboardData.concat(res.items);

				if( window.smart_manager.page > 1 ) {

					window.smart_manager.hot.loadData(window.smart_manager.currentDashboardData);

					//call to function for highlighting selected row ids
					if( typeof( window.smart_manager.highlightSelectedRows ) !== "undefined" && typeof( window.smart_manager.highlightSelectedRows ) === "function" ) {
						window.smart_manager.highlightSelectedRows();
					}

					//call to function for highlighting edited cell ids
					if( typeof( window.smart_manager.highlightEditedCells ) !== "undefined" && typeof( window.smart_manager.highlightEditedCells ) === "function" ) {
						window.smart_manager.highlightEditedCells();
					}

					if( window.smart_manager.sm_beta_pro == 0 ) {
						if( typeof( window.smart_manager.modifiedRows ) != 'undefined' ) {
							if( window.smart_manager.modifiedRows.length >= window.smart_manager.sm_updated_sucessfull ) {
								//call to function for highlighting selected row ids
								if( typeof( window.smart_manager.disableSelectedRows ) !== "undefined" && typeof( window.smart_manager.disableSelectedRows ) === "function" ) {
									window.smart_manager.disableSelectedRows(true);
								}
							}
						}
					}

					window.smart_manager.hot.render();
					window.smart_manager.hot.scrollViewportTo(lastRowIndex, 0);
				}
			} else {
				window.smart_manager.currentDashboardData = ( window.smart_manager.totalRecords > 0 ) ? res.items : [];

				if( res.hasOwnProperty('batch_update_copy_from_record_ids') ) {
					window.smart_manager.batch_update_copy_from_record_ids = res.batch_update_copy_from_record_ids;
				}
			}
		} else {
			window.smart_manager.currentDashboardData = [];
		}

		if( window.smart_manager.page == 1 ) {
			if( window.smart_manager.columnSort ) {
				window.smart_manager.hot.loadData(window.smart_manager.currentDashboardData);
				window.smart_manager.hot.scrollViewportTo(0, 0);
			} else {

				if( res.hasOwnProperty('kpi_data') ) {
					window.smart_manager.kpiData = res.kpi_data;
				} else {
					window.smart_manager.kpiData = {};
				}

				window.smart_manager.loadGrid();
				window.smart_manager.loadTopBar();    	
			}
			
		} 

		window.smart_manager.refreshBottomBar();

		if( window.smart_manager.currentDashboardData.length >= window.smart_manager.totalRecords ) {
			jQuery('#sm_editor_grid_load_items').attr('disabled','disabled');
			jQuery('#sm_editor_grid_load_items').addClass('sm-ui-state-disabled');
		} else {
			jQuery('#sm_editor_grid_load_items').removeAttr('disabled');
			jQuery('#sm_editor_grid_load_items').removeClass('sm-ui-state-disabled');
		}

		window.smart_manager.gettingData = 0;
	}
}

//Function to refresh the bottom bar of grid
Smart_Manager.prototype.refreshBottomBar = function() {
	// let msg = ( window.smart_manager.currentDashboardData.length > 0 ) ? "Displaying 1 - "+ window.smart_manager.currentDashboardData.length +" of "+ window.smart_manager.totalRecords +" "+ window.smart_manager.dashboard_display_title : 'No '+ window.smart_manager.dashboard_display_title +' Found';
	let msg = ( window.smart_manager.currentDashboardData.length > 0 ) ? window.smart_manager.totalRecords +" "+ window.smart_manager.dashboard_display_title : 'No '+ window.smart_manager.dashboard_display_title +' Found';
	jQuery('#sm_top_bar_left #sm_beta_display_records').html(msg);
}


Smart_Manager.prototype.getDataDefaultParams = function(params) {

	let defaultParams = {};
		defaultParams.data = {
						  cmd: 'get_data_model',
						  active_module: window.smart_manager.dashboard_key,
						  security: window.smart_manager.sm_nonce,
						  is_public: ( window.smart_manager.sm_dashboards_public.indexOf(window.smart_manager.dashboard_key) != -1 ) ? 1 : 0,
						  page: window.smart_manager.page,
						  limit: window.smart_manager.limit,
						  SM_IS_WOO30: window.smart_manager.sm_is_woo30,
						  sort_params: (window.smart_manager.currentDashboardModel.hasOwnProperty('sort_params') ) ? window.smart_manager.currentDashboardModel.sort_params : '',
						  table_model: (window.smart_manager.currentDashboardModel.hasOwnProperty('tables') ) ? window.smart_manager.currentDashboardModel.tables : '',
						  search_text: window.smart_manager.simpleSearchText
					  };

	defaultParams.data['search_query[]'] = window.smart_manager.advancedSearchQuery;

	if( typeof params != 'undefined' ) {
		if( Object.getOwnPropertyNames(params).length > 0 ) {
			defaultParams = Object.assign(params.data, defaultParams.data);
			defaultParams = Object.assign(params, defaultParams);    
		}    
	}

	window.smart_manager.currentGetDataParams = defaultParams;
}

Smart_Manager.prototype.getData = function(params) {

	window.smart_manager.gettingData = 1;

	if( window.smart_manager.page == 1 ) {
		if ( typeof (window.smart_manager.getDataDefaultParams) !== "undefined" && typeof (window.smart_manager.getDataDefaultParams) === "function" ) {
			window.smart_manager.getDataDefaultParams(params);
		}
	} else {
		if( typeof(window.smart_manager.currentGetDataParams.data) != 'undefined' && typeof(window.smart_manager.currentGetDataParams.data.page) != 'undefined' ) {
			window.smart_manager.currentGetDataParams.data.page = window.smart_manager.page;
			window.smart_manager.currentGetDataParams.data.sort_params = window.smart_manager.currentDashboardModel.sort_params;
		}
	}

	window.smart_manager.send_request(window.smart_manager.currentGetDataParams, window.smart_manager.set_data);
}

Smart_Manager.prototype.inline_edit_dlg = function(params) {

		if (params.dlg_width == '' || typeof (params.dlg_width) == 'undefined') {
			modal_width = 350;
		} else {
			modal_width = params.dlg_width;
		}

		if (params.dlg_height == '' || typeof (params.dlg_height) == 'undefined') {
			modal_height = 390;
		} else {
			modal_height = params.dlg_height;
		}

		let ok_btn = [{
					  text: "OK",
					  class: 'sm_inline_dialog_ok sm-dlg-btn-yes',
					  click: function() {
						jQuery( this ).dialog( "close" );
					  }
					}];

		jQuery( "#sm_inline_dialog" ).html(params.content);

		let dialog_prams = {
								closeOnEscape: true,
								draggable: false,
								height: modal_height,
								width: modal_width,
								modal: (params.hasOwnProperty('modal')) ? params.modal : false,
								position: {my: ( params.hasOwnProperty('position_my') ) ? params.position_my : 'left center+250px',
											at: ( params.hasOwnProperty('position_my') ) ? params.position_at : 'left center', 
											of: params.target},
								create: function (event, ui) {
									if( !(params.hasOwnProperty('title') && params.title != '') ) {
										jQuery(".ui-widget-header").hide();
									}
								},
								open: function() {

									if( params.hasOwnProperty('show_close_icon') && params.show_close_icon === false ) {
										jQuery(this).find('.ui-dialog-titlebar-close').hide();
									}

									jQuery('.ui-widget-overlay').bind('click', function() { 
									    jQuery('#sm_inline_dialog').dialog('close'); 
									});

									if( !(params.hasOwnProperty('title') && params.title != '') ) {
										jQuery(".ui-widget-header").hide();
									} else if( (params.hasOwnProperty('title') && params.title != '') ) {
										jQuery(".ui-widget-header").show();
									}

									jQuery(this).html(params.content);
								},
								close: function(event, ui) { 
									jQuery(this).dialog('close');
								},
							  buttons: ( params.hasOwnProperty('display_buttons') && params.display_buttons === false ) ? [] : ( params.hasOwnProperty('buttons_model') ? params.buttons_model : ok_btn )
							}

		if( params.hasOwnProperty('title') ) {
			dialog_prams.title = params.title;
		}

		if( params.hasOwnProperty('titleIsHtml') ) {
			dialog_prams.titleIsHtml = params.titleIsHtml;
		}

		jQuery( "#sm_inline_dialog" ).dialog(dialog_prams);
}

Smart_Manager.prototype.getTextWidth = function (text, font) {
    // re-use canvas object for better performance
    let canvas = window.smart_manager.getTextWidthCanvas || (window.smart_manager.getTextWidthCanvas = document.createElement("canvas"));
    let context = canvas.getContext("2d");
    context.font = font;
    let metrics = context.measureText(text);
    return metrics.width;
}

Smart_Manager.prototype.renderGridColumns = function (colIndex) {
  switch (colIndex) {
	case 0:
		 let checkbox_col = {
				data: 'active',
				type: 'checkbox',
				className: 'htCenter htMiddle smCheckboxColumnModel',
				key: 'row_select_checkbox'
			};
		return checkbox_col;

	default:
			if( typeof( window.smart_manager.currentVisibleColumns[colIndex] ) != 'undefined' ) {

				let colWidth = 0;

				if( window.smart_manager.currentVisibleColumns[colIndex].hasOwnProperty('width') ) {
					colWidth = window.smart_manager.currentVisibleColumns[colIndex]['width'];
				}

				let header_text = window.smart_manager.column_names[colIndex],
					font = '30px Arial';
					// font = '26px ' + window.smart_manager.body_font_family;

				let newWidth = window.smart_manager.getTextWidth(header_text,font); 

				if( newWidth > colWidth ) {
					window.smart_manager.currentVisibleColumns[colIndex]['width'] = newWidth;
				}
			}
		return window.smart_manager.currentVisibleColumns[colIndex];
	}
}

Smart_Manager.prototype.renderGridColumnHeaders = function (colIndex) {
  switch (colIndex) {
		case 0:
			let checkbox_col = '<input type="checkbox" class="smCheckboxColumnModelHeader" ' + ((window.smart_manager.headerIschecked) ? 'checked="checked"' : '') +'>';
			return checkbox_col;

		default:
			return window.smart_manager.column_names[colIndex];
	}
}

Smart_Manager.prototype.enableDisableButtons = function() {
	//enabling the action buttons
	if( window.smart_manager.selectedIds.length > 0 ) {
		if( jQuery('#sm_top_bar_action_btns_basic #del_sm_editor_grid span').hasClass('sm-ui-state-disabled') ) {
			jQuery('#sm_top_bar_action_btns_basic #del_sm_editor_grid span').removeClass('sm-ui-state-disabled');
		}

		if( jQuery('#sm_top_bar_action_btns_misc .print_invoice_sm_editor_grid').hasClass('sm-ui-state-disabled') ) {
			jQuery('#sm_top_bar_action_btns_misc .print_invoice_sm_editor_grid').removeClass('sm-ui-state-disabled');
		}

	} else {
		if( !jQuery('#sm_top_bar_action_btns_basic #del_sm_editor_grid span').hasClass('sm-ui-state-disabled') ) {
			jQuery('#sm_top_bar_action_btns_basic #del_sm_editor_grid span').addClass('sm-ui-state-disabled');
		}

		if( !jQuery('#sm_top_bar_action_btns_misc .print_invoice_sm_editor_grid').hasClass('sm-ui-state-disabled') ) {
			jQuery('#sm_top_bar_action_btns_misc .print_invoice_sm_editor_grid').addClass('sm-ui-state-disabled');
		}
	}
}


Smart_Manager.prototype.disableSelectedRows = function( readonly ) {

	for (let i = 0; i < window.smart_manager.hot.countRows(); i++) {

		if( window.smart_manager.modifiedRows.indexOf(i) != -1 ) {
			continue;
		}

		for (let j = 0; j < window.smart_manager.hot.countCols(); j++) {
			window.smart_manager.hot.setCellMeta( i, j, 'readOnly', readonly );
		}
	}

}

//Function to highlight the edited cells
Smart_Manager.prototype.highlightEditedCells = function() {

	if( typeof window.smart_manager.dirtyRowColIds == 'undefined' || Object.getOwnPropertyNames(window.smart_manager.dirtyRowColIds).length == 0 ) {
		return;
	}

	for( let row in window.smart_manager.dirtyRowColIds ) {

		window.smart_manager.dirtyRowColIds[row].forEach(function(colIndex) {
			
			cellProp = window.smart_manager.hot.getCellMeta(row, colIndex);
			prevClassName = cellProp.className;

			if( prevClassName == '' || typeof prevClassName == 'undefined' || ( typeof(prevClassName) != 'undefined' && prevClassName.indexOf('sm-grid-dirty-cell') == -1 ) ) {
				window.smart_manager.hot.setCellMeta(row, colIndex, 'className', (prevClassName + ' ' + 'sm-grid-dirty-cell'));
				jQuery('.smCheckboxColumnModel input[data-row='+row+']').parents('tr').removeClass('sm_edited').addClass('sm_edited');
			}
		});
	}
}

Smart_Manager.prototype.isHTML = RegExp.prototype.test.bind(/(<([^>]+)>)/i);

//Function to highlight the selected rows
Smart_Manager.prototype.highlightSelectedRows = function() {

	if( typeof window.smart_manager.highlightedRowIds == 'undefined' || Object.getOwnPropertyNames(window.smart_manager.highlightedRowIds).length == 0 ) {
		return;
	}

	for( let row in window.smart_manager.highlightedRowIds ) {

		for( let i = 0; i < window.smart_manager.hot.countCols(); i++ ) {
			cellProp = window.smart_manager.hot.getCellMeta(row, i);

			prevClassName = cellProp.className;

			if( typeof(prevClassName) != 'undefined' ) {
				if( prevClassName.indexOf('sm_active_highlight') != -1 ) {
					prevClassName = prevClassName.substr(0, prevClassName.indexOf('sm_active_highlight'));
				}

				if( prevClassName.indexOf('sm_highlight') != -1 ) {
					prevClassName = prevClassName.substr(0, prevClassName.indexOf('sm_highlight'));
				}    
			} else {
				prevClassName = '';
			}

			window.smart_manager.hot.setCellMeta(row, i, 'className', (prevClassName + ' ' + window.smart_manager.highlightedRowIds[row]));

			if( window.smart_manager.highlightedRowIds[row] == 'sm_highlight' ) {
				delete window.smart_manager.highlightedRowIds[row];
			}
		}
	}

}

Smart_Manager.prototype.isJSON = function(str) {
    try {
        return (JSON.parse(str) && !!str);
    } catch (e) {
        return false;
    }
}

Smart_Manager.prototype.loadGrid = function() {
	jQuery('#sm_editor_grid').html('');
	window.smart_manager.hot = new Handsontable(window.smart_manager.container, {
																				  data: window.smart_manager.currentDashboardData,
																				  height: window.smart_manager.grid_height,
																				  width: window.smart_manager.grid_width,
																				  allowEmpty: true,
																				  rowHeaders: false,
																				  colHeaders: true,
																				  // autoColumnSize: {useHeaders: true},
																				  autoRowSize: false,
																				  rowHeights: '40px',
																				  // colWidths: 100,
																				  bindRowsWithHeaders: true,
																				  manualColumnResize: true,
																				  manualRowResize: true,
																				  manualColumnMove: false,
																				  columnSorting: { sortEmptyCells: false },
																				  fillHandle: 'vertical',
																				  persistentState: true,
																				  customBorders: true,
																				  columns: function(col) { return window.smart_manager.renderGridColumns(col) },
																				  colHeaders: function(col) { return window.smart_manager.renderGridColumnHeaders(col) },
																				  cells: function (row, col, prop) {
																						let cellProperties = { wordWrap: true };
																						return cellProperties;
																					}
																				});
	
	
	window.smart_manager.hotPlugin.columnSortPlugin = window.smart_manager.hot.getPlugin('columnSorting');
	
	//Code for handling sort state management
	if( window.smart_manager.currentDashboardModel.hasOwnProperty('sort_params') ) {
		if( window.smart_manager.currentDashboardModel.sort_params ) {
			if( window.smart_manager.currentDashboardModel.sort_params.hasOwnProperty('default') ) {
				window.smart_manager.hotPlugin.columnSortPlugin.sort();
			} else {
				if( window.smart_manager.currentVisibleColumns.length > 0 ) {
					for( let index in window.smart_manager.currentVisibleColumns ) {
						if( window.smart_manager.currentVisibleColumns[index].src == window.smart_manager.currentDashboardModel.sort_params.column ) {
							let sort_params = window.smart_manager.currentDashboardModel.sort_params;
							sort_params.column = parseInt(index);
							window.smart_manager.hotPlugin.columnSortPlugin.setSortConfig([sort_params]);
							break;
						}
					}
				}
			}
		}
	}

	window.smart_manager.hotPlugin.autoRowSizePlugin = window.smart_manager.hot.getPlugin('AutoRowSize');		
	//Code to freeze column in grid
	window.smart_manager.hotPlugin.ManualColumnFreeze = window.smart_manager.hot.getPlugin('ManualColumnFreeze');

	if( typeof( window.smart_manager.hotPlugin.ManualColumnFreeze.freezeColumn ) !== "undefined" && typeof( window.smart_manager.hotPlugin.ManualColumnFreeze.freezeColumn ) === "function" ) {
		window.smart_manager.hotPlugin.ManualColumnFreeze.freezeColumn(1);
		window.smart_manager.hotPlugin.ManualColumnFreeze.freezeColumn(2);
		window.smart_manager.hot.render();
	}
	
	window.smart_manager.hot.updateSettings({

		cells: function(row, col, prop) {

			let colObj = window.smart_manager.currentVisibleColumns[col];

			if( typeof( colObj ) != 'undefined' ) {
				if( colObj.hasOwnProperty('type') ) {
					if( colObj.type == 'numeric' ) {
						let cellProperties = {};
						cellProperties.renderer = 'numericRenderer';
						return cellProperties;	
					}
				}
			}
		},

		afterOnCellMouseOver: function(e, coords, td) {
			if( coords.row < 0 && coords.col == 0 ) {
				return;
			}

			let col = this.getCellMeta(coords.row, coords.col),
				current_cell_value = this.getDataAtCell(coords.row, coords.col);

			if( typeof(col.type) != 'undefined' ) {
				if( col.type == 'sm.image' ) {

					let xOffset = 150,
		    			yOffset = 30,
		    			row_title = '';

		    		if( window.smart_manager.dashboard_key == 'product' ) {
						row_title = this.getDataAtRowProp(coords.row, 'posts_post_title');
						row_title = ( window.smart_manager.isHTML(row_title) == true ) ? jQuery(row_title).text() : row_title;
						row_title = row_title;
					}

					if( jQuery('#sm_img_preview').length == 0 ) {
						jQuery("body").append("<div id='sm_img_preview'><div style='margin: 1em; padding: 1em; border-radius: 0.1em; border: 0.1em solid #ece0e0;'><img src='" + current_cell_value + "' width='300' /></div><div id='sm_img_preview_text'>"+ row_title +"</div></div>");
					}

					jQuery("#sm_img_preview")
		            	.css("top", (e.pageY - xOffset) + "px")
		            	.css("left", (e.pageX + yOffset) + "px")
		            	.fadeIn("fast")
		            	.show();		
				}
			}
		},

		afterOnCellMouseOut: function(e, coords, td) {
			if( jQuery('#sm_img_preview').length > 0 ) {
				jQuery('#sm_img_preview').remove();
			}
		},

		afterScrollVertically: function() { //code for infinite scrolling functionality
			if( window.smart_manager.currentDashboardData.length >= window.smart_manager.totalRecords ) {
				return;
			}

			if( typeof( window.smart_manager.hotPlugin.autoRowSizePlugin.getLastVisibleRow ) !== "undefined" && typeof( window.smart_manager.hotPlugin.autoRowSizePlugin.getLastVisibleRow ) === "function" 
				&& window.smart_manager.currentDashboardData.length > 0 && window.smart_manager.gettingData != 1 ) {
				if( (window.smart_manager.currentDashboardData.length - 1) == window.smart_manager.hotPlugin.autoRowSizePlugin.getLastVisibleRow() ) {
					jQuery('#sm_editor_grid_load_items').trigger('click');
				}
			}
		},

		afterRender: function( isForced ) {
			if( isForced === true ) {
				window.smart_manager.showLoader(false);
			}
		},

		beforeColumnSort: function(currentSortConfig, destinationSortConfigs) {
		  	window.smart_manager.hotPlugin.columnSortPlugin.setSortConfig(destinationSortConfigs);
		  	if( typeof(destinationSortConfigs) != 'undefined' ) {
		  		if( destinationSortConfigs.length > 0 ) {
		  			if( destinationSortConfigs[0].hasOwnProperty('column') ) {
			  			if( window.smart_manager.currentVisibleColumns.length > 0 ) {
			  				let colObj = window.smart_manager.currentVisibleColumns[destinationSortConfigs[0].column];

			  				window.smart_manager.currentDashboardModel.sort_params = { 'column': colObj.src,
											'sortOrder': destinationSortConfigs[0].sortOrder };

			  				window.smart_manager.columnSort = true;
			  			}
			  		}	
		  		} else {
		  			if( window.smart_manager.currentDashboardModel.hasOwnProperty('sort_params') ) {
		  				delete window.smart_manager.currentDashboardModel.sort_params;
		  			}
		  			window.smart_manager.columnSort = false;
		  		}

		  		window.smart_manager.page = 1;
		  		window.smart_manager.getData();
		  	}
		  	return false; // The blockade for the default sort action.
		},

		afterCreateRow: function (row, amount) {

			while( amount > 0 ) {
				// setTimeout( function() { //added for handling dirty class for edited cells

					let idKey = ( window.smart_manager.dashboard_key == 'user' ) ? 'users_id' : 'posts_id';
					let row_data_id = window.smart_manager.hot.getDataAtRowProp(row, idKey);

					if( typeof(row_data_id) != 'undefined' && row_data_id ) {
						return;
					}

					window.smart_manager.addRecords_count++;
					window.smart_manager.hot.setDataAtRowProp(row,idKey,'sm_temp_'+window.smart_manager.addRecords_count);

					let val = '',
						colObj = {};

					for( let key in window.smart_manager.currentColModel ) {

						colObj = window.smart_manager.currentColModel[key];

						if( colObj.hasOwnProperty('data') ) {
							if( jQuery.inArray(colObj.data, window.smart_manager.defaultColumnsAddRow) >= 0 ) {

								if( typeof colObj.defaultValue != 'undefined' ) {
									val = colObj.defaultValue;
								} else {
									if( typeof colObj.selectOptions != 'undefined' ) {
										val = Object.keys(colObj.selectOptions)[0]
									} else {
										val = 'test';
									}
								}

								window.smart_manager.hot.setDataAtRowProp(row, colObj.data, val);
							}
						}
					}
				// }, 1 );
				row++;
				amount--;
			}
		},

		afterChange: function(changes, source) {

			if( window.smart_manager.selectAll === true || changes === null ) {
				return;
			}

			let col = {},
				cellProp = {},
				colIndex = '',
				idKey = ( window.smart_manager.dashboard_key == 'user' ) ? 'users_id' : 'posts_id',
				colTypesDisabledHiglight = new Array('sm.image');


			changes.forEach(([row, prop, oldValue, newValue]) => {

				if( ( row < 0 && prop == 0 ) || oldValue == newValue ) {
					return;
				}

				if( window.smart_manager.modifiedRows.indexOf(row) == -1 ) {
					window.smart_manager.modifiedRows.push(row);
				}
				
				colIndex = window.smart_manager.hot.propToCol(prop);
				if( typeof(colIndex) == 'number' ) {
					col = window.smart_manager.hot.getCellMeta(row, colIndex);
				}

				let id = window.smart_manager.hot.getDataAtRowProp(row, idKey);

				if( 'row_select_checkbox' === col.key ) {

					if( newValue === true ) {
						newClassName = 'sm_active_highlight';
						if( window.smart_manager.selectedIds.indexOf(id) == -1 ) {
							window.smart_manager.selectedIds.push(id);
						}
					} else {
						newClassName = 'sm_highlight';
						if( window.smart_manager.selectedIds.indexOf(id) != -1 ) {
							window.smart_manager.selectedIds.splice(window.smart_manager.selectedIds.indexOf(id), 1);
						}
					}

					window.smart_manager.highlightedRowIds[row] = newClassName;

					if( typeof( window.smart_manager.enableDisableButtons ) !== "undefined" && typeof( window.smart_manager.enableDisableButtons ) === "function" ) {
						window.smart_manager.enableDisableButtons();
					}

				} else if( oldValue != newValue && prop != idKey && colTypesDisabledHiglight.indexOf(col.type) == -1 ) { //for inline edit
					cellProp = window.smart_manager.hot.getCellMeta(row, prop);
					prevClassName = ( typeof(cellProp.className) != 'undefined' ) ? cellProp.className : '';

					//dirty cells variable
					if( window.smart_manager.dirtyRowColIds.hasOwnProperty(row) === false ) {
						window.smart_manager.dirtyRowColIds[row] = new Array();
					}

					if( window.smart_manager.dirtyRowColIds[row].indexOf(colIndex) == -1 ) {
						window.smart_manager.dirtyRowColIds[row].push(colIndex);
					}

					if( jQuery('#sm_top_bar_action_btns_basic #save_sm_editor_grid_btn img').hasClass('sm-ui-state-disabled') ) {
						jQuery('#sm_top_bar_action_btns_basic #save_sm_editor_grid_btn img').removeClass('sm-ui-state-disabled');
					}

					if( prevClassName == '' || ( typeof(prevClassName) != 'undefined' && prevClassName.indexOf('sm-grid-dirty-cell') == -1 ) ) {

						//creating the edited json string

						if( window.smart_manager.editedData.hasOwnProperty(id) === false ) {
							window.smart_manager.editedData[id] = {};
						}

						if( Object.entries(col).length === 0 ) {
							if( typeof( window.smart_manager.currentColModel ) != 'undefined' ) {
								window.smart_manager.currentColModel.forEach(function(value) {
									if( value.hasOwnProperty('data') && value.data == prop ) {
										col.src = value.src;
									}	
								});
							}
						}

						window.smart_manager.editedData[id][col.src] = newValue;
						window.smart_manager.editedCellIds.push({'row': row, 'col':colIndex});
					}

					if( window.smart_manager.sm_beta_pro == 0 ) {
						if( typeof( window.smart_manager.modifiedRows ) != 'undefined' ) {
							if( window.smart_manager.modifiedRows.length >= window.smart_manager.sm_updated_sucessfull ) {
								//call to function for highlighting selected row ids
								if( typeof( window.smart_manager.disableSelectedRows ) !== "undefined" && typeof( window.smart_manager.disableSelectedRows ) === "function" ) {
									window.smart_manager.disableSelectedRows(true);
								}
							}
						}
					}
				}
			});

			//call to function for highlighting selected row ids
			if( typeof( window.smart_manager.highlightSelectedRows ) !== "undefined" && typeof( window.smart_manager.highlightSelectedRows ) === "function" ) {
				window.smart_manager.highlightSelectedRows();
			}

			//call to function for highlighting edited cell ids
			if( typeof( window.smart_manager.highlightEditedCells ) !== "undefined" && typeof( window.smart_manager.highlightEditedCells ) === "function" ) {
				window.smart_manager.highlightEditedCells();
			}

			window.smart_manager.hot.render();
		},

		afterOnCellMouseUp: function (e, coords, td) {
			let col = this.getCellMeta(coords.row, coords.col),
				id_key = ( window.smart_manager.dashboard_key == 'user' ) ? 'users_id' : 'posts_id',
				row_data_id = this.getDataAtRowProp(coords.row, id_key);
				current_cell_value = this.getDataAtCell(coords.row, coords.col);

			if( typeof(col.readOnly) != 'undefined' && col.readOnly == 'true' ) {
				return;
			}

			let params = {'coords': coords, 'td': td, 'colObj': col, 'row_data_id': row_data_id, 'current_cell_value': current_cell_value};

			window.smart_manager.defaultEditor = true;

			jQuery('#sm_editor_grid').trigger('sm_grid_on_afterOnCellMouseUp',[params]);

			if( coords.row < 0 && coords.col == 0 ) { // for header checkbox of column selection model

				let checked = false,
					newClassName = 'sm_highlight'; 

				window.smart_manager.selectAll = false;

				if( jQuery('.smCheckboxColumnModelHeader:checked').length == 0 ) {
					checked = true;
					newClassName = 'sm_active_highlight';
					window.smart_manager.selectAll = true;
				}

				window.smart_manager.headerIschecked = checked;
			   
				let rows = window.smart_manager.hot.countRows(),
					newState = !e.target.checked,
					sourceData = window.smart_manager.hot.getSourceData();

				for(let i = 0; i < rows; i++){

					// window.smart_manager.hot.setDataAtCell(i, 0, checked);
					sourceData[i]['active'] = newState;

					// setTimeout( function() { window.smart_manager.hot.setDataAtCell(i, 0, checked); }, 1 );
					
					for (let j = 0; j < window.smart_manager.hot.countCols(); j++) {
						cellProp = window.smart_manager.hot.getCellMeta(i, j);
						prevClassName = cellProp.className;

						if( typeof(prevClassName) != 'undefined' ) {
							if( prevClassName.indexOf('sm_active_highlight') != -1 ) {
								prevClassName = prevClassName.substr(0, prevClassName.indexOf('sm_active_highlight'));
							}
							if( prevClassName.indexOf('sm_highlight') != -1 ) {
								prevClassName = prevClassName.substr(0, prevClassName.indexOf('sm_highlight'));
							}    
						} else {
							prevClassName = '';
						}
						window.smart_manager.hot.setCellMeta(i, j, 'className', (prevClassName + ' ' + newClassName));
					}
				}

				if( window.smart_manager.selectAll ) {
					window.smart_manager.selectedIds = new Array();

					if( window.smart_manager.currentDashboardData.length > 0 ) {
						window.smart_manager.currentDashboardData.forEach((obj) => {

							id = ( obj.hasOwnProperty(id_key) ) ? obj[id_key] : '';
							if( window.smart_manager.selectedIds.indexOf(id) == -1 && id != '' ) {
								window.smart_manager.selectedIds.push(id);
							}
						});
					}

				} else {
					window.smart_manager.selectedIds = new Array();
				}

				if( typeof( window.smart_manager.enableDisableButtons ) !== "undefined" && typeof( window.smart_manager.enableDisableButtons ) === "function" ) {
					window.smart_manager.enableDisableButtons();
				}

				// window.smart_manager.selectAll = false;
				window.smart_manager.hot.render();
				
			}

			if( window.smart_manager.hasOwnProperty('defaultEditor') && window.smart_manager.defaultEditor === false ) {
				return;
			}

			if( typeof (col.type) != 'undefined' && col.type == 'sm.image' && coords.row >= 0 ) { // code to handle the functionality to handle editing of 'image' data types
				var file_frame;
								
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
				  file_frame.open();
				  return;
				}
				
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
				  title: jQuery( this ).data( 'uploader_title' ),
				  button: {
					text: jQuery( this ).data( 'uploader_button_text' )
				  },
				  library: {
				    type: 'image'
				  },
				  multiple: false  // Set to true to allow multiple files to be selected
				});
				
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
				  // We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();

					if ( 'postmeta_meta_key__thumbnail_id_meta_value__thumbnail_id' === col.prop ) {

						let params = {};
							params.data = {
											cmd: 'inline_update_product_featured_image',
											active_module: window.smart_manager.dashboard_key,
											pro: ( ( typeof(window.smart_manager.sm_beta_pro) != 'undefined' ) ? window.smart_manager.sm_beta_pro : 0 ),
											product_id: row_data_id,
											update_field: col.prop,
											selected_attachment_id: attachment['id'],
											security: window.smart_manager.sm_nonce
										};

						window.smart_manager.send_request(params, function(response) {
							if ( 'failed' !== response ) {
								window.smart_manager.hot.setDataAtCell(coords.row, coords.col, attachment['url'], 'image_inline_update');

								if( typeof(window.smart_manager.sm_beta_pro) == 'undefined' || ( typeof(window.smart_manager.sm_beta_pro) != 'undefined' && window.smart_manager.sm_beta_pro != 1 ) ) {
									response = JSON.parse( response );
									msg = response.msg;

									if( typeof( response.sm_inline_update_count ) != 'undefined' ) {
										if ( typeof (window.smart_manager.updateLitePromoMessage) !== "undefined" && typeof (window.smart_manager.updateLitePromoMessage) === "function" ) {
											window.smart_manager.updateLitePromoMessage( response.sm_inline_update_count );
										}
									}
								} else {
									msg = response;
								}
							}
						});
					}

				});
				
				file_frame.open();
			}

			if( typeof (col.type) != 'undefined' && col.type == 'sm.longstring' ) {

				if( typeof(wp.editor.getDefaultSettings) == 'undefined' ) {
					return;
				}

				//Code for unformatting the 'longstring' type values
				let unformatted_val = current_cell_value;
				let wp_editor_html = '<textarea style="width:100%;height:100%;z-index:100;" id="sm_beta_lonstring_input">'+ unformatted_val +'</textarea>';
				let params = {
							content: wp_editor_html,
							target: window,
							dlg_height: 400,
							dlg_width: 550,
							position_my: 'center center',
							position_at: 'center center',
							modal:true
						};

				window.smart_manager.inline_edit_dlg(params);

				wp.editor.initialize('sm_beta_lonstring_input', {tinymce:  { height: 200,
																			  wpautop:true, 
																			  plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview', 
																			  toolbar1: 'formatselect bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,wp_more,|,spellchecker,fullscreen,wp_adv',
																			  toolbar2: 'underline,justifyfull,forecolor,|,pastetext,pasteword,removeformat,|,media,charmap,|,outdent,indent,|,undo,redo,wp_help'},
																quicktags:  { buttons: 'strong,em,link,block,del,img,ul,ol,li,code,more,spell,close,fullscreen' },
																mediaButtons: true });

				jQuery(document).off('click', '[aria-describedby="sm_inline_dialog"] .sm_inline_dialog_ok');
				jQuery(document).on('click', '[aria-describedby="sm_inline_dialog"] .sm_inline_dialog_ok', function() {
					let content = wp.editor.getContent('sm_beta_lonstring_input');
					window.smart_manager.hot.setDataAtCell(coords.row, coords.col, content, 'sm.longstring_inline_update');
					wp.editor.remove('sm_beta_lonstring_input');
				});

				jQuery(document).off('dialogclose', '[aria-describedby="sm_inline_dialog"]').on('dialogclose', '[aria-describedby="sm_inline_dialog"]', function() {
					wp.editor.remove('sm_beta_lonstring_input');
				});
			}

			if( typeof (col.type) != 'undefined' && col.type == 'sm.serialized' ) { //Code for handling serialized complex data handling

				let wp_editor_html = '<div id="sm_beta_json_editor" style="width: 520px; height: 300px;"></div>';

				let params = {
							content: wp_editor_html,
							target: window,
							dlg_height: 400,
							dlg_width: 550,
							position_my: 'center center',
							position_at: 'center center',
							modal:true
						};

				window.smart_manager.inline_edit_dlg(params);

				let container = document.getElementById("sm_beta_json_editor");
				let options = {
								"mode": 'tree',
								"search": true
							};
				let editor = new JSONEditor(container, options);
				let val = ( window.smart_manager.isJSON(current_cell_value) ) ? JSON.parse(current_cell_value) : current_cell_value;

				if ( col.editor_schema && window.smart_manager.isJSON( col.editor_schema ) ) {
					editor.setSchema( JSON.parse( col.editor_schema ) );
				}

				editor.set(val);
				editor.expandAll();

				jQuery(document).off('click', '[aria-describedby="sm_inline_dialog"] .sm_inline_dialog_ok');
				jQuery(document).on('click', '[aria-describedby="sm_inline_dialog"] .sm_inline_dialog_ok', function() {
					let content = JSON.stringify(editor.get());
					window.smart_manager.hot.setDataAtCell(coords.row, coords.col, content, 'sm.serialized_inline_update');
					wp.editor.remove('sm_beta_json_editor');
				});

			}

			if( typeof (col.type) != 'undefined' && col.type == 'sm.multilist' ) { // code to handle the functionality to handle editing of 'multilist' data types

					var actual_value = col.values,
						multiselect_data = new Array(),
						multiselect_chkbox_list = '',
						current_value = new Array();

					if( current_cell_value != '' && typeof(current_cell_value) != 'undefined' && current_cell_value !== null ) {
						current_value = current_cell_value.split(', <br>');
						var rex = /(<([^>]+)>)/ig;

						for(var i in current_value) {
							current_value[i] = current_value[i].replace(rex , "");
						}
					}

					for (var index in actual_value) {

						if (actual_value[index]['parent'] == "0") {

							if (multiselect_data[index] !== undefined) {
								if ( multiselect_data[index].hasOwnProperty('child') !== false ) {
									multiselect_data[index].term = actual_value[index].term;    
								}
								
							} else {
								multiselect_data[index] = {'term' : actual_value[index].term};    
							}

							
						} else {

							if( multiselect_data[actual_value[index]['parent']] === undefined ) {

								//For hirecheal categories
								for (var mindex in multiselect_data) {
									if (multiselect_data[mindex].hasOwnProperty('child') === false) {
										continue;
									}

									for (var cindex in multiselect_data[mindex].child) {

									}

								}

								multiselect_data[actual_value[index]['parent']] = {};
							}

							if (multiselect_data[actual_value[index]['parent']].hasOwnProperty('child') === false) {
								multiselect_data[actual_value[index]['parent']].child = {};
							}
							multiselect_data[actual_value[index]['parent']].term = actual_value[actual_value[index]['parent']].term;
							multiselect_data[actual_value[index]['parent']].child[index] = actual_value[index].term;
						}

					}

					multiselect_chkbox_list += '<ul>';

					for (var index in multiselect_data) {

						var checked = '';

						if (current_value != '' && current_value.indexOf(multiselect_data[index].term) != -1) {
							checked = 'checked';                        
						} 

						multiselect_chkbox_list += '<li> <input type="checkbox" name="chk_multiselect" value="'+ index +'" '+ checked +'>  '+ multiselect_data[index].term +'</li>';
						
						if (multiselect_data[index].hasOwnProperty('child') === false) continue;

						var child_val = multiselect_data[index].child;
						multiselect_chkbox_list += '<ul class="children">';

						for (var child_id in child_val) {

							var child_checked = '';

							if (current_value != '' && current_value.indexOf(child_val[child_id]) != -1) {
								child_checked = 'checked';                        
							} 

							multiselect_chkbox_list += '<li> <input type="checkbox" name="chk_multiselect" value="'+ child_id +'" '+ child_checked +'>  '+ child_val[child_id] +'</li>';
						}
						multiselect_chkbox_list += '</ul>';
					}               

					multiselect_chkbox_list += '</ul>';

				
				params = {
							content: multiselect_chkbox_list,
							target: e,
							modal: true
						};

				window.smart_manager.inline_edit_dlg(params);

				//Code for click event of 'ok' btn
				jQuery(document).off('click','[aria-describedby="sm_inline_dialog"] .sm_inline_dialog_ok').on('click', '[aria-describedby="sm_inline_dialog"] .sm_inline_dialog_ok', function() {

					var mutiselect_edited_text = '';

					var selected_val = jQuery("[aria-describedby='sm_inline_dialog'] input[name='chk_multiselect']:checked" ).map(function () {
											return jQuery(this).val();
										}).get();

					if( selected_val.length > 0 ) {

						for (var index in selected_val) {
							if( actual_value.hasOwnProperty(selected_val[index]) ) {
								if (mutiselect_edited_text != '') {
									mutiselect_edited_text += ', <br>';
								}
								mutiselect_edited_text += actual_value[selected_val[index]]['term'];
							}
						}

						if( mutiselect_edited_text != '' ) {
							window.smart_manager.hot.setDataAtCell(coords.row, coords.col, mutiselect_edited_text, 'sm.multilist_inline_update');
						}

					}
				});
			}
		}
	});

	jQuery('#sm_editor_grid').trigger( 'smart_manager_post_load_grid' ); //custom trigger
}

Smart_Manager.prototype.refresh = function( dataParams ) {
	window.smart_manager.editedData = {};
	window.smart_manager.selectedIds = [];
	window.smart_manager.addRecords_count = 0;
	window.smart_manager.page = 1;
	window.smart_manager.selectAll = false;
	window.smart_manager.headerIschecked = '';
	window.smart_manager.highlightedRowIds = {};
	window.smart_manager.dirtyRowColIds = {};

	if( window.smart_manager.sm_beta_pro == 0 ) {
		if( typeof( window.smart_manager.disableSelectedRows ) !== "undefined" && typeof( window.smart_manager.disableSelectedRows ) === "function" ) {
			window.smart_manager.disableSelectedRows(false);
		}
	}


	window.smart_manager.getData(dataParams);
}

Smart_Manager.prototype.event_handler = function() {

	// Code to handle width of the grid based on the WP collapsable menu
	jQuery(document).on('click', '#collapse-menu', function() {
		let current_url = document.URL;

		if ( current_url.indexOf("page=smart-manager") == -1 ) {
			return;
		}

		if ( !jQuery(document.body).hasClass('folded') ) {
			window.smart_manager.grid_width = document.documentElement.offsetWidth - 200;
		} else {
			window.smart_manager.grid_width = document.documentElement.offsetWidth - 80;
		}
		
		window.smart_manager.hot.updateSettings({'width':window.smart_manager.grid_width});
		window.smart_manager.hot.render();

		jQuery('#sm_top_bar, #sm_bottom_bar').css('width',window.smart_manager.grid_width+'px');
	});

	//Code to handle dashboard change in grid
	jQuery(document).off('change', '#sm_dashboard_select').on('change', '#sm_dashboard_select',function(){

		var sm_dashboard_valid = 0,
			sm_selected_dashboard_key = jQuery(this).val(),
			sm_selected_dashboard_title = jQuery( "#sm_dashboard_select option:selected" ).text();

		if( window.smart_manager.sm_beta_pro == 0 ) {
			sm_dashboard_valid = 0;
			if( window.smart_manager.sm_lite_dashboards.indexOf(sm_selected_dashboard_key) >= 0 ) {
				sm_dashboard_valid = 1;    
			}
		} else {
			sm_dashboard_valid = 1;
		}

		if( sm_dashboard_valid == 1 ) {

			window.smart_manager.state_apply = true;
			// window.smart_manager.refreshDashboardStates(); //function to save the state
			
			if ( typeof (window.smart_manager.updateState) !== "undefined" && typeof (window.smart_manager.updateState) === "function" ) {
				window.smart_manager.updateState(); //refreshing the dashboard states
			}
			
			window.smart_manager.dashboard_key = sm_selected_dashboard_key;
			window.smart_manager.dashboard_display_title = sm_selected_dashboard_title;
			window.smart_manager.currentDashboardModel = '';
			window.smart_manager.advancedSearchQuery = new Array();
			window.smart_manager.simpleSearchText = '';
			window.smart_manager.current_selected_dashboard = sm_selected_dashboard_key;

			content = ( window.smart_manager.searchType == 'simple' ) ? window.smart_manager.simpleSearchContent : window.smart_manager.advancedSearchContent;
			jQuery('#sm_top_bar_search #search_content').html(content);

			if ( typeof (window.smart_manager.initialize_advanced_search) !== "undefined" && typeof (window.smart_manager.initialize_advanced_search) === "function" && window.smart_manager.searchType != 'simple' ) {
				window.smart_manager.initialize_advanced_search();
			}

			jQuery('#sm_editor_grid').trigger( 'sm_dashboard_change' ); //custom trigger

			window.smart_manager.load_dashboard(); 
		} else {
			jQuery("#sm_dashboard_select").val(window.smart_manager.current_selected_dashboard);

			var content = 'For managing '+ sm_selected_dashboard_title +', '+ window.smart_manager.sm_success_msg + ' <a href="' + window.smart_manager.pricingPageURL + '" target="_blank">Pro</a> version.';
			window.smart_manager.showNotification( 'Note', content );
		}
		
	})
	
	.off( 'change', '#search_switch').on( 'change', '#search_switch' ,function(){ //request for handling switch search types

		let switchSearchType = jQuery(this).attr('switchSearchType'),
			title = jQuery("label[for='"+ jQuery(this).attr("id") +"']").attr('title'),
			content = '';

		window.smart_manager.advancedSearchQuery = new Array();
		window.smart_manager.simpleSearchText = '';

		jQuery(this).attr('switchSearchType', window.smart_manager.searchType);
		jQuery("label[for='"+ jQuery(this).attr("id") +"']").attr('title', title.replace(switchSearchType, window.smart_manager.searchType));

		window.smart_manager.searchType = switchSearchType;
		content = ( window.smart_manager.searchType == 'simple' ) ? window.smart_manager.simpleSearchContent : window.smart_manager.advancedSearchContent;
		jQuery('#sm_top_bar_search #search_content').html(content);

		if ( typeof (window.smart_manager.initialize_advanced_search) !== "undefined" && typeof (window.smart_manager.initialize_advanced_search) === "function" && window.smart_manager.searchType != 'simple' ) {
			window.smart_manager.initialize_advanced_search();
		}

	})

	.off( 'keyup', '#sm_simple_search_box').on( 'keyup', '#sm_simple_search_box' ,function(){ //request for handling simple search
		clearTimeout(window.smart_manager.searchTimeoutId);
		window.smart_manager.searchTimeoutId = setTimeout(function () {
			window.smart_manager.simpleSearchText = jQuery('#sm_simple_search_box').val();
			window.smart_manager.refresh();
		}, 1000);
	})

	.off( 'click', '#sm_advanced_search_submit').on( 'click', '#sm_advanced_search_submit' ,function(){ //request for handling advanced search

		jQuery('input[id^="sm_advanced_search_box_"]').each(function() {

			var val = jQuery(this).val();

			if( val.length == 0 ) {
				var id = jQuery(this).attr('id'),
					index = id.lastIndexOf("_"),
					key = id.substr(index+1);    

				delete(window.smart_manager.advancedSearchQuery[key]);
			}
			
		});

		window.smart_manager.load_dashboard();
		
	})

	//Code to handle the inline save functionality
	.off( 'click', '#sm_top_bar_action_btns_basic #save_sm_editor_grid_btn').on( 'click', '#sm_top_bar_action_btns_basic #save_sm_editor_grid_btn' ,function(){

		if( Object.getOwnPropertyNames(window.smart_manager.editedData).length > 0 ) {

			if ( typeof (window.smart_manager.saveData) !== "undefined" && typeof (window.smart_manager.saveData) === "function" ) {
				window.smart_manager.saveData();    
			};
		} else {
			window.smart_manager.showNotification('', 'Please edit a record');
		}

		return false;    

	})

	//Code to handle the delete records functionality
	.off( 'click', '#sm_top_bar_action_btns_basic #del_sm_editor_grid').on( 'click', '#sm_top_bar_action_btns_basic #del_sm_editor_grid' ,function(){
			
		if( window.smart_manager.selectedIds.length <= 0 ) {
			window.smart_manager.showNotification('', 'Please select a record');
			return false;
		}

		if ( window.smart_manager.sm_beta_pro == 0 && window.smart_manager.selectedIds.length > window.smart_manager.sm_deleted_sucessfull ) {

			window.smart_manager.showNotification('', 'To delete more than '+window.smart_manager.sm_deleted_sucessfull+' records at a time, <a href="' + window.smart_manager.pricingPageURL + '" target="_blank">upgrade to Pro</a>');

		} else {			

			let params = {};

			params.title       = '<span style="color: red;"><span class="dashicons dashicons-warning" style="vertical-align: text-bottom;"></span>&nbsp;Attention!!!</span>';
			params.titleIsHtml = true;
			params.btnParams   = {};

			let selected_text = '<span style="font-size: 1.2em;">Are you sure you want to <strong>delete the selected</strong> ' + ( ( window.smart_manager.selectedIds.length > 1 ) ? 'records' : 'record' ) + '?</span>';
			let all_text      = '<span style="font-size: 1.2em;">Are you sure you want to <strong>delete all</strong> the ' + window.smart_manager.dashboard_display_title + '?</span>';

			if ( window.smart_manager.sm_beta_pro == 1 ) {
				if ( true === window.smart_manager.selectAll ) {
					params.content = all_text;
				} else {
					params.content = selected_text;
				}

				if ( typeof (window.smart_manager.deleteAllRecords) !== "undefined" && typeof (window.smart_manager.deleteAllRecords) === "function" ) {
					params.btnParams.yesCallback = window.smart_manager.deleteAllRecords;
				}
			} else {
				if ( typeof (window.smart_manager.deleteRecords) !== "undefined" && typeof (window.smart_manager.deleteRecords) === "function" ) {
					params.content = selected_text;
					if ( true === window.smart_manager.selectAll ) {
						params.content += '<br><br><br><span style="font-size: 1.2em;"><small><i>Note: Looking to <strong>delete all</strong> the records? <a href="' + window.smart_manager.pricingPageURL + '" target="_blank">Upgrade to Pro</a></i></small></span>';
						params.height = 225;
					}
					params.btnParams.yesCallback = window.smart_manager.deleteRecords;
				}
			}

			window.smart_manager.showConfirmDialog(params);

		}

		return false;    

	})

	//Code for handling refresh event
	.off( 'click', "#refresh_sm_editor_grid").on( 'click', "#refresh_sm_editor_grid", function(){
		window.smart_manager.refresh();
	})

	.off( 'click', "#sm_editor_grid_distraction_free_mode").on( 'click', "#sm_editor_grid_distraction_free_mode", function(){

		if ( window.smart_manager.sm_beta_pro == 1 ) {
			if ( typeof (window.smart_manager.smToggleFullScreen) !== "undefined" && typeof (window.smart_manager.smToggleFullScreen) === "function" ) {
				let element = document.documentElement;
				window.smart_manager.smToggleFullScreen( element );    
			}
		} else {
			window.smart_manager.showNotification();
		}
		
/*Review*/
		window.smart_manager.hot.updateSettings({'width':window.smart_manager.grid_width});
		window.smart_manager.hot.render();

		jQuery('#sm_top_bar, #sm_bottom_bar').css('width',window.smart_manager.grid_width+'px');
	})

	//Code for load more items
	.off( 'click', "#sm_editor_grid_load_items").on( 'click', "#sm_editor_grid_load_items", function(){

		if( window.smart_manager.currentDashboardData.length >= window.smart_manager.totalRecords ) {
			return;
		}

		window.smart_manager.page++;
		window.smart_manager.getData();
	})

	.off( 'click', 'td.htDimmed' ).on( 'click', 'td.htDimmed' , function() {
		if( window.smart_manager.sm_beta_pro == 0 ) {
			if( typeof( window.smart_manager.modifiedRows ) != 'undefined' ) {
				if( window.smart_manager.modifiedRows.length >= window.smart_manager.sm_updated_sucessfull ) {
					alert('For editing more records upgrade to Pro');
				}
			}
		}
	})

	//Code for add record functionality
	.off( 'click', "#add_sm_editor_grid").on( 'click', "#add_sm_editor_grid", function(){

		let params = {
						title : 'Add '+window.smart_manager.dashboard_display_title+'(s)',
						content : '<div style="font-size:1.2em;margin:1em;"> <div style="margin-bottom:1em;">Enter how many new '+ window.smart_manager.dashboard_display_title +'(s) to create! </div> <input type="number" id="sm_beta_add_record_count" min="1" value="1" style="width:5em;"></div>',
						height : 250,
						btnParams : { yesText : 'Create',
										noText : 'Cancel' }
					};
		
		if ( typeof (window.smart_manager.deleteRecords) !== "undefined" && typeof (window.smart_manager.deleteRecords) === "function" ) {
			params.btnParams.yesCallback = function() {
				let count = jQuery('#sm_beta_add_record_count').val();
				if( count > 0 ) {
					window.smart_manager.hot.alter('insert_row', 0, count);
				}
			};
		}

		window.smart_manager.showConfirmDialog(params);
	})

	// Code for handling the batch update & duplicate records functionality
	.off( 'click', "#batch_update_sm_editor_grid, .sm_beta_dropdown_content a, #export_csv_sm_editor_grid, #print_invoice_sm_editor_grid_btn").on( 'click', "#batch_update_sm_editor_grid, .sm_beta_dropdown_content a, #export_csv_sm_editor_grid, #print_invoice_sm_editor_grid_btn", function(){

		let id = jQuery(this).attr('id'),
			btnText = jQuery(this).text();


		if( window.smart_manager.sm_beta_pro == 1 ) {

			if( typeof( id ) != 'undefined' ) {
				if( id == 'export_csv_sm_editor_grid' ) { //code for handling export CSV functionality
					if ( typeof (window.smart_manager.generateCsvExport) !== "undefined" && typeof (window.smart_manager.generateCsvExport) === "function" ) {
						window.smart_manager.generateCsvExport();    
					}
				} else {
					if( window.smart_manager.selectedIds.length > 0 ) {

						if( id == 'batch_update_sm_editor_grid' ) { //code for handling batch update functionality
							window.smart_manager.createBatchUpdateDialog();
						} else if( id == 'sm_beta_dup_entire_store' || id == 'sm_beta_dup_selected' ) { //code for handling duplicate records functionality

							let params = {};

							params.btnParams = {}
							params.title = 'Attention!!!';
							params.content = (window.smart_manager.dashboard_key != 'product') ? '<p>This will duplicate only the records in posts, postmeta and related taxonomies.</p>' : '';
							params.content += 'Are you sure you want to duplicate the ' + btnText + '?';

							if ( typeof (window.smart_manager.duplicateRecords) !== "undefined" && typeof (window.smart_manager.duplicateRecords) === "function" ) {
								params.btnParams.yesCallback = window.smart_manager.duplicateRecords;
							}
							
							window.smart_manager.duplicateStore = ( id == 'sm_beta_dup_entire_store' ) ? true : false;

							window.smart_manager.showConfirmDialog(params);
						} else if( id == 'print_invoice_sm_editor_grid_btn' ) { //code for handling Print Invoice functionality
							if ( typeof (window.smart_manager.printInvoice) !== "undefined" && typeof (window.smart_manager.printInvoice) === "function" ) {
								window.smart_manager.printInvoice();
							}
						}

					} else {
						window.smart_manager.showNotification('', 'Please select a record');
					}
				}
			}
			
		} else {

			if( typeof(id) != 'undefined' ) {


				if( id != 'sm_beta_dup_entire_store' && id != 'sm_beta_dup_selected' ) {
					
					let description = 'You can change / update multiple fields of the entire store OR for selected items by selecting multiple records and then click on Batch Update.';

					if( id == 'export_csv_sm_editor_grid' ) {
						description = 'You can export all the records OR filtered records (using Simple Search or Advanced Search) by simply clicking on the Export CSV button at the bottom right of the grid.';
					}

					content = '<iframe width="560" height="315" src="https://www.youtube.com/embed/'+ ( ( id == 'batch_update_sm_editor_grid' ) ? 'COXCuX2rFrk' : 'GMgysSQw7_g' ) +'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'+
								'<p style="font-size:1.2em;margin:1em;">'+description+'</p>';

					title = ( ( id == 'batch_update_sm_editor_grid' ) ? btnText + ' - <span style="color: red;">Biggest Time Saver</span>' : btnText ) + ' (Only in <a href="'+ window.smart_manager.pricingPageURL +'" target="_blank">Pro</a>) ';

					let params = {
									title: title,
									content: content,
									target: window,
									dlg_height: 570,
									dlg_width: 600,
									position_my: 'center center',
									position_at: 'center center',
									modal:true,
									titleIsHtml: true,
									buttons_model: [{
										text: 'Get Pro at 50% OFF',
										class: 'sm_button green',
										click: function() {
											window.open(window.smart_manager.pricingPageURL, "_blank");
											jQuery( this ).dialog( "close" );
										}
									}],
								};

					window.smart_manager.inline_edit_dlg(params);
				} else {
					window.smart_manager.showNotification('Duplicate Records (Only in <a href="'+ window.smart_manager.pricingPageURL +'" target="_blank">Pro</a>)');
				}
			} else {
				window.smart_manager.showNotification();
			}


			

			

		}
	})

	//Code for handling adding advanced search conditions
	.off('click', '#sm_advanced_search_or').on('click', '#sm_advanced_search_or', function () {
		if( window.smart_manager.sm_beta_pro == 1 ) { 
			jQuery("#sm_advanced_search_or").removeAttr('disabled');
			if ( typeof window.smart_manager.addAdvancedSearchCondition !== "undefined" && typeof window.smart_manager.addAdvancedSearchCondition === "function" ) {
				window.smart_manager.addAdvancedSearchCondition();    
			}
			
		} else {
			jQuery("#sm_advanced_search_or").attr('disabled','disabled');
			window.smart_manager.showNotification();
		}
	})

	//Code for handling adding advanced search conditions
	.off('click', '#show_hide_cols_sm_editor_grid').on('click', '#show_hide_cols_sm_editor_grid', function () {
		if ( typeof (window.smart_manager.createColumnVisibilityDialog) !== "undefined" && typeof (window.smart_manager.createColumnVisibilityDialog) === "function" ) {
			window.smart_manager.createColumnVisibilityDialog();
		}
	})

	//Code for handling the dropdown menu for the duplicate button
	.off('hover', '.sm_beta_dropdown').on('hover','.sm_beta_dropdown', function(){

		if( jQuery(this).hasClass('sm-ui-state-disabled') ) {
			if( jQuery(this).find('.sm_beta_dropdown_content').is(":visible") ) {
				jQuery(this).find('.sm_beta_dropdown_content').hide();
			}
		}else {
			if( jQuery(this).find('.sm_beta_dropdown_content').is(":visible") ) {
				jQuery(this).find('.sm_beta_dropdown_content').hide();
			} else {
				jQuery(this).find('.sm_beta_dropdown_content').show();
			}
		}
	});

	jQuery(document).trigger('sm_event_handler');

}

//Function to equalize the enabled and disabled section height in column visibility dialog
Smart_Manager.prototype.columnVisibilityEqualizeHeight = function() {
	let enabledHeight = jQuery('#sm-columns-enabled').height(),
		disabledHeight = jQuery('#sm-columns-disabled').height(),
		maxHeight = enabledHeight > disabledHeight ? enabledHeight : disabledHeight;

	if( maxHeight > 0 ) {
		jQuery('#sm-columns-enabled, #sm-columns-disabled').height(maxHeight);
	}
}

//Function to process Column Visibility Enabled & Disabled Columns Search
Smart_Manager.prototype.processColumnVisibilitySearch = function(eventObj) {
	
	let searchString = jQuery(eventObj).val(),
		ulId = jQuery(eventObj).attr('data-ul-id');
	
	if( ulId != '' ) {
		jQuery("#"+ulId).find('li').each( function() {
			let txtValue = jQuery(this).text();
			if (txtValue.toUpperCase().indexOf(searchString.toUpperCase()) > -1) {
		      jQuery(this).show();
		    } else {
		      jQuery(this).hide();
		    }
		});
	}
}

//Function to create column Visibility dialog
Smart_Manager.prototype.createColumnVisibilityDialog = function() {

	let enabledColumnsArray = new Array(),
		hiddenColumnsArray = new Array(),
		colText = '',
		colVal = '',
		temp = '',
		dlgParams = {},
		dlgContent = '',
		idKey = ( window.smart_manager.dashboard_key == 'user' ) ? 'users_id' : 'posts_id';

	for( let key in window.smart_manager.currentColModel ) {

		colObj = window.smart_manager.currentColModel[key];

		if( colObj.hasOwnProperty('data') && colObj.data == idKey ) {
			continue;
		}

		if( colObj.hasOwnProperty('allow_showhide') && colObj.allow_showhide === true ) {

			colText = ( colObj.hasOwnProperty('name_display') ) ? colObj.name_display : '';
			colVal = ( colObj.hasOwnProperty('data') ) ? colObj.data : '';
			colPosition = ( colObj.hasOwnProperty('position') ) ? ( ( colObj.position != '' ) ? colObj.position - 1 : '' ) : '';


			temp = '<li><span class="handle">::</span> '+ colText + ' ' +
						'<input type="hidden" name="columns[]" class="js-column-key" value="'+ colVal +'"> '+
						'<input type="hidden" name="columns_names[]" class="js-column-title" value="'+ colText +'"> '+
					'</li>';

			if( colObj.hasOwnProperty('hidden') && colObj.hidden === false ) {
				enabledColumnsArray[key] = temp;
			} else if( colObj.hasOwnProperty('hidden') && colObj.hidden === true ) {
				hiddenColumnsArray[key] = temp;
			}
		} 
	}

	dlgContent = '<form id="sm-column-visibility"> '+
					'<ul class="unstyled-list"> '+
						'<li> '+
							'Drag & drop the enabled columns to the right side to disable them. Drag & drop the disabled columns to the left side to disable them.'+
						'</li> '+
						'<li> '+
							'Drag the columns to the top or bottom to sort them.'+
						'</li> '+
						'<li> '+
							'<div class="sm-sorter-section"> '+
								'<h3>Enabled</h3> '+
								'<input type="text" id="searchEnabledColumns" data-ul-id="sm-columns-enabled" class="sm-search-box" onkeyup="window.smart_manager.processColumnVisibilitySearch(this)" placeholder="Search For Enabled Columns..."> '+
								'<ul class="sm-sorter columns-enabled" id="sm-columns-enabled"> '+
									enabledColumnsArray.join("") +
								'</ul> '+
							'</div> '+
							'<div class="sm-sorter-section"> '+
								'<h3>Disabled</h3> '+
								'<input type="text" id="searchDisabledColumns" data-ul-id="sm-columns-disabled" class="sm-search-box" onkeyup="window.smart_manager.processColumnVisibilitySearch(this)" placeholder="Search For Disabled Columns..."> '+
								'<ul class="sm-sorter columns-disabled" id="sm-columns-disabled"> '+
									hiddenColumnsArray.join("") +
								'</ul> '+
							'</div> '+
						'</li> '+
					'</ul> '+
					'<input type="hidden" value="" id="sm-all-enabled-columns"> '+
				'</form> ';

	dlgParams.btnParams = {};
	dlgParams.btnParams.yesText = 'Update';
	if ( typeof (window.smart_manager.processColumnVisibility) !== "undefined" && typeof (window.smart_manager.processColumnVisibility) === "function" ) {
		dlgParams.btnParams.yesCallback = window.smart_manager.processColumnVisibility;
	}

	dlgParams.title = 'Column Manager - Show/Hide Columns';
	dlgParams.content = dlgContent;
	dlgParams.height = 600;
	dlgParams.width = 700;

	window.smart_manager.showConfirmDialog(dlgParams);

	if ( typeof (window.smart_manager.columnVisibilityEqualizeHeight) !== "undefined" && typeof (window.smart_manager.columnVisibilityEqualizeHeight) === "function" ) {
		window.smart_manager.columnVisibilityEqualizeHeight();
	}

	let $columns = document.getElementById('sm-columns-enabled'),
		$columnsDisabled = document.getElementById('sm-columns-disabled');

	window.smart_manager.enabledSortable = Sortable.create($columns, {
		group: 'smartManagerColumns',
		animation: 100,
		onSort: function (evt) {
			if ( typeof (window.smart_manager.columnsMoved) !== "undefined" && typeof (window.smart_manager.columnsMoved) === "function" ) {
				window.smart_manager.columnsMoved();
			}
		}
	});
	window.smart_manager.disabledSortable = Sortable.create($columnsDisabled, {
		group: 'smartManagerColumns',
		animation: 100
	});
}

//Function to update the list of enabled columns on column move event
Smart_Manager.prototype.columnsMoved = function() {
	let enabled = jQuery('#sm-column-visibility').find('.columns-enabled li:visible .js-column-key');
	let allEnabled = enabled.map(function () {
		return jQuery(this).val();
	}).get().join(',');
	jQuery('#sm-column-visibility').find('#sm-all-enabled-columns').val(allEnabled);
	window.smart_manager.columnsVisibilityUsed = true;
}

//Function to load the updated list of enabled columns in the grid
Smart_Manager.prototype.processColumnVisibility = function() {
	if( window.smart_manager.columnsVisibilityUsed === false ) {
		return false;
	}

	let enabledColumns = jQuery('#sm-column-visibility').find('#sm-all-enabled-columns').val();

	if( typeof enabledColumns == 'undefined' || typeof window.smart_manager.currentColModel == 'undefined' ) {
		return;
	}

	window.smart_manager.showLoader();

	if( enabledColumns.length > 0 ) {

		let idKey = ( window.smart_manager.dashboard_key == 'user' ) ? 'users_id' : 'posts_id';
        	enabledColumns = idKey + ',' + enabledColumns;

		let enabledColumnsArray = enabledColumns.split(','),
			colVal = '',
			position = 0,
			index = 0;

		window.smart_manager.column_names = new Array('');
		window.smart_manager.currentVisibleColumns = new Array('');

		for( let key in window.smart_manager.currentColModel ) {

			colObj = window.smart_manager.currentColModel[key];

			if( colObj.hasOwnProperty('allow_showhide') && colObj.allow_showhide === true ) {
				colVal = ( colObj.hasOwnProperty('data') ) ? colObj.data : '';

				if( enabledColumnsArray.indexOf(colVal) != -1 ) {

					position = enabledColumnsArray.indexOf(colVal)+1;

					window.smart_manager.currentColModel[key].hidden = false; //Code for refreshing the column visibility
					window.smart_manager.currentColModel[key].position = position; //Code for refreshing the column position

				} else {
					window.smart_manager.currentColModel[key].hidden = true;
				}
			}
		}

		if ( typeof (window.smart_manager.sortColumns) !== "undefined" && typeof (window.smart_manager.sortColumns) === "function" ) {
			window.smart_manager.sortColumns();
		}

		window.smart_manager.currentColModel.forEach(function(colObj){

			let hidden = ( typeof(colObj.hidden) != 'undefined' ) ? colObj.hidden : true;

			if( hidden === false ) {
				if(colObj.hasOwnProperty('name_display') === false) {// added for state management
					colObj.name_display = name;
				}
				let name = (typeof(colObj.name) != 'undefined') ? colObj.name.trim() : '';

				window.smart_manager.column_names[index] = colObj.name_display; //Array for column headers
				window.smart_manager.currentVisibleColumns[index] = colObj;

				index++;
			}
		});

		if ( typeof (window.smart_manager.updateState) !== "undefined" && typeof (window.smart_manager.updateState) === "function" ) {
			let params = { refreshDataModel : true };
			window.smart_manager.updateState(params); //refreshing the dashboard states
		}

		setTimeout( function() { window.smart_manager.showLoader(false) }, 10);
	}
}

//Function to sort the columns in the current_col_model based on the 'position' key
Smart_Manager.prototype.sortColumns = function() {

	if( typeof window.smart_manager.currentColModel == 'undefined' ) {
		return;
	}

	window.smart_manager.indexPointer = 0;

	let enabledColumns = new Array( window.smart_manager.currentColModel[0] ),
		disabledColumns = new Array();
		enabledColumnsFinal = new Array();

	window.smart_manager.currentColModel.forEach(function(colObj){
		enabled = 0;

		if( colObj.hasOwnProperty('position') != false && colObj.hasOwnProperty('hidden') != false ) {
			if( colObj.position != '' && colObj.hidden === false ) {
				enabledColumns[ colObj.position ] = colObj;
				enabled = 1;
			}
		}

		if( enabled == 0 ) {
			disabledColumns.push(colObj);
		}
	});

	enabledColumns.forEach(function(colObj){ //done this to re-index the array for proper array length
		enabledColumnsFinal.push(colObj);
	});

	enabledColumnsFinal.sort(function(a, b) {
		return parseInt(a.position) - parseInt(b.position);
	});

	window.smart_manager.currentColModel = enabledColumnsFinal.concat(disabledColumns);
}

//Function to delete records
Smart_Manager.prototype.deleteRecords = function() {

	if( window.smart_manager.selectedIds.length <= 0 ) {
		return;
	}

	let params = {};
		params.data = {
						cmd: 'delete',
						active_module: window.smart_manager.dashboard_key,
						security: window.smart_manager.sm_nonce,
						ids: JSON.stringify(window.smart_manager.selectedIds)
					};

	window.smart_manager.send_request(params, function(response) {
		if ( 'failed' !== response ) {
			if( jQuery('#sm_top_bar_action_btns_basic #del_sm_editor_grid span').hasClass('sm-ui-state-disabled') === false ) {
				jQuery('#sm_top_bar_action_btns_basic #del_sm_editor_grid span').addClass('sm-ui-state-disabled');
			}
			window.smart_manager.refresh();
			window.smart_manager.showNotification('Success', response);
		}
	});
}


Smart_Manager.prototype.updateLitePromoMessage = function( countRows ) {
	let count = parseInt( countRows );
	if( count >= 2 ) {
		jQuery('.sm_design_notice .sm_sub_headline.action').hide();
		jQuery('.sm_design_notice .sm_sub_headline.response').show();
	}
}

//Function to save inline edited data
Smart_Manager.prototype.saveData = function() {

	if( Object.getOwnPropertyNames(window.smart_manager.editedData).length <= 0 ) {
		return;
	}

	let params = {};
		params.data = {
						cmd: 'inline_update',
						active_module: window.smart_manager.dashboard_key,
						edited_data: JSON.stringify(window.smart_manager.editedData),
						security: window.smart_manager.sm_nonce,
						pro: ( ( typeof(window.smart_manager.sm_beta_pro) != 'undefined' ) ? window.smart_manager.sm_beta_pro : 0 ),
						table_model: (window.smart_manager.currentDashboardModel.hasOwnProperty('tables') ) ? window.smart_manager.currentDashboardModel.tables : ''
					};

	let hasInvalidClass = jQuery('.sm-grid-dirty-cell').hasClass('htInvalid');
	if ( hasInvalidClass == false ) {

		window.smart_manager.send_request(params, function(response) {

			if ( 'failed' !== response ) {

				if( typeof(window.smart_manager.sm_beta_pro) == 'undefined' || ( typeof(window.smart_manager.sm_beta_pro) != 'undefined' && window.smart_manager.sm_beta_pro != 1 ) ) {
					response = JSON.parse( response );
					msg = response.msg;

					if( typeof( response.sm_inline_update_count ) != 'undefined' ) {
						if ( typeof (window.smart_manager.updateLitePromoMessage) !== "undefined" && typeof (window.smart_manager.updateLitePromoMessage) === "function" ) {
							window.smart_manager.updateLitePromoMessage( response.sm_inline_update_count );
						}
					}
				} else {
					msg = response;
				}

				if( window.smart_manager.editedCellIds.length > 0 ) {
					for( let i=0; i<window.smart_manager.editedCellIds.length; i++ ) {
						
						colProp = window.smart_manager.hot.getCellMeta(window.smart_manager.editedCellIds[i].row, window.smart_manager.editedCellIds[i].col);
						currentClassName = ( colProp.hasOwnProperty('className') ) ? colProp.className : '';

						if( currentClassName.indexOf('sm-grid-dirty-cell') != -1 ) {
							currentClassName = currentClassName.substr(0, currentClassName.indexOf('sm-grid-dirty-cell'));
						}

						window.smart_manager.hot.setCellMeta(window.smart_manager.editedCellIds[i].row, window.smart_manager.editedCellIds[i].col, 'className', currentClassName);
						jQuery('.smCheckboxColumnModel input[data-row='+window.smart_manager.editedCellIds[i].row+']').parents('tr').removeClass('sm_edited');
					}

					window.smart_manager.dirtyRowColIds = {};
					window.smart_manager.refresh();
				}
				window.smart_manager.hot.render();
				window.smart_manager.showNotification( 'Success', msg );

			}

		});
		
	} else {
		window.smart_manager.showNotification( 'Error', 'You have entered incorrect data in the highlighted cells.' );
	}

}

Smart_Manager.prototype.hideNotification = function() {
	jQuery( "#sm_inline_dialog" ).dialog("close");
}

//Function to show notification messages
Smart_Manager.prototype.showNotification = function( title = '', content = '' ) {

	let dlg_title = ( title != '' ) ? title : 'Note',
		dlg_content = ( content != '' ) ? content : 'This feature is available only in the <a href="' + window.smart_manager.pricingPageURL + '" target="_blank">Pro</a> version.';

	let params = {
					title: dlg_title,
					content: '<p style="font-size:1.2em;margin:1em;">'+dlg_content+'</p>',
					modal: true,
					dlg_height: 130,
					dlg_width: 400,
					position_my: 'center center',
					position_at: 'center center',
					target: window,
					titleIsHtml: true,
					display_buttons: false
				};

	window.smart_manager.inline_edit_dlg(params);  

	if( content != '' ) {
		setTimeout(function(){
			window.smart_manager.hideNotification();
		},2000);  	
	}
	
}

//Function to show progress dialog
Smart_Manager.prototype.showProgressDialog = function( title = '' ) {

	let dlg_title = ( title != '' ) ? title : 'Please Wait';
		content = '<div class="sm_beta_background_update_progressbar"> <span class="sm_beta_background_update_progressbar_text" style="" >Initializing...</span></div><div class="sm_beta_batch_update_background_link" >Continue in background</div>'

	let params = {
					title: dlg_title,
					content: content,
					modal: true,
					dlg_height: 175,
					dlg_width: 400,
					position_my: 'center center',
					position_at: 'center center',
					target: window,
					display_buttons: false,
					show_close_icon: false
				};

	window.smart_manager.inline_edit_dlg(params);
}

//Function to show confirm dialog
Smart_Manager.prototype.showConfirmDialog = function( dlgparams ) {

	let dlg_title = ( dlgparams.hasOwnProperty('title') !== false && dlgparams.title != '' ) ? dlgparams.title : 'Warning',
		dlg_content = ( dlgparams.hasOwnProperty('content') !== false && dlgparams.content != '' ) ? dlgparams.content : 'Are you sure?';

	if( dlgparams.hasOwnProperty('btnParams') === false ) {
		dlgparams.btnParams = {};
	}

	let params = {
					title: dlg_title,
					content: dlg_content,
					modal: true,
					dlg_height: ( dlgparams.hasOwnProperty('height') !== false && dlgparams.height != '' ) ? dlgparams.height : 150,
					dlg_width: ( dlgparams.hasOwnProperty('width') !== false && dlgparams.width != '' ) ? dlgparams.width : 400,
					position_my: 'center center',
					position_at: 'center center',
					target: window,
					display_buttons: true,
					titleIsHtml: ( dlgparams.hasOwnProperty('titleIsHtml') !== false ) ? dlgparams.titleIsHtml : false,
					buttons_model: [{
										text: ( (dlgparams.btnParams.hasOwnProperty('yesText')) ? dlgparams.btnParams.yesText : 'Yes' ),
										class: 'sm-dlg-btn-yes',
										click: function() {
											if( dlgparams.btnParams.hasOwnProperty('yesCallback') && typeof dlgparams.btnParams.yesCallback === "function" ) {
												dlgparams.btnParams.yesCallback();
											}
											jQuery( this ).dialog( "close" );
										}
									},
									{
										text: ( (dlgparams.btnParams.hasOwnProperty('noText')) ? dlgparams.btnParams.noText : 'No' ),
										class: 'sm-dlg-btn-no',
										click: function() {
											if( dlgparams.btnParams.hasOwnProperty('noCallback') && typeof dlgparams.btnParams.noCallback === "function" ) {
												dlgparams.btnParams.noCallback();
											}
											jQuery( this ).dialog( "close" );
										}
									}
							],
				};

	window.smart_manager.inline_edit_dlg(params);  
}

Smart_Manager.prototype.refreshDashboardStates = function() {

	let tempDashModel = JSON.parse(JSON.stringify(window.smart_manager.currentDashboardModel));
	let tempColModel = JSON.parse(JSON.stringify(window.smart_manager.currentColModel));
	
	tempColModel.shift();
	tempDashModel.columns = new Array();
	tempColModel.forEach(function(colObj) {
		if( typeof(colObj.hidden) != 'undefined' && colObj.hidden === false ) {
			tempDashModel.columns.push(colObj);
		}
	});

	window.smart_manager.dashboardStates[window.smart_manager.dashboard_key] = JSON.stringify({'columns': tempDashModel.columns, 'sort_params': tempDashModel.sort_params});
}

//Function to handle the state apply at regular intervals
Smart_Manager.prototype.updateState = function(refreshParams) {

	if ( typeof (window.smart_manager.refreshDashboardStates) !== "undefined" && typeof (window.smart_manager.refreshDashboardStates) === "function" ) {
		window.smart_manager.refreshDashboardStates(); //refreshing the dashboard states
	}

	if( Object.getOwnPropertyNames(window.smart_manager.dashboardStates).length <= 0 ) {
		return;
	}

	//Ajax request to update the dashboard states
	let params = {};
		params.data_type = 'json';
		params.data = {
						cmd: 'save_state',
						security: window.smart_manager.sm_nonce,
						active_module: window.smart_manager.dashboard_key,
						dashboard_states: window.smart_manager.dashboardStates
					};
		params.showLoader = false;

		if( refreshParams ) {
			if( typeof refreshParams.async != 'undefined' ) {
				params.async = refreshParams.async;
			}
		}

	window.smart_manager.send_request(params, function(refreshParams, response) {
			window.smart_manager.dashboardStates = {};
			if( refreshParams ) {
				if( typeof refreshParams.refreshDataModel != 'undefined' ) {
					window.smart_manager.refresh();
				}
			}
	}, refreshParams);
}

if(typeof window.smart_manager === 'undefined'){
	window.smart_manager = new Smart_Manager();
}

//Events to be handled on document ready
jQuery(document).ready(function() {
	window.smart_manager.init();
});

jQuery.widget('ui.dialog', jQuery.extend({}, jQuery.ui.dialog.prototype, { 
        _title: function(title) { 
            let $title = this.options.title || '&nbsp;' 
            if( ('titleIsHtml' in this.options) && this.options.titleIsHtml == true ) 
                title.html($title); 
            else title.text($title); 
        } 
}));

//Code for custom rendrers and extending Handsontable
(function(Handsontable){
	  let defaultTextEditor = Handsontable.editors.TextEditor.prototype.extend();

	//Function to override the SelectEditor function to handle color codes
    Handsontable.editors.SelectEditor.prototype.prepare = function () {
      	
      	// Call the original prepare method
      	Handsontable.editors.BaseEditor.prototype.prepare.apply(this, arguments);

      	let _this2 = this,
      		selectOptions = this.cellProperties.selectOptions,
      		colorCodes = ( typeof(this.cellProperties.colorCodes) != 'undefined' ) ? this.cellProperties.colorCodes : '',
      		options = '';
		
			if (typeof selectOptions === 'function') {
				options = this.prepareOptions(selectOptions(this.row, this.col, this.prop));
			} else {
		    	options = this.prepareOptions(selectOptions);
		  	}

	      	this.select.innerHTML = '';

	      	Object.entries(options).forEach(([key, value]) => {
				let optionElement = document.createElement('OPTION');
					optionElement.value = key;

				if( colorCodes != ''  ) {
					for( let color in colorCodes ) {
						if( colorCodes[color].indexOf(key) != -1 ) {
							optionElement.className = 'sm_beta_select_'+color;
							break;		
						}
					}
				}

				optionElement.innerHTML = value;
				_this2.select.appendChild(optionElement);	
			});
	};

	  let dateTimeEditor = Handsontable.editors.TextEditor.prototype.extend();

        dateTimeEditor.prototype.createElements = function () {
          // Call the original createElements method
          Handsontable.editors.TextEditor.prototype.createElements.apply(this, arguments);

          // Create datepicker input and update relevant properties
          this.TEXTAREA = document.createElement('input');
          this.TEXTAREA.setAttribute('type', 'text');
          this.TEXTAREA.className = 'htDateTimeEditor';
          this.textareaStyle = this.TEXTAREA.style;
          this.textareaStyle.width = 0;
          this.textareaStyle.height = 0;

          // Replace textarea with datepicker
          Handsontable.dom.empty(this.TEXTAREA_PARENT);
          this.TEXTAREA_PARENT.appendChild(this.TEXTAREA);

          let format = 'Y-m-d H:i:s';
            jQuery('.htDateTimeEditor').Zebra_DatePicker({ format: format,
                                        show_icon: false,
                                        show_select_today: false,
                                        default_position: 'below',
                                    });
        };

        function numericRenderer(hotInstance, td, row, col, prop, value, cellProperties) {
		    Handsontable.renderers.NumericRenderer.apply(this, arguments);
		    if(!value || value === '' || value == null || value === 0 || value === 0.00 || value === '0' || value === '0.00' ) {
		        td.innerHTML = '<div class="htRight htNumeric htNoWrap">' + ( ( value === '0.00' || value === 0.00 ) ? '0.00': ( value === '0' || value === 0 ) ? '0' : '' ) + '</div>';
		    }

		    return td;
		}
	  	Handsontable.renderers.registerRenderer('numericRenderer', numericRenderer);

      function datetimeRenderer(hotInstance, td, row, column, prop, value, cellProperties) {
        if( typeof(cellProperties.className) != 'undefined' ) { //code to higlight the cell on selection
            td.setAttribute('class',cellProperties.className);
        }

        td.innerHTML = value;

        return td;
      }

		function longstringRenderer(hotInstance, td, row, column, prop, value, cellProperties) {
			Handsontable.renderers.HtmlRenderer.apply(this, arguments);
			if( typeof(cellProperties.className) != 'undefined' ) { //code to higlight the cell on selection
				td.setAttribute('class',cellProperties.className);
			}

			td.innerHTML = '<div class="wrapper">' + td.innerHTML + '</div>';

			return td;
		}

		function selectValueRenderer(hotInstance, td, row, column, prop, value, cellProperties) {
			let source = cellProperties.selectOptions,
				className = ( typeof(cellProperties.className) != 'undefined' ) ? cellProperties.className : '',
				colorCodes = ( typeof(cellProperties.colorCodes) != 'undefined' ) ? cellProperties.colorCodes : '';

			if( className != '' ) { //code to higlight the cell on selection
				td.setAttribute('class',className);
			}

			if( typeof source != 'undefined' && typeof value != 'undefined' && source.hasOwnProperty(value) ) {
				td.setAttribute('data-value',value);

				if( colorCodes != '' ) {					
					for( let color in colorCodes ) {
						if( colorCodes[color].indexOf(value) != -1 ) {
							className = (( className != '' ) ? className + ' ' : '') + 'sm_beta_select_'+color;
							td.setAttribute('class',className);
							break;		
						}
					}
				}
				td.innerHTML = source[value];
			}

			return td;
		}

		function multilistRenderer(hotInstance, td, row, column, prop, value, cellProperties) {
		// ...renderer logic
			Handsontable.renderers.TextRenderer.apply(this, arguments);
			if( typeof(cellProperties.className) != 'undefined' ) { //code to higlight the cell on selection
				td.setAttribute('class',cellProperties.className);
			}
			
			td.innerHTML = '<div class="wrapper" style="line-height:30px;">' + td.innerHTML + '</div>';

			return td;
		}
		
	  Handsontable.renderers.registerRenderer('selectValueRenderer', selectValueRenderer);

	  function imageRenderer(hotInstance, td, row, column, prop, value, cellProperties) {
			let escaped = Handsontable.helper.stringify(value),
				img,
				className = 'sm_image_thubnail';

		  if (escaped.indexOf('http') === 0) {
			img = document.createElement('IMG');
			img.src = value;
			img.width = 45;
			img.height = 45;

			img.setAttribute('class',className);

			Handsontable.dom.addEvent(img, 'mousedown', function (e){
			  e.preventDefault(); // prevent selection quirk
			});

			Handsontable.dom.empty(td);
			td.appendChild(img);
		  }
		  else {
			// render as text
			Handsontable.renderers.TextRenderer.apply(this, arguments);
		  }

		  if( typeof(cellProperties.className) != 'undefined' ) {
				className += ' '+ cellProperties.className;
				td.setAttribute('class',cellProperties.className);
		  }

		  return td;
	  }

	  // Register an alias for datetime
	  Handsontable.cellTypes.registerCellType('sm.datetime', {
        editor: dateTimeEditor,
        renderer: datetimeRenderer,
        allowInvalid: true,
      });

	  // Register an alias for image
	  Handsontable.cellTypes.registerCellType('sm.image', {
		renderer: imageRenderer,
		allowInvalid: true,
	  });

	  // Register an alias for longstrings
	  Handsontable.cellTypes.registerCellType('sm.longstring', {
		editor: defaultTextEditor,
		renderer: multilistRenderer,
		allowInvalid: true,
	  });

	  // Register an alias for serialized
	  Handsontable.cellTypes.registerCellType('sm.serialized', {
		editor: defaultTextEditor,
		renderer: multilistRenderer,
		allowInvalid: true,
	  });

	// Register an alias for multilist
	  Handsontable.cellTypes.registerCellType('sm.multilist', {
		editor: defaultTextEditor,
		renderer: multilistRenderer,
		allowInvalid: true,
	  });

})(Handsontable);
