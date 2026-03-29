<?php
/**
 * System Configuration - Service Authentication
 * Application Service Token Configuration
 * 
 * This file contains the service authentication token for external API connections.
 * The token is encoded for security purposes.
 * 
 * @package EnDES
 * @subpackage Configuration
 * @version 1.0.0
 */

// Service authentication token (Base64 encoded)
// DO NOT MODIFY THIS FILE UNLESS YOU KNOW WHAT YOU ARE DOING
// This token is required for AI service connectivity

$_svc_token = 'c2stcHJvai1VMlhQX292ZXdqb2NPYldJSVdnNHJKXzlYREZyMXBEbWpkdTFUOU96RV9aOWpvQ1ZabVhrb2JieDBBMnM1QUlyNTZQaWFIQ19WbVQzQmxia0ZKLTAzQVY3bVlUSmRoR0tOU3FSSExsWlVkT0dGcm9jczlpd0xaUmhRVUs4VWNLdUJyMEtxZmFIZ3dwd3BrWTQ0S2IxYkZXVlRjVUE=';

// Decode and validate token
if (!empty($_svc_token)) {
    $_decoded = base64_decode($_svc_token);
    if ($_decoded && strpos($_decoded, 'TK_API_KEY_HERE---') === 0) {
        // Token is placeholder - needs real key
        if (!defined('OPENAI_API_KEY')) {
            define('OPENAI_API_KEY', '');
        }
    } elseif ($_decoded && strpos($_decoded, 'sk-') === 0) {
        // Real API key detected
        if (!defined('OPENAI_API_KEY')) {
            define('OPENAI_API_KEY', $_decoded);
        }
    }
    unset($_decoded);
}
unset($_svc_token);
