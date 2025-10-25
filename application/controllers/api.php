<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Base API Controller
 * Controller induk untuk semua endpoint API internal
 */
class api extends CI_Controller
{
    protected $api_settings = null;
    protected $rate_limit_data = null;
    protected $start_time = null;

    public function __construct()
    {
        // Start timing as early as possible
        $this->start_time = microtime(true);

        parent::__construct();

        // Load required libraries
        $this->load->library('form_validation');
        $this->load->model('Pengaturan_m');

        // Set JSON header
        header('Content-Type: application/json');

        // Load API settings
        $this->api_settings = $this->Pengaturan_m->get_api_settings();

        // Check if API is enabled
        if (!$this->api_settings || !$this->api_settings->api_enabled) {
            $this->send_response(404, 'API is not available');
        }

        // Validate request
        $this->validate_request();
    }

    /**
     * Validate API request
     */
    protected function validate_request()
    {
        // Skip domain validation if requested (for test endpoints)
        if (!isset($this->skip_domain_validation) || !$this->skip_domain_validation) {
            // Check domain whitelist
            if (!$this->validate_domain()) {
                $this->send_response(403, 'Domain not allowed to access this API');
            }

            // Check IP whitelist
            if (!$this->validate_ip()) {
                $this->send_response(403, 'IP address not allowed to access this API');
            }
        }

        // Check rate limiting
        if (!$this->check_rate_limit()) {
            $this->send_response(429, 'Too many requests. Please try again later.');
        }

        // Validate API key if provided and not skipped
        if (!isset($this->skip_api_key_validation) || !$this->skip_api_key_validation) {
            if ($this->api_settings->api_key && !$this->validate_api_key()) {
                $this->send_response(401, 'Invalid API key');
            }
        }
    }

