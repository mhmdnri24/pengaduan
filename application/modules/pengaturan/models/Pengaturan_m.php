<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan_m extends CI_Model {
    
    public function perusahaan_by_id($id_perusahaan)
    {
        $result = $this->db->where('id_perusahaan', $id_perusahaan)
                            ->get('perusahaan')
                            ->row();
        return $result;
    }
    
    public function perusahaan_update_data($post_data, $id)
    {
        return $this->db->where('id_perusahaan', $id)->update('perusahaan', $post_data);
    }

    public function update_api_settings($data)
    {
        // Check if settings already exist
        $existing = $this->db->get('api_settings')->row();
        if ($existing) {
            return $this->db->update('api_settings', $data);
        } else {
            return $this->db->insert('api_settings', $data);
        }
    }

    public function get_api_settings()
    {
        return $this->db->get('api_settings')->row();
    }

    public function get_api_domains()
    {
        return $this->db->get('api_domains')->result();
    }

    public function add_api_domain($data)
    {
        return $this->db->insert('api_domains', $data);
    }

    public function update_api_domain($id, $data)
    {
        return $this->db->where('id', $id)->update('api_domains', $data);
    }

    public function delete_api_domain($id)
    {
        return $this->db->where('id', $id)->delete('api_domains');
    }

    public function get_api_ip_whitelist()
    {
        return $this->db->get('api_ip_whitelist')->result();
    }

    public function add_api_ip($data)
    {
        return $this->db->insert('api_ip_whitelist', $data);
    }

    public function update_api_ip($id, $data)
    {
        return $this->db->where('id', $id)->update('api_ip_whitelist', $data);
    }

    public function delete_api_ip($id)
    {
        return $this->db->where('id', $id)->delete('api_ip_whitelist');
    }

    public function get_recent_api_logs($limit = 100)
    {
        return $this->db->order_by('created_at', 'DESC')
                       ->limit($limit)
                       ->get('api_logs')
                       ->result();
    }

    public function get_api_usage_stats($days = 7)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $this->db->select('DATE(created_at) as date, COUNT(*) as total_requests')
                 ->where('created_at >=', $date)
                 ->group_by('DATE(created_at)')
                 ->order_by('date', 'ASC');

        return $this->db->get('api_logs')->result();
    }

    public function get_top_endpoints($limit = 10)
    {
        return $this->db->select('endpoint, COUNT(*) as total_requests, AVG(response_time) as avg_response_time')
                       ->group_by('endpoint')
                       ->order_by('total_requests', 'DESC')
                       ->limit($limit)
                       ->get('api_logs')
                       ->result();
    }

    public function get_api_error_stats($days = 7)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $this->db->select('DATE(created_at) as date,
                          SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors,
                          COUNT(*) as total_requests')
                 ->where('created_at >=', $date)
                 ->group_by('DATE(created_at)')
                 ->order_by('date', 'ASC');

        return $this->db->get('api_logs')->result();
    }

    public function get_rate_limit_violations($hours = 24)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        return $this->db->where('created_at >=', $date)
                       ->where('status_code', 429)
                       ->count_all_results('api_logs');
    }

    public function get_api_performance_metrics($days = 1)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $metrics = $this->db->select('
            COUNT(*) as total_requests,
            AVG(response_time) as avg_response_time,
            MIN(response_time) as min_response_time,
            MAX(response_time) as max_response_time,
            SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as success_requests,
            SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_requests
        ')
        ->where('created_at >=', $date)
        ->get('api_logs')
        ->row();

        if ($metrics) {
            $metrics->success_rate = $metrics->total_requests > 0 ?
                round(($metrics->success_requests / $metrics->total_requests) * 100, 2) : 0;
            $metrics->error_rate = $metrics->total_requests > 0 ?
                round(($metrics->error_requests / $metrics->total_requests) * 100, 2) : 0;
        }

        return $metrics;
    }

}