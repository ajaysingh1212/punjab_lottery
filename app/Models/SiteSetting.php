<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type', 'label', 'sort_order'];

    protected static array $defaultSettings = [
        // General
        ['key' => 'site_name', 'value' => 'RBAC System', 'group' => 'general', 'type' => 'text', 'label' => 'Site Name', 'sort_order' => 1],
        ['key' => 'site_tagline', 'value' => 'Professional Role Based Access Control', 'group' => 'general', 'type' => 'text', 'label' => 'Tagline', 'sort_order' => 2],
        ['key' => 'site_description', 'value' => 'A powerful and flexible RBAC system.', 'group' => 'general', 'type' => 'textarea', 'label' => 'Description', 'sort_order' => 3],
        ['key' => 'site_logo', 'value' => null, 'group' => 'general', 'type' => 'image', 'label' => 'Logo', 'sort_order' => 4],
        ['key' => 'site_favicon', 'value' => null, 'group' => 'general', 'type' => 'image', 'label' => 'Favicon', 'sort_order' => 5],
        ['key' => 'admin_email', 'value' => 'admin@example.com', 'group' => 'general', 'type' => 'text', 'label' => 'Admin Email', 'sort_order' => 6],
        ['key' => 'customer_dashboard_title', 'value' => 'Punjab Lottery Prize Desk', 'group' => 'customer_dashboard', 'type' => 'text', 'label' => 'Customer Dashboard Title', 'sort_order' => 1],
        ['key' => 'customer_dashboard_subtitle', 'value' => 'Track your lottery tickets, live draw countdowns, winning claims, and secure charge payments in one place.', 'group' => 'customer_dashboard', 'type' => 'textarea', 'label' => 'Dashboard Intro Text', 'sort_order' => 2],
        ['key' => 'customer_dashboard_logo', 'value' => null, 'group' => 'customer_dashboard', 'type' => 'image', 'label' => 'Customer Dashboard Logo', 'sort_order' => 3],
        ['key' => 'customer_dashboard_badge', 'value' => 'Official Punjab Lottery Customer Panel', 'group' => 'customer_dashboard', 'type' => 'text', 'label' => 'Header Badge Text', 'sort_order' => 4],
        ['key' => 'customer_kyc_title', 'value' => 'Punjab Lottery KYC Verification', 'group' => 'customer_dashboard', 'type' => 'text', 'label' => 'KYC Modal Title', 'sort_order' => 5],
        ['key' => 'customer_kyc_note', 'value' => 'Bank details are required for withdrawal. Aadhaar, PAN, and photo uploads are optional but help the admin verify your claim faster.', 'group' => 'customer_dashboard', 'type' => 'textarea', 'label' => 'KYC Modal Note', 'sort_order' => 6],
        ['key' => 'customer_payment_note', 'value' => 'Use only the verified payment accounts shown here. After payment, submit UTR and screenshot for admin verification.', 'group' => 'customer_dashboard', 'type' => 'textarea', 'label' => 'Charge Payment Note', 'sort_order' => 7],
        ['key' => 'customer_support_whatsapp', 'value' => '+91 9876543210', 'group' => 'customer_dashboard', 'type' => 'text', 'label' => 'Customer WhatsApp Number', 'sort_order' => 8],
        ['key' => 'customer_support_call', 'value' => '+91 9876543210', 'group' => 'customer_dashboard', 'type' => 'text', 'label' => 'Customer Calling Number', 'sort_order' => 9],
        ['key' => 'customer_support_address', 'value' => 'Ludhiana, Punjab', 'group' => 'customer_dashboard', 'type' => 'textarea', 'label' => 'Customer Support Address', 'sort_order' => 10],
        ['key' => 'customer_footer_note', 'value' => 'Independent ticket seller information and customer support desk.', 'group' => 'customer_dashboard', 'type' => 'textarea', 'label' => 'Customer Dashboard Footer Note', 'sort_order' => 11],
        ['key' => 'landing_kicker', 'value' => 'Punjab State Lottery - Authorized Seller & Information Portal', 'group' => 'landing_page', 'type' => 'text', 'label' => 'Landing Kicker', 'sort_order' => 1],
        ['key' => 'landing_title', 'value' => 'Top Ludhiana Lottery Seller for Punjab State Lottery Tickets', 'group' => 'landing_page', 'type' => 'text', 'label' => 'Landing Title', 'sort_order' => 2],
        ['key' => 'landing_subtitle', 'value' => 'Weekly, Monthly & Bumper draws - Always buy genuine physical tickets only.', 'group' => 'landing_page', 'type' => 'textarea', 'label' => 'Landing Subtitle', 'sort_order' => 3],
        ['key' => 'landing_bumper_title', 'value' => 'Punjab State Dear Rakhi Bumper 2026', 'group' => 'landing_page', 'type' => 'text', 'label' => 'Bumper Title', 'sort_order' => 4],
        ['key' => 'landing_bumper_prize', 'value' => '1st Prize Rs 7 Crore - Guaranteed', 'group' => 'landing_page', 'type' => 'text', 'label' => 'Bumper Prize Text', 'sort_order' => 5],
        ['key' => 'landing_bumper_date', 'value' => '2026-08-29 18:00:00', 'group' => 'landing_page', 'type' => 'text', 'label' => 'Bumper Draw Date Time', 'sort_order' => 6],
        ['key' => 'landing_result_text', 'value' => 'Every Punjab State Lottery draw is conducted live by the Directorate. Keep your physical ticket safe and match the exact series and number after the draw.', 'group' => 'landing_page', 'type' => 'textarea', 'label' => 'Result Information Text', 'sort_order' => 7],
        ['key' => 'landing_footer_text', 'value' => 'We are an independent information portal, not the government lottery department. Official results are announced only by the Directorate.', 'group' => 'landing_page', 'type' => 'textarea', 'label' => 'Landing Footer Text', 'sort_order' => 8],
        // Contact
        ['key' => 'contact_email', 'value' => '', 'group' => 'contact', 'type' => 'text', 'label' => 'Contact Email', 'sort_order' => 1],
        ['key' => 'contact_phone', 'value' => '', 'group' => 'contact', 'type' => 'text', 'label' => 'Contact Phone', 'sort_order' => 2],
        ['key' => 'contact_phone2', 'value' => '', 'group' => 'contact', 'type' => 'text', 'label' => 'Alternate Phone', 'sort_order' => 3],
        ['key' => 'contact_address', 'value' => '', 'group' => 'contact', 'type' => 'textarea', 'label' => 'Address', 'sort_order' => 4],
        ['key' => 'contact_city', 'value' => '', 'group' => 'contact', 'type' => 'text', 'label' => 'City', 'sort_order' => 5],
        ['key' => 'contact_state', 'value' => '', 'group' => 'contact', 'type' => 'text', 'label' => 'State', 'sort_order' => 6],
        ['key' => 'contact_country', 'value' => '', 'group' => 'contact', 'type' => 'text', 'label' => 'Country', 'sort_order' => 7],
        ['key' => 'contact_zip', 'value' => '', 'group' => 'contact', 'type' => 'text', 'label' => 'ZIP Code', 'sort_order' => 8],
        // Social
        ['key' => 'social_facebook', 'value' => '', 'group' => 'social', 'type' => 'text', 'label' => 'Facebook URL', 'sort_order' => 1],
        ['key' => 'social_twitter', 'value' => '', 'group' => 'social', 'type' => 'text', 'label' => 'Twitter/X URL', 'sort_order' => 2],
        ['key' => 'social_linkedin', 'value' => '', 'group' => 'social', 'type' => 'text', 'label' => 'LinkedIn URL', 'sort_order' => 3],
        ['key' => 'social_instagram', 'value' => '', 'group' => 'social', 'type' => 'text', 'label' => 'Instagram URL', 'sort_order' => 4],
        ['key' => 'social_youtube', 'value' => '', 'group' => 'social', 'type' => 'text', 'label' => 'YouTube URL', 'sort_order' => 5],
        // SEO
        ['key' => 'meta_keywords', 'value' => '', 'group' => 'seo', 'type' => 'text', 'label' => 'Meta Keywords', 'sort_order' => 1],
        ['key' => 'meta_description', 'value' => '', 'group' => 'seo', 'type' => 'textarea', 'label' => 'Meta Description', 'sort_order' => 2],
        ['key' => 'google_analytics', 'value' => '', 'group' => 'seo', 'type' => 'text', 'label' => 'Google Analytics ID', 'sort_order' => 3],
        // System
        ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean', 'label' => 'Maintenance Mode', 'sort_order' => 1],
        ['key' => 'registration_enabled', 'value' => '1', 'group' => 'system', 'type' => 'boolean', 'label' => 'Allow Registration', 'sort_order' => 2],
        ['key' => 'items_per_page', 'value' => '15', 'group' => 'system', 'type' => 'text', 'label' => 'Items Per Page', 'sort_order' => 3],
        ['key' => 'timezone', 'value' => 'Asia/Kolkata', 'group' => 'system', 'type' => 'text', 'label' => 'Timezone', 'sort_order' => 4],
        ['key' => 'date_format', 'value' => 'd M Y', 'group' => 'system', 'type' => 'text', 'label' => 'Date Format', 'sort_order' => 5],
    ];

    public static function get(string $key, $default = null)
    {
        if (!Schema::hasTable('site_settings')) {
            return $default ?? collect(static::getDefaultSettings())->firstWhere('key', $key)['value'] ?? null;
        }

        return Cache::rememberForever('settings.' . $key, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.' . $key);
    }

    public static function getAllSettings(): array
    {
        return static::orderBy('group')->orderBy('sort_order')->get()->keyBy('key')->toArray();
    }

    public static function getGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('group', $group)->orderBy('sort_order')->get();
    }

    public static function getDefaultSettings(): array
    {
        return static::$defaultSettings;
    }

    public static function clearCache(): void
    {
        static::all()->each(function ($setting) {
            Cache::forget('settings.' . $setting->key);
        });
    }

    public static function ensureDefaults(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        foreach (static::getDefaultSettings() as $setting) {
            static::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
