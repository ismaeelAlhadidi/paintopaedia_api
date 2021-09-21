<?php

namespace App\Helpers;

class Paginator implements \JsonSerializable, \IteratorAggregate {
    
    private $meta_data;

    private $data;

    public function __construct(array $data, $total, $per_page, $current_page) {

        $this->data = $data;

        $last_page = floor($total / $per_page + 1);
        $from = ( $current_page - 1 ) * $per_page + 1;

        // $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https';
        // $path = $protocol.'://'.$_SERVER['HTTP_HOST'].'?'.$_SERVER['QUERY_STRING'];
        $path = "http://test.com";

        $this->meta_data = [
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'last_page' => $last_page,
            'first_page_url' => $path . "?page=1",
            'last_page_url' => $path . "?page=" . $last_page,
            'next_page_url' => ( ($current_page + 1) > $last_page ? null : $path . "?page=" . ( $current_page + 1 ) ),
            'prev_page_url' => ( ($current_page - 1) < 1 ? null : $path . "?page=" . ( $current_page - 1 ) ),
            'path' => $path,
            'from' => $from ,
            'to' => ( $current_page == $last_page ) ? ( $total - $from ) : $per_page,
        ];
    }

    public function count() {

        return $this->meta_data['to'] - $this->meta_data['from'] + 1;
    }

    public function items() {

        return $this->data;
    }

    public function currentPage() {

        return $this->meta_data['current_page'];
    }

    public function nextPageUrl() {

        return $this->meta_data['next_page_url'];
    }

    public function previousPageUrl() {

        return $this->meta_data['prev_page_url'];
    }

    public function total() {

        return $this->meta_data['total'];
    }

    public function url($page) {

        if($page < 1 || $page > $this->meta_data['last_page']) return null;

        return $this->meta_data['path'] . "?page=" . $page;
    }

    public function perPage() {

        return $this->meta_data['per_page'];
    }

    public function jsonSerialize() {

        return array_merge ( $this->meta_data, [ "data" => $this->data ] );
    }

    public function getIterator() {

        return new \ArrayIterator($this->data);
    }
}