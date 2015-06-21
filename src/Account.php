<?php

namespace Iris;

class Account extends RestEntry {
    public function __construct($account_id, $client=Null, $namespace='accounts')
    {
        parent::_init($client, $namespace);
        $this->account_id = $account_id;
        $this->client = $client;
    }

    /**
    * @params \Iris\TnLineOptions
    */
    public function lineOptionOrders(TnLineOptions $data) {
        $url = sprintf('%s/%s', $this->account_id, 'lineOptionOrders');
        $response = parent::post($url, "LineOptionOrder", $data->to_array());
        return new TnLineOptionOrderResponse($response);
    }

    public function inserviceNumbers() {
        if(!isset($this->inserviceNumbers))
            $this->inserviceNumbers = new InserviceNumbers($this);
        return $this->inserviceNumbers;
    }

    public function orders() {
        if(!isset($this->orders))
            $this->orders = new Orders($this);
        return $this->orders;
    }

    public function portins() {
        if(!isset($this->portins))
            $this->portins = new Portins($this);
        return $this->portins;
    }

    public function disconnects() {
        if(!isset($this->disconnects))
            $this->disconnects = new Disconnects($this);
        return $this->disconnects;
    }

    public function disnumbers() {
        if(!isset($this->disnumbers))
            $this->disnumbers = new Disnumbers($this);
        return $this->disnumbers;
    }

    public function portouts() {
        if(!isset($this->portouts))
            $this->portouts = new Portouts($this);
        return $this->portouts;
    }

    public function lsrorders() {
        if(!isset($this->lsrorders))
            $this->lsrorders = new Lsrorders($this);
        return $this->lsrorders;
    }

    public function didas() {
        if(!isset($this->didas))
            $this->didas = new Didas($this);
        return $this->didas;
    }

    public function subscriptions() {
        if(!isset($this->subscriptions))
            $this->subscriptions = new Subscriptions($this);
        return $this->subscriptions;
    }

    public function tnsreservations() {
        if(!isset($this->tnsreservations))
            $this->tnsreservations = new TnsReservations($this);
        return $this->tnsreservations;
    }

    public function sites() {
        if(!isset($this->sites))
            $this->sites = new Sites($this);
        return $this->sites;
    }

    public function reports() {
        if(!isset($this->reports))
            $this->reports = new Reports($this);
        return $this->reports;
    }

    public function lidbs() {
        if(!isset($this->lidbs))
            $this->lidbs = new Lidbs($this);
        return $this->lidbs;
    }

    /**
     * Account Info by Id
     *
     */
    public function get($url, $options=Array(), $defaults = Array(), $required = Array())
    {
        $data = parent::get($this->account_id);
        return $data;
    }

    private function parse_response($data, $level1, $level2, $count, $classname) {
        $out = [];
        $items = $level2 ? $data[$level1][$level2]: $data[$level1];

        if($count == 1 || $this->is_assoc($items))
            $items = [ $items ];

        foreach($items as $item) {
            $out[] = new $classname($item);
        }

        return $out;
    }

    public function availableNumbers($filters=Array()){
        $query_fields = ["areaCode", "quantity", "enableTNDetail", "npaNxx", "npaNxxx",
            "LCA", "enableTNDetail", "rateCenter", "state", "quantity", "tollFreeVanity",
            "tollFreeWildCardPattern", "city", "zip", "lata" ];

        foreach($filters as $field => $value) {
            if(!in_array($field, $query_fields))
                throw new \Exception("Field $filed is not allowed.");
        }

        $url = sprintf('%s/%s', $this->account_id, 'availableNumbers');
        $data = parent::get($url, $filters);
        $count = $data['ResultCount'];

        $types = [
            ["level1" => "TelephoneNumberDetailList", "level2" => "TelephoneNumberDetail", "classname" => "\Iris\TelephoneNumberDetail"],
            ["level1" => "TelephoneNumberList", "level2" => false, "classname" => "\Iris\TelephoneNumbers"],
        ];

        foreach($types as $type) {
            if(isset($data[$type['level1']]) && (!$type['level2'] || isset($data[$type['level1']][$type['level2']])))
                return $this->parse_response($data, $type['level1'], $type['level2'], $count, $type['classname']);
        }
    }

    public function lnpChecker(NumberPortabilityRequest $data, $fullcheck = false) {
        if($fullcheck !== false && in_array($fullcheck, ["true", "false", "onnetportability", "offnetportability"])) {
            $f = "?fullcheck=$fullcheck";
        } else {
            $f = "";
        }

        $url = sprintf('%s/%s%s', $this->account_id, 'lnpchecker', $f);
        $res = parent::post($url, "NumberPortabilityRequest", $data->to_array());
        return new NumberPortabilityResponse($res);
    }

    public function serviceNumbers($filters=Array()){
        $url = sprintf('%s/%s', $this->account_id, 'serviceNumbers');
        $data = parent::get($url, $filters);
        return $data;
    }

    public function users($filters=Array()){
        $url = sprintf('%s/%s', $this->account_id, 'users');
        $data = parent::get($url, $filters);
        return $data;
    }

    public function products($filters=Array()){
        $url = sprintf('%s/%s', $this->account_id, 'products');
        $data = parent::get($url, $filters);
        return $data;
    }

    public function get_relative_namespace() {
      return "accounts/{$this->account_id}";
    }

    public function get_rest_client() {
      return $this->client;
    }
}