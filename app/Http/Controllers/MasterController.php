<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Master;
use App\Models\Company;
use App\Models\Estate;
use App\Models\Afdeling;
use App\Models\Block;
use App\Models\VEstate;
use App\Models\VBlock;
use App\Models\VAfdeling;
use AccessRight;
use Yajra\DataTables\Facades\DataTables;
use DB;
use API;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class MasterController extends Controller
{
    
	/*
		Endpoint:
		
			afdeling/all
			block/all
			comp/all
			est/all
			region/all
	
	*/
	
	public function sync_afd()
	{
		$Master = new Master;
		$token = $Master->token();
		$RestAPI = $Master
					->setEndpoint('afdeling/all')
					->setHeaders([
						'Authorization' => 'Bearer '.$token
					])
					->get();
					
		// return $RestAPI;
		if(count($RestAPI['data']) > 0 ){
			foreach($RestAPI['data'] as $data){
				$est = Estate::where('werks',$data['WERKS'])->first();
					if($est){
						try {
								$afd = Afdeling::firstOrNew(array('estate_id' => $est['id'],'afdeling_code' => $data['AFD_CODE']));
								$afd->region_code = $data['REGION_CODE'];
								$afd->company_code = $data['COMP_CODE'];
								$afd->afdeling_name = $data['AFD_NAME'];
								$afd->werks = $data['WERKS'];
								$afd->werks_afd_code = $data['WERKS_AFD_CODE'];
								$afd->save();
						}catch (\Throwable $e) {
							//
						}catch (\Exception $e) {
							//
						}
					}else{
						// masuk log  COMP_CODE  not found
					}
				
			}
		}
						
		return 1;
		
	}

	public function sync_block()
	{
		// $Master = new Master;
		// $token = $Master->token();
		$RestAPI = API::exec(array(
			'request' => 'GET',
			'host' => 'api',
			'method' => "block/all/raw/", 
		));
		// $RestAPI->data;
		// dd($RestAPI);
		if(count($RestAPI->data) > 0 ){
			// 	if(count($RestAPI['data']) > 0 ){
			// 		foreach($RestAPI['data'] as $data){
			$d = json_decode(json_encode($RestAPI->data), true);
			foreach( $d as $data){
				$afd = Afdeling::where('afdeling_code',$data['AFD_CODE'])->where('werks',$data['WERKS'])->first();
					if($afd){
						try {
								$block = Block::firstOrNew(array(
									'afdeling_id' => $afd['id'],
									'block_code' => $data['BLOCK_CODE'],
									// 'start_valid' => $data['START_VALID'],									
									// 'end_valid' => $data['END_VALID'],									
								));
								$block->block_name = $data['BLOCK_NAME'];
								$block->region_code = $data['REGION_CODE'];
								$block->company_code = $data['COMP_CODE'];
								$block->estate_code = $data['EST_CODE'];
								$block->werks = $data['WERKS'];
								$block->werks_afd_block_code = $data['WERKS_AFD_BLOCK_CODE'];
								$block->latitude_block = $data['LATITUDE_BLOCK'];
								$block->longitude_block = $data['LONGITUDE_BLOCK'];
								$block->start_valid = date("Y-m-d", strtotime($data['START_VALID']));
								$block->end_valid = date("Y-m-d", strtotime($data['END_VALID']));
								$block->save();
						}catch (\Throwable $e) {
							//
						}catch (\Exception $e) {
							//
						}
					}else{
						\Log::info('Not found ini- '.$data['AFD_CODE'].' - '.$data['WERKS']);
						// masuk log  COMP_CODE  not found
					}
				
			}
		}
		return 1;
		
	}


	public function sync_comp()
	{
		$Master = new Master;
		$token = $Master->token();
		$RestAPI = $Master
					->setEndpoint('comp/all')
					->setHeaders([
						'Authorization' => 'Bearer '.$token
					])
					->get();
		$jml = count($RestAPI['data']);
		if($jml > 0 ){
			foreach($RestAPI['data'] as $data){
				try {
					$comp = Company::firstOrNew(array('region_code' => $data['REGION_CODE'],'company_code' => $data['COMP_CODE']));
					$comp->company_name = $data['COMP_NAME'];
					$comp->address = $data['ADDRESS'];
					$comp->national = $data['NATIONAL'];
					$comp->save();
				}catch (\Throwable $e) {
					//
				}catch (\Exception $e) {
					//
				}
			}
				
		}
					
		return response()->success('Success', $jml);
	}
	
	public function sync_est()
	{
		$Master = new Master;
		$token = $Master->token();
		$RestAPI = $Master
					->setEndpoint('est/all')
					->setHeaders([
						'Authorization' => 'Bearer '.$token
					])
					->get();
		$jml = count($RestAPI['data']);
		if($jml > 0){
			foreach($RestAPI['data'] as $data){
				$comp = Company::where('company_code',$data['COMP_CODE'])->first();
				
				if($comp){
					try {
						$est = Estate::firstOrNew(array('company_id' => $comp['id'],'estate_code' => $data['EST_CODE']));
						$est->estate_name 	= $data['EST_NAME'];
						$est->werks 		= $data['WERKS'];
						$est->city 			= $data['CITY'];
						$est->save();
					}catch (\Throwable $e) {
						//
					}catch (\Exception $e) {
						//
					}
				}else{
					// masuk log  COMP_CODE  not found
				}
				
			}
				
		}else{
			//
		}		
		
		return response()->success('Success', $jml);
		
	}
	
	public function company()
	{
		$access = AccessRight::roleaccess();
		$title = 'Master Data Company';
		$data['ctree'] = '/master/company';
		$data["access"] = (object)$access['access'];
		return view('master.company', compact('data','title'));
	}
	
	public function company_datatables(Request $request)
	{
		$req = $request->all();
		$start = $req['start'];
		$access = access($request, 'master/company');
		
		$werks = explode(',',session('area_code'));
		$cek =  collect($werks);
		if( $cek->contains('All') ){
			$where = "1=1";
		}else{
			$ww = '';
			foreach($werks as $k=>$w){
				if($w != 'All'){
					$ww .= $k!=0 ? " ,'$w' " : " '$w' ";
				}
			}
			$where = "id in (select distinct company_id from TM_ESTATE where werks in ($ww))";
		}
		// dd($start);
		// $model = Company::selectRaw(' @rank  := ifnull(@rank, '.$start.')  + 1  AS no, TM_COMPANY.*')->whereRaw($where);
		$model = DB::select( DB::raw('select @rank  := ifnull(@rank, '.$start.')  + 1  AS no, TM_COMPANY.* from TM_COMPANY where '.$where));
		
		$collection = collect($model);
		// return Datatables::eloquent($model)
		return Datatables::of($collection)
			->rawColumns(['action'])
			->make(true);
	}
	
	public function estate()
	{
		$access = AccessRight::roleaccess();
		$title = 'Master Data Estate';
		$data['ctree'] = '/master/estate';
		$data["access"] = (object)$access['access'];
		return view('master.estate', compact('data','title'));
	}
	
	public function estate_datatables(Request $request)
	{
		$req = $request->all();
		$start = $req['start'];
		$access = access($request, 'master/estate');
		
		$werks = explode(',',session('area_code'));
		$cek =  collect($werks);
		if( $cek->contains('All') ){
			$where = "1=1";
		}else{
			$ww = '';
			foreach($werks as $k=>$w){
				if($w != 'All'){
					$ww .= $k!=0 ? " ,'$w' " : " '$w' ";
				}
			}
			$where = "werks in ($ww)";
		}
		
		$model = VEstate::selectRaw(' @rank  := ifnull(@rank, '.$start.')  + 1  AS no, V_ESTATE.*')->whereRaw($where);		
		
		return Datatables::eloquent($model)
			->rawColumns(['action'])
			->make(true);
	}
	
	public function afdeling()
	{
		$access = AccessRight::roleaccess();
		$title = 'Master Data Afdeling';
		$data['ctree'] = '/master/afdeling';
		$data["access"] = (object)$access['access'];
		return view('master.afdeling', compact('data','title'));
	}
	
	public function afdeling_datatables(Request $request)
	{
		$req = $request->all();
		$start = $req['start'];
		$access = access($request, 'master/afdeling');
		
		$werks = explode(',',session('area_code'));
		$cek =  collect($werks);
		if( $cek->contains('All') ){
			$where = "1=1";
		}else{
			$ww = '';
			foreach($werks as $k=>$w){
				if($w != 'All'){
					$ww .= $k!=0 ? " ,'$w' " : " '$w' ";
				}
			}
			$where = "werks in ($ww)";
		}
		
		$model = VAfdeling::selectRaw(' @rank  := ifnull(@rank, '.$start.')  + 1  AS no, V_AFDELING.*')->whereRaw($where);		
		
		return Datatables::eloquent($model)
			->rawColumns(['action'])
			->make(true);
	}

	
	public function block()
	{
		$access = AccessRight::roleaccess();
		$title = 'Master Data Block';
		$data['ctree'] = '/master/block';
		$data["access"] = (object)$access['access'];
		return view('master.block', compact('data','title'));
	}
	
	public function block_datatables(Request $request)
	{
		$req = $request->all();
		$start = $req['start'];
		$access = access($request, 'master/block');
		
		$werks = explode(',',session('area_code'));
		$cek =  collect($werks);
		if( $cek->contains('All') ){
			$where = "1=1";
		}else{
			$ww = '';
			foreach($werks as $k=>$w){
				if($w != 'All'){
					$ww .= $k!=0 ? " ,'$w' " : " '$w' ";
				}
			}
			$where = "werks in ($ww)";
		}
		
		$model = VBlock::selectRaw(' @rank  := ifnull(@rank, '.$start.')  + 1  AS no, V_BLOCK.*')->whereRaw($where);	
		
		return Datatables::eloquent($model)
			->rawColumns(['action'])
			->make(true);
	}
	
	public function api_company()
	{
		try{
			$werks = explode(',',session('area_code'));
			$cek =  collect($werks);
			if( $cek->contains('All') ){
				$where = "1=1";
			}else{
				$ww = '';
				foreach($werks as $k=>$w){
					if($w != 'All'){
						$ww .= $k!=0 ? " ,'$w' " : " '$w' ";
					}
				}
				$where = "id in (select distinct company_id from TM_ESTATE where werks in ($ww))";
			}
			$data = Company::selectRaw('id, company_code, company_name')->whereRaw($where)->get();
			
		}catch (\Throwable $e) {
            return response()->error('Error',throwable_msg($e));
        }catch (\Exception $e) {
            return response()->error('Error',exception_msg($e));
		}
		return response()->success('Success', $data);
	}
	
	public function api_estate_tree($id)
	{
		try{
			$werks = explode(',',session('area_code'));
			$cek =  collect($werks);
			if( $cek->contains('All') ){
				$where = "1=1";
			}else{
				$ww = '';
				foreach($werks as $k=>$w){
					if($w != 'All'){
						$ww .= $k!=0 ? " ,'$w' " : " '$w' ";
					}
				}
				$where = "TM_ESTATE.werks in ($ww)";
			}
			$data = Estate::
							selectRaw('estate_code, werks, estate_name')
							->join('TM_COMPANY','TM_COMPANY.id','=','TM_ESTATE.company_id')
							->where('TM_COMPANY.company_code',$id)
							->whereRaw($where)
							->get();
			
		}catch (\Throwable $e) {
            return response()->error('Error',throwable_msg($e));
        }catch (\Exception $e) {
            return response()->error('Error',exception_msg($e));
		}
		return response()->success('Success', $data);
	}
	
	public function api_afdeling_tree($id)
	{
		try{
			$d = explode('-',$id);
			$data = Afdeling::
							selectRaw('afdeling_code, afdeling_name')
							->where('werks',$d[0])
							->get();
			
		}catch (\Throwable $e) {
            return response()->error('Error',throwable_msg($e));
        }catch (\Exception $e) {
            return response()->error('Error',exception_msg($e));
		}
		return response()->success('Success', $data);
	}
	
	public function api_block_tree($id, $werks)
	{
		try{
			$d = explode('-',$werks);
			$data = Block::
							selectRaw('block_code, block_name')
							->whereRaw("substring(werks_afd_block_code,5,1) = '$id' and werks = '{$d[0]}'")
							->get();
			
		}catch (\Throwable $e) {
            return response()->error('Error',throwable_msg($e));
        }catch (\Exception $e) {
            return response()->error('Error',exception_msg($e));
		}
		return response()->success('Success', $data);
	}
	
	public function api_estate()
	{
		$data = DB::table('TM_ESTATE')
        ->select('werks as id', 'estate_name as text')
        ->get();

        $arr = array();
		$arr[] = ['id'=>'All','text'=>'All-All Business Area Code'];
        foreach ($data as $row) {
            $arr[] = array(
                "id" => $row->id,
                "text" => $row->id .'-' . $row->text
            );
        }

        return response()->json(array('data' => $arr));
	}
	
	public function cace()
	{
		// $p = Redis::incr('p');
		// return $p;
		
		$value = Cache::remember('company', 1/2, function () {
			return Company::all();
		});
		return $value;
	}
	public function caces()
	{
		if (Cache::has('company')){
			$value = Cache::get('company');
		} else {
			$value=  0;
		}
		return $value;
	}
	
}
