
// init namespace
if ( typeof WPLGE != 'object') var WPLGE = {};

// main controller
WPLGE.GridController = (function () {

    // private properties and constants
    var self = {};

    const AJAX_BASE  = window.wpleApiSettings.wple_ajax_base;
    const GRID_THEME = window.wpleApiSettings.wple_grid_theme;

    var listing_profiles = window.wpleApiSettings.wple_listing_profiles;
    var listing_profile_ids = jQuery.map(listing_profiles, function(profile, idx){
      return profile.id;
    });

    var ebay_accounts = window.wpleApiSettings.wple_ebay_accounts;
    var ebay_account_ids = jQuery.map(ebay_accounts, function(account, idx){
      return account.id;
    });
    // console.log('listing_profile_ids: ', listing_profile_ids);

    var numeric_comparator = function (valueA, valueB, nodeA, nodeB, isInverted) {
        return valueA - valueB;
    }

    // specify the columns
    var columnDefs = [
      {
        headerName: "ID", 
        field: "id",
        width: 90,
        pinned: 'left',
        hide: true,
      },
      {
        headerName: "", 
        field: "_selected",
        width: 50,
        checkboxSelection: true,
        pinned: 'left',
        hide: true,
      },
      {
        headerName: "", 
        field: "thumb",
        width: GRID_THEME == 'alpine' ? 42 : 29,  // 42px for alpine, 29px for balham
        pinned: 'left',
        cellClass: 'listing-image',
        cellRenderer: imageCellRenderer,
        resizable: false,
      },
      {
        headerName: "SKU", 
        field: "sku",
        width: 150,
        editable: true,
        sortable: true, 
        filter: true,
      },
      {
        headerName: "Title", 
        field: "auction_title",
        // width: 700,
        flex: 1,
        editable: true,
        sortable: true, 
        filter: true,
        cellClass: 'listing-title',
        cellRenderer: titleCellRenderer,
      },
      {
        headerName: "Stock", 
        field: "quantity",
        width: 90,
        editable: true,
        sortable: true, 
        filter: false,
        // filter: 'agNumberColumnFilter',
        type: "numericColumn",
        comparator: numeric_comparator,
        cellClass: 'listing-stock ag-numeric-cell ag-right-aligned-cell',
        cellRenderer: stockCellRenderer,
      },
      {
        headerName: "MSRP", 
        field: "_msrp_price",
        width: 90,
        editable: true,
        sortable: true, 
        filter: false,
        valueFormatter: currencyFormatter,
        type: "numericColumn",
        comparator: numeric_comparator,
        hide: true,
      },
      {
        headerName: "Price", 
        field: "_regular_price",
        width: 100,
        editable: true,
        sortable: true, 
        filter: false,
        valueFormatter: currencyFormatter,
        type: "numericColumn",
        comparator: numeric_comparator,
      },
      {
        headerName: "Sale Price", 
        field: "_sale_price",
        width: 100,
        editable: true,
        sortable: true, 
        filter: false,
        valueFormatter: currencyFormatter,
        type: "numericColumn",
        comparator: numeric_comparator,
      },
      {
        headerName: "eBay Price", 
        field: "_ebay_start_price",
        width: 110,
        editable: true,
        sortable: true, 
        filter: false,
        valueFormatter: currencyFormatter,
        type: "numericColumn",
        comparator: numeric_comparator,
        // hide: true,
      },
      {
        headerName: "Amazon Price", 
        field: "_amazon_price",
        width: 125,
        editable: true,
        sortable: true, 
        filter: false,
        valueFormatter: currencyFormatter,
        type: "numericColumn",
        comparator: numeric_comparator,
        hide: true,
      },
      {
        headerName: "Min.", 
        field: "_amazon_minimum_price",
        width: 90,
        editable: true,
        sortable: true, 
        filter: false,
        valueFormatter: currencyFormatter,
        type: "numericColumn",
        comparator: numeric_comparator,
        hide: true,
      },
      {
        headerName: "Max.", 
        field: "_amazon_maximum_price",
        width: 90,
        editable: true,
        sortable: true, 
        filter: false,
        valueFormatter: currencyFormatter,
        type: "numericColumn",
        comparator: numeric_comparator,
        hide: true,
      },
      {
        headerName: "Profile", 
        field: "profile_id",
        width: 150,
        editable: true,
        sortable: true, 
        filter: true,
        valueFormatter: profileCellRenderer,
        cellEditor: 'agSelectCellEditor',
        // cellEditor: 'agPopupSelectCellEditor',
        cellEditorParams: {
          values: listing_profile_ids,
        },
      },
      {
        headerName: "Account", 
        field: "account_id",
        width: 150,
        editable: false,
        sortable: true, 
        filter: true,
        valueFormatter: accountCellRenderer,
      },
      {
        headerName: "Status", 
        field: "status",
        width: 120, // 90 for balham
        editable: true,
        sortable: true, 
        filter: true,
        cellClass: 'column-status',
        // cellClass: function(params) { return ['listing-status','status-'+params.value]; },
        cellRenderer: function(params) { return '<div class="listing-status status-'+params.value+'"><span>'+params.value+'</span></div>'; },
        cellEditor: 'agSelectCellEditor',
        cellEditorParams: {
          values: ['prepared', 'verified', 'published', 'changed', 'ended'],
        },
        pinned: 'right',
        resizable: false,
      },
      {
        headerName: "Lock", 
        field: "locked",
        sortable: true, 
        filter: true,
        hide: true,
      },
    ];


    // let the grid know which columns and what data to use
    var gridOptions = {
      defaultColDef: {
        resizable: true,
      },
      columnDefs: columnDefs,
      rowSelection: 'multiple',
      // floatingFilter: true,            // enable filter row below column headers
      // singleClickEdit: true,              // enable single click edit
      undoRedoCellEditing: true,          // enable undo/redo
      undoRedoCellEditingLimit: 20,       // default is 10
      enableCellChangeFlash: true,        // make undo/redo actions become visible
      //rowHeight: 42,                    // default is 25 for balham, but 42 for alpine
      suppressColumnMoveAnimation: true,  // don't animate hiding and showing columns
      suppressColumnMoveAnimation: true,  // don't animate hiding and showing columns
      onGridReady: function(params) {
        console.log('onGridReady()', params);
      },
      onCellValueChanged: function(params) {
        console.log('onCellValueChanged()', params);
        saveItem( params.data.id, params.column.colId, params.newValue );
      },
    };

    // https://www.ag-grid.com/javascript-grid-accessing-data/
    gridOptions.getRowNodeId = function(data) {
        return data.id;
    };


    function titleCellRenderer(params) {
      const edit_url = "post.php?post="+params.data.post_id+"&action=edit";
      const view_url = params.data.ViewItemURL ? params.data.ViewItemURL : 'admin.php?page=wplister&action=wple_preview_auction&auction='+params.data.id+'&_wpnonce='+window.wpleApiSettings.view_nonce+'&width=820&height=550&TB_iframe=true';
      var config_symbol = '<a class="dashicons dashicons-lock" onClick="WPLGE.GridController.onActionButtonClicked('+params.data.id+')"></a>';
      var   edit_symbol = '<a href="'+edit_url+'" class="dashicons dashicons-edit" title="Edit product in new tab" target="_blank"></a>';
      var   view_symbol = '<a href="'+view_url+'" class="dashicons dashicons-visibility thickbox" title="Preview" target="_blank"></a>';
      var buttons = '<div class="buttons">'+view_symbol+edit_symbol+config_symbol+'</div>';
      return params.value + buttons;
    }

    function stockCellRenderer(params) {
      var locked_symbol = '<span class="dashicons dashicons-lock"></span>';
      var unlock_symbol = '<span class="dashicons dashicons-unlock"></span>';
      var symbol        = params.data.locked == '1' ? locked_symbol : unlock_symbol;
      return '<div class="lock_status">'+symbol+'</div>' + params.value;
    }

    function currencyFormatter(params) {
      // TODO: render empty fields in cell renderer
      if ( params.value == '' ) return '---';
      if ( params.value == undefined ) return '---';
      // const options = { style: 'currency', currency: 'USD' };
      const options = { minimumFractionDigits: 2 };
      const numberFormat = new Intl.NumberFormat('en-US', options);
      return numberFormat.format(params.value);
    }

    function imageCellRenderer(params) {
      const imgsize = GRID_THEME == 'alpine' ? 40 : 26;
      return '<img src="'+params.value+'" width="'+imgsize+'" height="'+imgsize+'" className="thumb"/>';
    }

    function profileCellRenderer(params) {
      // return listing_profiles[params.value] ? listing_profiles[params.value].text + ':'+params.value : '---'; 
      return listing_profiles[params.value] ? listing_profiles[params.value].text : '---'; 
    }    

    function accountCellRenderer(params) {
      return ebay_accounts[params.value] ? ebay_accounts[params.value].text : '---'; 
    }    
 
    function onActionButtonClicked(id) {
      console.log('onActionButtonClicked', id);
      var item = WPLGE.GridController.getItem( id );
      // console.log('item', item);

      // toggle locked status
      item.locked = item.locked == 1 ? 0 : 1;

      // update row
      var rowNode = WPLGE.api.getRowNode( id );
      rowNode.setDataValue('locked', item.locked );
      // rowNode.setData(item);

      // refresh entire row - to update lock icon stock column
      WPLGE.api.refreshCells({ rowNodes:[rowNode], force:true });
    }    
 
    function getItem(id) {
      for (var i = WPLGE.listings.length - 1; i >= 0; i--) {
        if ( WPLGE.listings[i].id == id) return WPLGE.listings[i];
      };
      return false;
    }    
 

    function saveItem( key, column, value ) {
      console.log( 'saveItem() ', key, column, value );
      if ( value === undefined ) return;

      const post_data = {
        id : key,
        col: column,
        val: value
      };
      console.log( 'post_data: ', post_data );

      window.jQuery.ajax({
        url: AJAX_BASE + `/listing/${key}`,
        dataType: 'json',
        method: 'POST',
        data: JSON.stringify( post_data ),
        beforeSend: function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', window.wpleApiSettings.rest_nonce );
        },
        success: function(response) {
          if ( true === response ) {
            // all is good
          };
          if ( response.success == true ) {
            console.log('SUCCESS');
            console.log('response:',response);
            if ( response.msg ) {
              showNotification('success', response.msg, 'Success' );
            }
          };
          if ( response.success == false ) {
            console.log('ERROR');
            console.log('response:',response);
            // WPLGE.api.undoCellEditing();

            // show response.errors and/or response.msg to the user!
            if ( response.errors.constructor === Array ) {
              response.errors.forEach( processResponseErrors )
            } else {
              showNotification('info',response.msg, 'Can\'t do that...');
            }

            // update row data with data from response
            var rowNode = WPLGE.api.getRowNode( response.item.id );
            console.log('response.item.id:',response.item.id);
            console.log('rowNode:',rowNode);
            rowNode.setData( response.item );

          };
        }.bind(this)
        
      });      
    } // saveItem()

    function processResponseErrors(element, index, array) {
      // console.log('a[' + index + '] = ' + element);

      var title = 'Error '+ element.ErrorCode;

      var notifyType = 'info';
      if ( 'Error' == element.SeverityCode )   notifyType = 'error';
      if ( 'Warning' == element.SeverityCode ) notifyType = 'warning';
      if ( 'Validation' == element.SeverityCode ) {
        notifyType = 'warning';
        title = 'Validation check failed';
      }

      showNotification( notifyType, element.LongMessage, title );
    }

    function showNotification( level, message, title ) {

      // //Overriding default options
      // Lobibox.notify.OPTIONS = $.extend({}, Lobibox.notify.OPTIONS, {        
      // });

      // //Overriding default options - not working
      // Lobibox.base.OPTIONS = $.extend({}, Lobibox.base.OPTIONS, {
      //   icons: {
      //       bootstrap: {
      //           // confirm: 'glyphicon glyphicon-question-sign',
      //           confirm: 'dashicons dashicons-info',
      //           success: 'dashicons dashicons-yes',
      //           // success: 'dashicons dashicons-yes-alt',
      //           error: 'dashicons dashicons-no',
      //           // error: 'dashicons dashicons-dismiss',
      //           warning: 'dashicons dashicons-warning',
      //           info: 'dashicons dashicons-info'
      //       },
      //       fontAwesome: {
      //           confirm: 'fa fa-question-circle',
      //           success: 'fa fa-check-circle',
      //           error: 'fa fa-times-circle',
      //           warning: 'fa fa-exclamation-circle',
      //           info: 'fa fa-info-circle'
      //       }
      //   }
      // });

      var iconClass = 'dashicons-info';
      // if ( 'error'   == level ) iconClass = 'dashicons-no';
      if ( 'error'   == level ) iconClass = 'dashicons-dismiss';
      if ( 'warning' == level ) iconClass = 'dashicons-warning';
      if ( 'success' == level ) iconClass = 'dashicons-yes-alt';

      var delay = 5000;
      if ( 'error'   == level ) delay = 15000;
      if ( 'warning' == level ) delay = 15000;
      if ( 'success' == level ) delay =  5000;

      Lobibox.notify(level, {
        title: title,
        msg: message,
        position: 'top right',
        icon: 'dashicons ' + iconClass,
        rounded: true,
        sound: false,
        delay: delay,
        pauseDelayOnHover: true,
        continueDelayOnInactiveTab: false,
        messageHeight: false, /* max-height */
      });
    }

    // init - when document ready
    var init = function () {
        self = this; // assign reference to current object to "self"
        console.log('init()');

        // adjust height and width
        jQuery('#wplge_grid_container').css('height',window.innerHeight - 30 - 56);
        jQuery('#wplge_grid_container').css('width', window.innerWidth - 160     );

        // lookup the container we want the Grid to use
        var wplgeGridDiv = document.querySelector('#wplge_grid_container'); // try to use #wpbody (todo)

        // create the grid passing in the div to use together with the columns & data we want to use
        new agGrid.Grid(wplgeGridDiv, gridOptions);

        // shortcut to WPLGE.GridController.gridOptions.api
        WPLGE.api  = gridOptions.api;
        WPLGE.grid = gridOptions;
        WPLGE.cols = columnDefs;

    } // init()

    jQuery(document).ready(init); // call init() when document is ready

    // resize grid automatically
    window.onresize = function() {
        jQuery('#wplge_grid_container').css('height',window.innerHeight - 30 - 56).css('width', window.innerWidth - 160 );
    }

    return {
        // declare which properties and methods are supposed to be public
        init: init,
        getItem: getItem,
        gridOptions: gridOptions,
        onActionButtonClicked: onActionButtonClicked,
    }
})(); // WPLGE.GridController


