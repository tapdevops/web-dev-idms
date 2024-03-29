<?php 

	if( $data->datax[0]->PO_TYPE == 0 )
	{
		$TYPE_OF_SUBMISSION = 'PO SAP';
	}
	else if( $data->datax[0]->PO_TYPE == 1 )
	{
		$TYPE_OF_SUBMISSION = 'PO AMP';
	}
	else
	{
		$TYPE_OF_SUBMISSION = 'PO ASET LAIN';
	}

	$document_code = $data->datax[0]->document_code;
	if($document_code != "")
	{
		$dc = base64_encode($document_code);
	}
	else
	{
		$dc = "";
	}

?>

<h3>PERMOHONAN PERSETUJUAN PENGAJUAN ASET</h3>

<?php /* <h4>Asset Management PT. Triputra Agro Persada</h4> */ ?>
<h4>Fixed Asset Management System (FAMS)</h4>

<br/>
<br/>
Kepada Yth, <br/>
{{ $data->nama_lengkap }}
<br/>
<br/>
<br/>
Dibutuhkan persetujuan atas dokumen berikut :
<br/>
<br/>
<div style='margin-left:10%'>
	NO DOCUMENT : {{ $data->datax[0]->document_code }} (<a href="<?php echo url('/?noreg='.$dc.'') ?>" target="_blank">detail</a>) <br/>
	TYPE OF SUBMISSION : {{ $TYPE_OF_SUBMISSION }} <br/>
	PURCHASE NO : {{ $data->datax[0]->NO_PO }} <br/>
	LOKASI KEPEMILIKAN ASET : {{ $data->datax[0]->BA_PEMILIK_ASSET }} - {{ $data->datax[0]->BA_PEMILIK_ASSET_DESC }} <br/><br/>
	<?php 

		//echo "1<pre>"; print_r($data->datax); 
		$content = "";

		if( $data->datax )
		{
			$no = 1;
			$l = " 
				<table border='1' cellpadding='5' cellspacing='0'>
					<tr style='background-color:#EEEEEE'>
						<th>NO</th>
						<th>KODE MATERIAL</th>
						<th>ASSETS</th>
						<th>LOKASI</th>
					</tr>
			";
			foreach($data->datax as $k => $v)
			{
				$l .= " 
					<tr>
						<td>$no</td>
						<td>$v->KODE_MATERIAL</td>
						<td>$v->NAMA_MATERIAL</td>
						<td>$v->LOKASI_BA_CODE - $v->LOKASI_BA_CODE_DESC</td>
					</tr> 
				";
				$no++;
			}
			$l .= "</table>";

			$content = $l; 
		}

		echo $content;
	?>
	<br/>
	<h4> Historical Approval : </h4>
	<?php 

		$content2 = "";
		
		//echo "1<pre>"; print_r($data->history_approval);
		
		if( $data->history_approval )
		{
			$no = 1;
			$l = "";
			$note = "";

			foreach( $data->history_approval as $kk => $vv )
			{
				if( $vv->notes != "" )
				{
					$note .= "( ".$vv->notes." )";
				}

				$l .= $no.". ".$vv->name." :: ".$vv->status_approval." ".$note." <br/>";
				$no++;
			}

			$content2 = $l;
		} 

		echo $content2; 

	?>
</div>

<br/>
<br/>
<br/>

<?php /*
<h2>CATATAN PENTING</h2>
1.Melakukan Persetujuan secara Parsial <br/>
2.Melihat sejarah dokumen <br/>
3.Mengurangi jumlah barang/ jasa yang diajukan <br/>
4.Pengajuan revisi ke pembuat dokumen <br/>
5.Permohonan penambahan data pendukung ke pembuat dokumen <br/>
dapat dilakukan dengan memilih action Detail <br/>
*/ ?>

<hr/>

<i><b>Email ini terbentuk otomatis oleh Sistem. <br/>
Mohon tidak melakukan reply.</b></i><br/>
Apabila ada pertanyaan mengenai email ini dapat menghubungi : <br/>

Email : TAP.callcenter.helpdesk@tap-agri.com <br/>
Ext : 481 <br/>
HP : 0821 1401 3315 <br/>
Website : helpdesk.tap-agri.com
