@extends('layouts.app')

@section('title', 'Bulk Add')

@section('theme_js')
<script src="{{ asset('limitless/global_assets/js/plugins/tables/handsontable/handsontable.min.js') }}"></script>
<script src="{{ asset('limitless/global_assets/js/plugins/tables/handsontable/sheetclip.js') }}"></script>
<script src="{{ asset('limitless/global_assets/js/plugins/notifications/jgrowl.min.js') }}"></script>
<script src="{{ asset('limitless/global_assets/js/plugins/notifications/noty.min.js') }}"></script>
<script src="{{ asset('limitless/global_assets/js/plugins/notifications/sweet_alert.min.js') }}"></script>
@endsection

@section('content')

<div class="card">
	<div class="card-header header-elements-inline">
		<h5 class="card-title">Copy-paste</h5>
		<div class="header-elements">
			<div class="list-icons">
				<a class="list-icons-item" data-action="collapse"></a>
				<a class="list-icons-item" data-action="reload"></a>
			</div>
		</div>
	</div>

	<div class="card-body">
		<div class="row mb-3">
			<div class="col-md-8">
				<p class="">Gunakan shortcuts <kbd>Ctrl (Cmd) + C</kbd> and <kbd>Ctrl (Cmd) + V</kbd> untuk menyalin data dari file excel atau csv anda.</p>
			</div>
			<div class="col-md-4 text-right">
			{{--
				<button type="button" class="btn btn-default validation-check">Cek Validasi</button>
			--}}
				<button type="button" class="btn btn-primary save save-bulk">Simpan</button>
			</div>
		</div>
		
		<div class="row">
			<div class="col-12">
				<div class="alert error alert-danger alert-dismissible d-none">
					<span class="error_area"></span>
				</div>
			</div>
			
			<div class="col-12">
				<div class="alert success alert-success alert-dismissible d-none">
					<span class="success_area"></span>
				</div>
			</div>
		</div>
		
		
		<div class="hot-container">
			<!-- <pre id="example1console" class="console"></pre>
			<div id="hot_context_copy" class="handsontable"></div> -->
			<div id="hot_context_copy"></div>
		</div>
	</div>
</div>

@endsection

@section('my_script')
<script>
var HotContextMenu, hot_context_copy_init;
$(document).ready(()=>{
	HotContextMenu.init();
	
	Noty.overrideDefaults({
            theme: 'limitless',
            layout: 'topRight',
            type: 'alert',
            timeout: 3500
        });
	
	$('a[data-action="reload"]').click(()=>{
		HotContextMenu.init()
	})
	
	$('.save').click(()=>{
		save()
	})
	
	$('.validation-check').click(()=>{
		hot_context_copy_init.validateCells((isValid)=>{
			if(isValid){
				new Noty({
					text: 'Semua data valid.',
					type: 'success'
				}).show();
			}  
		})
	})
})

