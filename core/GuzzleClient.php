<?php

namespace Iris;

final class GuzzleClient extends iClient {
    public function __construct($login, $password, $options=Null)
    {
        /* TODO:  singleton */

        $this->login = $login;
        $this->password = $password;

        $this->url = $this->prepare_base_url($options['url']);

        $this->client = new \GuzzleHttp\Client();

    }

    public function get($url, $options=array())
    {
        $url = $this->prepare_url($url);
        $full_url = sprintf('%s%s', $this->url, $url);
        $response = $this->client->get($full_url, ['query' => $options, 'auth' =>  [$this->login, $this->password]]);
        $response_body_str = '';
        $string_or_stream_body = $response->getBody(true);
        $response_body_str = $this->get_body($string_or_stream_body);


        if($response_body_str !== "") {
            $response_body_xml = new \SimpleXMLElement($response_body_str);
            $response_array = $this->xml2array($response_body_xml);
        } else {
            $response_array = array();
        }
        return $response_array;
    }

    public function make_call($url, $base_node, $data, $method) {
        $url = $this->prepare_url($url);
        $full_url = sprintf('%s%s', $this->url, $url);

        $xml_base_str = sprintf("<%s></%s>", $base_node, $base_node);
        $xml = new \SimpleXMLElement($xml_base_str);
        //print_r($data); exit;
        $this->array2xml($data, $xml);
        print_r($xml->asXML());

        try {
            $response = $this->client->{$method}(
                                   $full_url,
                                   ['auth' =>  [$this->login, $this->password],
                                    'body' => $xml->asXML(),
                                    'headers' => ['Content-Type' => 'application/xml']
                                    ]
                                   );
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
          /* TODO:  get message */
          //print_r($e->getResponse()->getBody(true)->read(1024));exit;
            echo "Response status code: ".$e->getResponse()->getStatusCode();
            throw new \Exception($e);
        }

        $string_or_stream_body = $response->getBody(true);
        $response_body_str = $this->get_body($string_or_stream_body);

        try {
            if($response_body_str == "") {
                if($response->hasHeader('Location')) {
                    $response_array = array("Location" => $response->getHeader('Location')[0]);
                } else {
                    $response_array = array();
                }
            } else {
                $response_body_xml = new \SimpleXMLElement($response_body_str);
                $response_array = $this->xml2array($response_body_xml);
            }
        } catch(Exception $e) {
            $response_array = array();
        }
        return $response_array;
    }

    public function post($url, $base_node, $data)
    {
        return $this->make_call($url, $base_node, $data, 'post');
    }

    public function put($url, $base_node, $data)
    {
        return $this->make_call($url, $base_node, $data, 'put');
    }


    public function delete($url)
    {
        $url = $this->prepare_url($url);
        $full_url = sprintf('%s%s', $this->url, $url);
        $this->client->delete($full_url, ['auth' =>  [$this->login, $this->password]]);
    }

    protected function get_body($body)
    {
        $body_str = '';
        if ($body instanceof \GuzzleHttp\Psr7\Stream) 
        {
            while(!$body->eof())
            {
                $body_str .= $body->read(1024);
            }
        }
        else 
        {
            $body_str = $body;
        }

        return $body_str;
    }
}