WPLGE.VueBar = new Vue({
  el: '#wplge_grid_header',
  data: {
    ebay_accounts:     window.wpleApiSettings.wple_ebay_accounts,
    listing_profiles:  window.wpleApiSettings.wple_listing_profiles,
    filter_status:     '',
    filter_profile_id: '',
    filter_account_id: '',
    message:           '',
  },
  components: {
  },

  methods: {
    toggleGridColumn: function (event) {
      console.log('vue.toggleGridColumn()',event);
    },
    btnToggleGridSelection: function (event) {
      console.log('vue.btnToggleGridSelection()',event);
      var is_visible = WPLGE.grid.columnApi.getColumn('_selected').visible;
      WPLGE.grid.columnApi.setColumnVisible('_selected', ! is_visible );
    },
    btnToggleGridFilter: function (event) {
      console.log('vue.btnToggleGridFilter()',event);
      var is_visible = WPLGE.grid.floatingFilter;
      WPLGE.grid.floatingFilter = ! is_visible;
      WPLGE.grid.api.refreshHeader();
    },
    btnOpenGridSettings: function (event) {
      console.log('vue.btnOpenGridSettings()',event);

      Lobibox.window({
          title: 'Select columns',
          width: 800,
          modal: true,
          //Available types: string, jquery object, function
          content: function(){
              return $('#wplge_grid_options');
          },
          buttons: {
              // load: {
              //     text: 'Save'
              // },
              close: {
                  text: 'Close',
                  closeOnClick: true
              }
          },
          callback: function($this, type, ev){
              console.log('OptionsBox callback');  
              if (type === 'load'){
                  console.log('clicked load button');
              }
          }
      });

      // init Vue on options window content
      jQuery('.lobibox-body #wplge_grid_options').show();
      WPLGE.VueOptions = new Vue({
        el: '.lobibox-body #wplge_grid_options',
        data: {
          columns:          WPLGE.cols,
          colstate:         [],
          message:          '',
          testyes:          true,
          testno:           false,
          visibleClass:     'dashicons-yes',
          invisibleClass:   'dashicons-no-alt',
          // visibleClass:     'dashicons-yes-alt',
          // invisibleClass:   'dashicons-dismiss',
          // visibleClass:     'dashicons-visibility',
          // invisibleClass:   'dashicons-hidden',
        },
        // computed: {
        //   // a computed getter
        //   isColumnVisible: function ( col ) {
        //     // `this` points to the vm instance
        //     console.log('col: ',col);
        //     return true;
        //   },
        // },
        methods: {
          isColumnVisible: function ( col ) {
            // `this` points to the vm instance
            // console.log('col: ',col);
            for (var i = this.colstate.length - 1; i >= 0; i--) {
              if ( this.colstate[i].colId != col ) continue;
              return this.colstate[i].hide ? false : true;
            }
            return null;
          },
          btnToggleGridColumn: function (event) {
            // console.log('VueOptions.btnToggleGridColumn()',event);
            // console.log('key1: ',event.target.dataset.key);
            // console.log('key2: ',event.target.parentElement.dataset.key);
            var columnKey = event.target.dataset.key;
            if ( columnKey == null ) {
              columnKey = event.target.parentElement.dataset.key;
            };
            if ( columnKey == null ) return;
            var is_visible = WPLGE.grid.columnApi.getColumn(columnKey).visible;
            WPLGE.grid.columnApi.setColumnVisible(columnKey, ! is_visible );

            this.colstate = WPLGE.grid.columnApi.getColumnState();
          },
        },

        created: function () {
          console.log('VueOptions.created()');  
          // this.message = 'OK';
  
          this.colstate = WPLGE.grid.columnApi.getColumnState();
        },

      }) // WPLGE.VueOptions



    },
    reloadGridData: function (event) {
      this.message = 'Loading listings...';
      this.filter_status     = '';
      this.filter_profile_id = '';
      this.filter_account_id = '';

      window.jQuery.ajax({
        url: window.wpleApiSettings.wple_ajax_base + '/listings',
        beforeSend: function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', window.wpleApiSettings.rest_nonce );
        },
        success: function(data) {
          if ( data.items ) {
            // console.log('loaded data:',data);

            // update data store
            WPLGE.listings = data.items;

            WPLGE.api.setRowData(data.items);
            WPLGE.VueBar.message = data.items.length.toString() + ' listings loaded'; // TODO

            // clear filters
            WPLGE.grid.api.getFilterInstance('locked').setModel();
            WPLGE.grid.api.getFilterInstance('status').setModel();
            WPLGE.grid.api.getFilterInstance('profile_id').setModel();
            WPLGE.grid.api.getFilterInstance('account_id').setModel();

            // WPLGE.grid.api.onFilterChanged(); // to fix: reloading items does not apply filters!
          };
        }.bind(this)
        
      });

    },
    quickFilterChanged: function (event) {
      var filterValue = jQuery('#wplge_quickfilter_input').val();
      this.message = filterValue ? 'Filtering by "'+filterValue+'"' : '';
      WPLGE.grid.api.setQuickFilter( filterValue );
    },
    profileFilterChanged: function (event) {
      // var filterValue = event.target.value;
      var filterValue = this.filter_profile_id;
      this.message = filterValue ? 'Filtering by profile #'+filterValue+'' : '';

      var profileFilterComponent = WPLGE.grid.api.getFilterInstance('profile_id');
      // profileFilterComponent.selectNothing();
      // profileFilterComponent.selectValue( filterValue );
      // profileFilterComponent.applyModel();
      profileFilterComponent.setModel({
          type: 'equals',
          filter: filterValue
      });
      WPLGE.grid.api.onFilterChanged();
    },
    accountFilterChanged: function (event) {
      // var filterValue = event.target.value;
      var filterValue = this.filter_account_id;
      this.message = filterValue ? 'Filtering by account #'+filterValue+'' : '';

      var accountFilterComponent = WPLGE.grid.api.getFilterInstance('account_id');
      accountFilterComponent.setModel({
          type: 'equals',
          filter: filterValue
      });
      WPLGE.grid.api.onFilterChanged();
    },
    doBulkAction: function (event) {
      alert('Support for bulk actions will be coming very soon!');
    },
    statusFilterChanged: function (event) {
      var filterValue = event.target.value;
      this.message = filterValue ? 'Displaying '+filterValue+' items only' : '';
      
      // first clear all filters to ensure only one is active at a time
      WPLGE.grid.api.getFilterInstance('locked').setModel();
      WPLGE.grid.api.getFilterInstance('status').setModel();

      if ( filterValue == 'locked' || filterValue == 'unlocked' ) {
        var filterInstance = WPLGE.grid.api.getFilterInstance('locked');
        filterInstance.setModel({
            type: 'equals',
            filter: filterValue == 'locked' ? '1' : '0'
        });
      } else {
        var filterInstance = WPLGE.grid.api.getFilterInstance('status');
        filterInstance.setModel({
            type: 'equals',
            filter: filterValue
        });
      }
      WPLGE.grid.api.onFilterChanged();
    },
  },

  created: function () {
    // console.log('Vue.created()');  

    this.reloadGridData();

  },

}) // WPLGE.VueBar