HotContextMenu = function() {
	var _componentHotContextMenu = function() {
			if (typeof Handsontable == 'undefined') {
				console.warn('Warning - handsontable.min.js is not loaded.');
				return;
			}	
		var car_data = [
				{road_code: "", road_name: "", length: "", month: "", year: ""},
			];
			
		var hot_context_copy = document.getElementById('hot_context_copy');
		$('#hot_context_copy').html('')
		var sheetclip = new SheetClip();
		var clipboardCache = '';
		
		var cekString = function(value,callback){
			if (value == '' || value === null) {
			  callback(false);
			}
			else {
			  callback(true);
			}
		}
		var cekNum = function(value,callback){
			if (value == '' || value === null || typeof(value) == 'string') {
			  callback(false);
			}
			else {
			  callback(true);
			}
		}
		var cekNumYear = function(value,callback){
			if (value == '' || value === null || typeof(value) == 'string') {
				callback(false);
			}
			else {
				if(value > 999 && value < 3000){
					callback(true);
				}else{
					callback(false);
				}
			}
		}
		var cekNumMon = function(value,callback){
			if (value == '' || value === null || typeof(value) == 'string') {
				callback(false);
			}
			else {
				if(value > 0 && value < 13){
					callback(true);
				}else{
					callback(false);
				}
			}
		}
		
		hot_context_copy_init = new Handsontable(hot_context_copy, {
				data: car_data,
				rowHeaders: true,
				stretchH: 'all',
				colHeaders: ['Road Code', 'Road Name', 'Length (m)', 'Month', 'Year'],
				columns: [
					{
						data: 'road_code',
						type: 'numeric',
						className: 'htLeft',
						width: 50,
						validator: cekNum
					},
					{
						data: 'road_name',
						validator: cekString
					},
					{
						data: 'length',
						type: 'numeric',
						className: 'htLeft',
						numericFormat: {
							// pattern: '0%'
							pattern: '0'
						},
						width: 50,
						validator: cekNum
					},
					{
						data: 'month',
						type: 'numeric',
						className: 'htLeft',
						width: 50,
						validator: cekNumMon
					},
					{
						data: 'year',
						type: 'numeric',
						className: 'htLeft',
						width: 50,
						validator: cekNumYear
					},
				],
				afterCopy: function(changes) {
					clipboardCache = sheetclip.stringify(changes);
				},
				afterCut: function(changes) {
					clipboardCache = sheetclip.stringify(changes);
				},
				afterPaste: function(changes) {
					clipboardCache = sheetclip.stringify(changes);
				},
				afterValidate: function(isValid, value, row, prop){
					if(!isValid){
						if(prop == 'road_name'){
							new Noty({
								text: 'Kolom '+prop+' baris ke '+(row+1)+' harus diisi.',
								type: 'warning'
							}).show();
						}else if(prop == 'year'){
							new Noty({
								text: 'Format tahun baris ke '+(row+1)+' salah.',
								type: 'warning'
							}).show();
						}else if(prop == 'month'){
							new Noty({
								text: 'Format bulan baris ke '+(row+1)+' salah.',
								type: 'warning'
							}).show();
						}else{
							if(value=='0'||value==0){
								new Noty({
									text: 'Kolom '+prop+' baris ke '+(row+1)+' harus lebih dari 0 meter.',
									type: 'warning'
								}).show();
							}else{
								new Noty({
									text: 'Kolom '+prop+' baris ke '+(row+1)+' harus diisi dan harus berupa angka.',
									type: 'warning'
								}).show();
							}

						}
					}
				},
				contextMenu: [
					'row_above',
					'row_below',
					'remove_row',
					'---------',
					'copy',
					'cut',
					{
						key: 'paste',
						name: 'Paste',
						disabled: function() {
							return clipboardCache.length === 0;
						},
						callback: function() {
							var plugin = this.getPlugin('copyPaste');

							this.listen();
							plugin.paste(clipboardCache);
						}
					}
				]
			});
	};
	return {
		init: function() {
			_componentHotContextMenu();
		}
	}
}();

function distinct(value, index, self) { 
    return self.indexOf(value) === index;
}

function save(){
	
	hot_context_copy_init.validateCells((isValid)=>{
		if(isValid){
			window.onbeforeunload = function() {
				return 'Data sedang diproses, apakah anda ingin membatalkan ?';
			};
			
			let dataFix = [];
			$.each(hot_context_copy_init.getData(), (k,v)=>{
				dataFix.push({road_code: v[0], road_name: v[1], length: v[2], month: v[3], year: v[4]})
			});
			
			$.ajax({
				type:'post',
				url:"{{ URL::to('history/progres-perkerasan/bulksave') }}",
				data:{ data: dataFix },
				cache:false,
				beforeSend:function(){
					HoldOn();
					$('.error').addClass('d-none')
					$('.error_area').html('')
					$('.success').addClass('d-none')
					$('.success_area').html('')
					$('.save-bulk').attr('disabled','disabled').html('<b><i class="icon-spinner spinner"></i></b> Please wait...')
				},
				complete:function(){
					HoldOff();
					window.onbeforeunload = null
					$('.save-bulk').removeAttr('disabled').html('Simpan')
				},
				headers: {
					"Access-Control-Allow-Origin":"*",
					"X-CSRF-TOKEN": "{{ csrf_token() }}",
				},
				success:function(rsp){
					if(rsp.code==200){
						let cont = rsp.contents
						if(cont.error.length > 0){
							$('.error').removeClass('d-none');
							$.each(cont.error, (k,v)=>{
								$('.error_area').append('Road code '+v.value+' at line '+v.line+' error with status <strong>'+v.status+'<strong><br/>');
							})
						}
						if(cont.success.length > 0){
							$('.success').removeClass('d-none');
							$('.success_area').append('Berhasil memproses Road code: <br/>');
							var succ = cont.success.filter( distinct )
							$.each(succ, (k,v)=>{								
								$('.success_area').append( v+' <br/>' );
							})
						}
					}else{
						swal({
							title: rsp.code,
							text: 'Oops.. '+rsp.contents,
							type: 'error',
							padding: 30
						});
					}
				},
				error: function (xhr, ajaxOptions, thrownError) {
					swal({
						title: xhr.status,
						text: 'Oops.. '+thrownError,
						type: 'error',
						padding: 30
					});
				}
			})
				
		}

	})
}
</script>
@endsection