<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoadCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_name' => [
				'required', 
				Rule::unique('TM_ROAD_CATEGORY')->where(function ($query) {
					if(\Route::current()->getName()!= 'master.road_category_update'){
						return $query->where('status_id',$this->get('status_id'))
									 ->whereRaw('deleted_at is null');
					}else{
						return $query->where('status_id',$this->get('status_id'))
									 ->whereRaw('deleted_at is null')
									 ->where('id','!=',$this->get('id'));
					}
				})
			],
            'category_code' => [
				'required', 
				Rule::unique('TM_ROAD_CATEGORY')->where(function ($query) {
					if(\Route::current()->getName()!= 'master.road_category_update'){
						return $query->where('status_id',$this->get('status_id'))
								 ->whereRaw('deleted_at is null');
					}else{
						return $query->where('status_id',$this->get('status_id'))
								 ->whereRaw('deleted_at is null')
								 ->where('id','!=',$this->get('id'));
					}
				}),
				'numeric'
			],
			'category_initial' => [
				'required', 
				Rule::unique('TM_ROAD_CATEGORY')->where(function ($query) {
					if(\Route::current()->getName()!= 'master.road_category_update'){
						return $query->whereRaw('deleted_at is null');
					}else{
						return $query->whereRaw('deleted_at is null')->where('id','!=',$this->get('id'));
					}
				})
			],
            'status_id' => 'required',
        ];
    }
	
	public function messages()
	{
		// return [
			// 'category_name.required' => 'Category Name harus diisi',
			// 'category_initial.required'  => 'Category Initial harus diisi',
			// 'category_code.required'  => 'Category Code harus diisi',
			// 'category_code.numeric'  => 'Category Code harus berupa angka',
			// 'status_id.required'  => 'Status harus diisi',
		// ];
		
		return [
			'required'  => 'Harap bagian :attribute di isi.',
			'unique'    => ':attribute sudah digunakan',
		];
	}
	
}