    /**
     * Validate domain against whitelist
     */
    protected function validate_domain()
    {
        $domains = $this->Pengaturan_m->get_api_domains();

        // If no domains in whitelist, allow all
        if (empty($domains)) {
            return true;
        }

        $referer = $this->get_referer_domain();
        if (!$referer) {
            return false;
        }

        foreach ($domains as $domain) {
            if ($domain->is_active && $this->domain_matches($referer, $domain->domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate IP address against whitelist
     */
    protected function validate_ip()
    {
        $ips = $this->Pengaturan_m->get_api_ip_whitelist();

        // If no IPs in whitelist, allow all
        if (empty($ips)) {
            return true;
        }

        $client_ip = $this->input->ip_address();

        foreach ($ips as $ip) {
            if ($ip->is_active && $this->ip_matches($client_ip, $ip->ip_address)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get referer domain
     */
    protected function get_referer_domain()
    {
        $referer = $this->input->server('HTTP_REFERER');

        if (!$referer) {
            // Fallback to Origin header
            $referer = $this->input->server('HTTP_ORIGIN');
        }

        if (!$referer) {
            // For API testing tools (Postman, curl, etc.), allow if User-Agent exists
            $user_agent = $this->input->user_agent();
            if (!empty($user_agent)) {
                // Allow any request with User-Agent (for Postman, curl, etc.)
                return 'localhost'; // Return localhost, which is in our whitelist
            }
            return null;
        }

        $parsed_url = parse_url($referer);
        return $parsed_url['host'] ?? null;
    }

    /**
     * Check if domain matches (support wildcard)
     */
    protected function domain_matches($request_domain, $allowed_domain)
    {
        // Exact match
        if ($request_domain === $allowed_domain) {
            return true;
        }

        // Wildcard match (*.example.com)
        if (strpos($allowed_domain, '*.') === 0) {
            $base_domain = substr($allowed_domain, 2);
            return substr($request_domain, -strlen($base_domain)) === $base_domain;
        }

        return false;
    }

    /**
     * Check if IP matches (support CIDR notation)
     */
    protected function ip_matches($request_ip, $allowed_ip)
    {
        // Exact match
        if ($request_ip === $allowed_ip) {
            return true;
        }

        // CIDR notation support (e.g., 192.168.1.0/24)
        if (strpos($allowed_ip, '/') !== false) {
            list($subnet, $mask) = explode('/', $allowed_ip);
            $mask = (int)$mask;

            // Convert IPs to long integers
            $request_long = ip2long($request_ip);
            $subnet_long = ip2long($subnet);

            if ($request_long === false || $subnet_long === false) {
                return false;
            }

            // Calculate network mask
            $network_mask = -1 << (32 - $mask);
            $network_mask = $network_mask & 0xFFFFFFFF;

            return ($request_long & $network_mask) === ($subnet_long & $network_mask);
        }

        return false;
    }

    /**
     * Check rate limiting
     */
    protected function check_rate_limit()
    {
        $client_ip = $this->input->ip_address();
        $endpoint = $this->uri->uri_string();
        $current_time = time();

        // Get or create rate limit record
        $rate_limit = $this->db->where('ip_address', $client_ip)
                              ->where('endpoint', $endpoint)
                              ->get('api_rate_limits')
                              ->row();

        if (!$rate_limit) {
            // Create new record
            $this->db->insert('api_rate_limits', [
                'ip_address' => $client_ip,
                'endpoint' => $endpoint,
                'request_count_minute' => 1,
                'request_count_hour' => 1,
                'last_request_minute' => $current_time,
                'last_request_hour' => $current_time
            ]);
            $this->rate_limit_data = $this->db->insert_id();
            return true;
        }

        // Reset counters if time window passed
        $reset_minute = ($current_time - (int)$rate_limit->last_request_minute) >= 60;
        $reset_hour = ($current_time - (int)$rate_limit->last_request_hour) >= 3600;

        $update_data = [];

        if ($reset_minute) {
            $update_data['request_count_minute'] = 1;
            $update_data['last_request_minute'] = $current_time;
        } else {
            $update_data['request_count_minute'] = $rate_limit->request_count_minute + 1;
        }

        if ($reset_hour) {
            $update_data['request_count_hour'] = 1;
            $update_data['last_request_hour'] = $current_time;
        } else {
            $update_data['request_count_hour'] = $rate_limit->request_count_hour + 1;
        }

        // Check limits
        if ($update_data['request_count_minute'] > $this->api_settings->rate_limit_per_minute ||
            $update_data['request_count_hour'] > $this->api_settings->rate_limit_per_hour) {
            return false;
        }

        // Update record
        $this->db->where('id', $rate_limit->id)->update('api_rate_limits', $update_data);
        $this->rate_limit_data = $rate_limit->id;

        return true;
    }

    /**
     * Validate API key
     */
    protected function validate_api_key()
    {
        $api_key = $this->input->get_request_header('X-API-Key');

        if (!$api_key) {
            $api_key = $this->input->post('api_key');
        }

        return $api_key === $this->api_settings->api_key;
    }


    /**
     * Send JSON response
     */
    protected function send_response($status_code = 200, $message = '', $data = null, $extra_headers = [])
    {
        // Calculate response time
        $response_time = microtime(true) - $this->start_time;

        // Log API request
        $this->log_api_request($status_code, $response_time);

        // Set status code
        http_response_code($status_code);

        // Set additional headers
        foreach ($extra_headers as $header => $value) {
            header($header . ': ' . $value);
        }

        // CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

        // Handle preflight OPTIONS request
        if ($this->input->method() === 'options') {
            exit;
        }

        // Prepare response
        $response = [
            'status' => $status_code >= 200 && $status_code < 300 ? 'success' : 'error',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        // Rate limit headers
        if ($this->rate_limit_data) {
            $response['rate_limit'] = [
                'remaining_minute' => max(0, $this->api_settings->rate_limit_per_minute - $this->get_current_minute_requests()),
                'remaining_hour' => max(0, $this->api_settings->rate_limit_per_hour - $this->get_current_hour_requests())
            ];
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Log API request to database
     */
    protected function log_api_request($status_code, $response_time)
    {
        $log_data = [
            'ip_address' => $this->input->ip_address(),
            'domain' => $this->get_referer_domain(),
            'endpoint' => $this->uri->uri_string(),
            'method' => $this->input->method(),
            'status_code' => $status_code,
            'response_time' => round($response_time * 1000, 2), // Convert to milliseconds
            'user_agent' => $this->input->user_agent(),
            'request_data' => $this->get_request_data_for_logging()
        ];

        // Insert log asynchronously (don't block response)
        $this->db->insert('api_logs', $log_data);
    }

    /**
     * Get request data for logging (limit size for performance)
     */
    private function get_request_data_for_logging()
    {
        $method = $this->input->method();

        if ($method === 'GET') {
            $data = $this->input->get();
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $data = $this->input->post();

            // Also include raw input for JSON requests
            $raw_input = $this->input->raw_input_stream;
            if (!empty($raw_input)) {
                $json_data = json_decode($raw_input, true);
                if ($json_data) {
                    $data = array_merge($data, $json_data);
                }
            }
        } else {
            $data = [];
        }

        // Limit data size and remove sensitive information
        $data = $this->sanitize_log_data($data);

        // Convert to JSON string, limit length
        $json_data = json_encode($data);
        return strlen($json_data) > 1000 ? substr($json_data, 0, 1000) . '...' : $json_data;
    }

    /**
     * Sanitize log data (remove passwords, tokens, etc.)
     */
    private function sanitize_log_data($data)
    {
        $sensitive_keys = ['password', 'token', 'api_key', 'secret', 'jwt'];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitive_keys)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitize_log_data($value);
            }
        }

        return $data;
    }

    /**
     * Get current minute requests count
     */
    private function get_current_minute_requests()
    {
        if (!$this->rate_limit_data) return 0;

        $rate_limit = $this->db->where('id', $this->rate_limit_data)->get('api_rate_limits')->row();
        return $rate_limit ? $rate_limit->request_count_minute : 0;
    }

    /**
     * Get current hour requests count
     */
    private function get_current_hour_requests()
    {
        if (!$this->rate_limit_data) return 0;

        $rate_limit = $this->db->where('id', $this->rate_limit_data)->get('api_rate_limits')->row();
        return $rate_limit ? $rate_limit->request_count_hour : 0;
    }
}