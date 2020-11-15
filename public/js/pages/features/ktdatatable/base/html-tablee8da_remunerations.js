"use strict";
// Class definition

var KTDatatableHtmlTableDemoRemunerations = function () {
	// Private functions

	// demo initializer
	var demo = function () {

		var datatable = $('#kt_datatable_remuneration').KTDatatable({
			data: {
				saveState: { cookie: false },
			},
			search: {
				input: $('#kt_datatable_search_query_remuneration'),
				key: 'generalSearch'
			},
			columns: [
				{
					field: 'Actions',
					title: 'Actions',
					sortable: false,
					width: 150,
					overflow: 'visible',
					autoHide: false,
				}
			],
		});

	};

	return {
		// Public functions
		init: function () {
			// init dmeo
			demo();
		},
	};
}();

jQuery(document).ready(function () {
	KTDatatableHtmlTableDemoRemunerations.init();
});
