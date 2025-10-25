<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_login_m extends CI_Model
{
    private $table = 'log_login';

    public function insert_log($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_login_attempts($ip_address, $minutes = 15)
    {
        $this->db->where('ip_address', $ip_address);
        $this->db->where('attempt_time >=', date('Y-m-d H:i:s', strtotime("-{$minutes} minutes")));
        $this->db->where('status', 'failed');
        return $this->db->count_all_results($this->table);
    }

    public function clean_old_logs($days = 30)
    {
        $this->db->where('attempt_time <', date('Y-m-d H:i:s', strtotime("-{$days} days")));
        return $this->db->delete($this->table);
    }

    public function get_recent_logins($limit = 5)
    {
        $this->db->select('log_login.*, user.nama, user.username');
        $this->db->from($this->table);
        $this->db->join('user', 'user.id_user = log_login.id_user', 'left');
        $this->db->order_by('log_login.attempt_time', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function get_recent_logins_by_user($user_id, $limit = 10)
    {
        $this->db->select('log_login.*, user.nama, user.username');
        $this->db->from('log_login');
        $this->db->join('user', 'log_login.id_user = user.id_user', 'left');
        $this->db->where('log_login.id_user', $user_id);
        $this->db->order_by('log_login.attempt_time', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
} 