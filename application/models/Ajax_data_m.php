<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ajax_data_m extends CI_Model
{
    protected $table; // Nama tabel dari database
    protected $select = '*';
    protected $column_order; // Field yang ada di tabel
    protected $column_search; // Field yang diizinkan untuk pencarian 
    protected $order; // Default order 
    protected $condition; // Default condition 
    protected $join; // Default join 
    protected $group_by; // Default group by

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function _get_datatables_query()
    {
        $post_search = $this->input->post('search');
        $post_order = $this->input->post('order');

        $this->db->from($this->table);

        if (!empty($this->join)) {
            foreach ($this->join as $param) {
                // Pastikan format join benar
                if (is_array($param) && count($param) >= 2) {
                    $table = isset($param[0]) ? $param[0] : '';
                    $on = isset($param[1]) ? $param[1] : '';
                    $type = isset($param[2]) ? $param[2] : 'inner';
                    
                    if (!empty($table) && !empty($on)) {
                        $this->db->join($table, $on, $type);
                    }
                }
            }
        }

        if (!empty($this->condition)) {
            foreach ($this->condition as $key => $val) {
                if (!is_string($key)) {
                    $this->db->where($val);
                } else {
                    $this->db->where($key, $val);
                }
            }
        }

        if (!empty($this->group_by)) {
            $this->db->group_by($this->group_by);
        }

        $i = 0;
        foreach ($this->column_search as $item) {
            if (@$post_search['value']) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $post_search['value']);
                } else {
                    $this->db->or_like($item, $post_search['value']);
                }
                if (count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        } else if (isset($post_order)) {
            $this->db->order_by($this->column_order[$post_order['0']['column']], $post_order['0']['dir']);
        }
    }

    public function data_config($dataConfig)
    {
        $this->table = isset($dataConfig['table']) ? $dataConfig['table'] : '';
        $this->select = isset($dataConfig['select']) ? $dataConfig['select'] : '*';
        $this->column_order = isset($dataConfig['column_order']) ? $dataConfig['column_order'] : [];
        $this->column_search = isset($dataConfig['column_search']) ? $dataConfig['column_search'] : [];
        $this->order = isset($dataConfig['order']) ? $dataConfig['order'] : [];
        $this->join = isset($dataConfig['join']) ? $dataConfig['join'] : [];
        $this->condition = isset($dataConfig['condition']) ? $dataConfig['condition'] : [];
        $this->group_by = isset($dataConfig['group_by']) ? $dataConfig['group_by'] : '';
    }

    public function get_datatables()
    {
        $post_length = $this->input->post('length');
        $post_start = $this->input->post('start');

        $this->_get_datatables_query();
        $this->db->select($this->select);
        if ($post_length != -1)
            $this->db->limit($post_length, $post_start);
        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered()
    {
        $this->_get_datatables_query();
        $query = $this->db->select('COUNT(*) as total')->get();
        $result = $query->row();
        return $result ? $result->total : 0;
    }

    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
}
