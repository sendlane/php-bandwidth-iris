<?php

/**
 * @model Order
 * https://api.test.inetwork.com/v1.0/accounts/orders
 *
 *
 *
 * provides:
 * get/0
 *
 */

namespace Iris;

final class Orders extends RestEntry{

    public function __construct($parent) {
        $this->parent = $parent;
        parent::_init($this->parent->get_rest_client(), $this->parent->get_relative_namespace());
    }

    public function get($filters = Array(), $defaults = Array(), $required = Array()) {

        $orders = [];

        $data = parent::get('orders', $filters, Array("page"=> 1, "size" => 30), Array("page", "size"));

        if($data['ListOrderIdUserIdDate'] && $data['ListOrderIdUserIdDate']['TotalCount']) {
            $items = $data['ListOrderIdUserIdDate']['OrderIdUserIdDate'];

            if($this->is_assoc($items))
                $items = [ $items ];

            foreach($items as $order) {
                $orders[] = new Order($this, $order);
            }
        }

        return $orders;
    }

    public function order($id) {
        $order = new Order($this, array("id" => $id));
        return $order->get();
    }

    public function get_appendix() {
        return '/orders';
    }

    public function create($data) {
        $order = new Order($this, $data);
        return $order;
    }
}

final class Order extends RestEntry{
    use BaseModel;

    public $id = Null;

    protected $fields = array(
        "id" => array("type" => "string"),
        "orderId" => array("type" => "string"),
        "Quantity" => array("type" => "string"),
        "Name" => array("type" => "string"),
        "CustomerOrderId" => array("type" => "string"),
        "SiteId" => array("type" => "string"),
        "PeerId" => array("type" => "string"),
        "PartialAllowed" => array("type" => "string"),
        "BackOrderRequested" => array("type" => "string"),
        "AreaCodeSearchAndOrderType" => array("type" => "\Iris\SearchOrderType"),
        "RateCenterSearchAndOrderType" => array("type" => "\Iris\SearchOrderType"),
        "NPANXXSearchAndOrderType" => array("type" => "\Iris\SearchOrderType"),
        "TollFreeVanitySearchAndOrderType" => array("type" => "\Iris\SearchOrderType"),
        "TollFreeWildCharSearchAndOrderType"  => array("type" => "\Iris\SearchOrderType"),
        "StateSearchAndOrderType" => array("type" => "\Iris\SearchOrderType"),
        "CitySearchAndOrderType" => array("type" => "\Iris\SearchOrderType"),
        "ZIPSearchAndOrderType" => array("type" => "\Iris\SearchOrderType"),
        "LATASearchAndOrderType" => array("type" => "\Iris\SearchOrderType"),
        "CountOfTNs" => array("type" => "string"),
        "userId" => array("type" => "string"),
        "lastModifiedDate" => array("type" => "string"),
        "OrderDate" => array("type" => "string"),
        "OrderStatus" => array("type" => "string"),
        "OrderType" => array("type" => "string"),
        "EnableTNDetail" => array("type" => "string"),
        "TelephoneNumberList" => array("type" => "\Iris\TelephoneNumbers"),
        "ExistingTelephoneNumberOrderType" => array("type" => "\Iris\TelephoneNumberList"),
        "CloseOrder"  => array("type" => "string"),

    );

    public function __construct($orders, $data)
    {
        $this->set_data($data);
        $this->parent = $orders;
        parent::_init($orders->get_rest_client(), $orders->get_relative_namespace());
        $this->notes = null;
    }

    public function get() {
        $data = parent::get($this->get_id());
        $response = new OrderResponse($data);
        $this->set_data($data['Order']);
        $response->Order = $this;
        return $response;
    }

    public function post() {
        $data = parent::post(null, "Order", $this->to_array());
        $this->set_data($data["Order"]);
        $this->OrderStatus = $data["OrderStatus"];
    }

    public function put() {
        $arr = $this->to_array();
        unset($arr['id']);
        unset($arr['orderId']);
        parent::put($this->get_id(), "Order", $arr);
    }

    /**
    * Get Notes of Entity
    * @return \Iris\Notes
    */
    public function notes() {
        if(is_null($this->notes)) {
            $this->notes = new Notes($this);
        }

        return $this->notes;
    }
    /**
     * Provide relative url
     * @return string
     */
    public function get_appendix() {
        return '/'.$this->get_id();
    }
    /**
     * Get Entity Id
     * @return type
     * @throws Exception in case of OrderId is null
     */
    public function get_id() {
        if(!isset($this->orderId) && !isset($this->id))
            throw new \Exception("You can't use this function without OrderId or id");
        return isset($this->orderId) ? $this->orderId : $this->id;
    }
}