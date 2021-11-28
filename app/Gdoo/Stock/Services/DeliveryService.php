<?php namespace Gdoo\Stock\Services;

use DB;
use Gdoo\Index\Services\BadgeService;

class DeliveryService
{
	/**
     * 获取待审发货
     */
    public static function getBadge()
    {
        return BadgeService::getModelTodo('stock_delivery');
    }

	/**
	 * 自定义查询打印数据
	 */
    public static function getPrintData($id, $print_master_id = false)
    {
		$master = DB::table('stock_delivery as sd')
		->leftJoin('customer as c', 'c.id', '=', 'sd.customer_id')
		->leftJoin('customer_tax as ct', 'ct.id', '=', 'sd.tax_id')
		->leftJoin('sale_type as st', 'st.id', '=', 'sd.type_id')
		->selectRaw('sd.*, ct.name as tax_name, c.name as customer_name, st.name as type_name')
		->where('sd.id', $id)
		->first();

		$model = DB::table('stock_delivery_data as sdd')
		->leftJoin('stock_delivery as sd', 'sd.id', '=', 'sdd.delivery_id')
		->leftJoin('product as p', 'p.id', '=', 'sdd.product_id')
		->leftJoin('product_unit as pu', 'pu.id', '=', 'p.unit_id')
		->leftJoin('customer_order_type as cot', 'cot.id', '=', 'sdd.type_id')
		->leftJoin('warehouse as w', 'w.id', '=', 'sdd.warehouse_id');

		if ($print_master_id) {
			$model->where('sd.print_master_id', $master['print_master_id']);
		} else {
			$model->where('sdd.delivery_id', $id);
		}

		$model->whereRaw("p.code <> '99001'");
		
		$rows = $model->selectRaw("
			sdd.*,
			p.name as product_name,
			p.spec as product_spec,
			cot.name as type_name,
			pu.name as product_unit,
			p.material_type,
			p.product_type,
			batch_sn,
			w.name as warehouse_name
		")
		->orderBy('p.code', 'asc')
		->get();

		// 获取折扣额
		$money = DB::table('stock_delivery_data as sdd')
		->leftJoin('product as p', 'p.id', '=', 'sdd.product_id')
		->where('sdd.delivery_id', $id)
		->whereRaw("p.code = '99001'")
		->sum("money");

		$master['fee_money'] = $money;

		return [
			'master' => $master,
			'rows' => $rows,
		];
    }
}